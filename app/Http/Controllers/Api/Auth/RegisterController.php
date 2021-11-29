<?php

namespace App\Http\Controllers\Api\Auth;

use App\Constant\AccountType;
use App\Db\Entity\Role;
use App\Db\Entity\User;
use App\Db\Service\AffiliatedPersonDao;
use App\Db\Service\AffiliatedPersonStatisticDao;
use App\Db\Service\UserDao;
use App\Http\Controllers\BaseController;
use App\Providers\RouteServiceProvider;
use App\Rules\IsEnumValueRule;
use App\Service\AuthService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
// to api methods
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

class RegisterController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    protected UserDao $userDao;
    protected AffiliatedPersonStatisticDao $affiliatedPersonStatisticDao;
    protected AffiliatedPersonDao $affiliatedPersonDao;
    private string $env;

    public function __construct(
        AuthService $authService,
        UserDao $userDao,
        AffiliatedPersonDao $affiliatedPersonDao,
        AffiliatedPersonStatisticDao $affiliatedPersonStatisticDao
    )
    {
        parent::__construct($authService);
        $this->userDao = $userDao;
        $this->affiliatedPersonDao = $affiliatedPersonDao;
        $this->affiliatedPersonStatisticDao = $affiliatedPersonStatisticDao;
        $this->env = config('app.env');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'accountType' => ['required', new IsEnumValueRule(AccountType::class)],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'middleName' => ['string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'isProcessedPersonalData' => ['required', 'boolean']
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  User | Builder | Model  $data
     * @return User
     */
    protected function create(array $data) : Model
    {
        return User::query()->create([
            'account_type' => $data['accountType'],
            'name' => $data['name'],
            'surname' => $data['surname'],
            'middle_name' => $data['middleName'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => Role::ROLE_CUSTOMER,
            'is_processed_personal_data' => $data['isProcessedPersonalData']
        ]);
    }

    // api methods
    public function register(Request $request)
    {
        // Here the request is validated. The validator method is located
        // inside the RegisterController, and makes sure the name, email
        // password and password_confirmation fields are required.
        $this->validator($request->all())->validate();

        if (!$request->get("isProcessedPersonalData"))
        {
            return $this->jsonError('Для успешной регистрации подтвердите своё согласие на хранение и обработку ваших данных!');
        }
        // A Registered event is created and will trigger any relevant
        // observers, such as sending a confirmation email or any
        // code that needs to be run as soon as the user is created.
        /** @var User $user */
        $user = $this->create($request->all());
        event(new Registered($user));

        // After the user is created, he's logged in.
        $this->guard()->login($user);

        $authToken = $user->generateToken();
        $user->apiToken = $authToken->token;

        if (Session::exists('promo_code'))
        {
            $affiliatedPerson = $this->affiliatedPersonDao->getByPromo(Session::get('promo_code'));
            $this->affiliatedPersonStatisticDao->add($user, $affiliatedPerson);
        }

        $this->userDao->setPremiumTariffUser($user);

        return $this->json($user);
    }
}
