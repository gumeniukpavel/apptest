<?php

namespace App\Console\Commands;

use App\Constant\AccountType;
use App\Db\Entity\Role;
use App\Db\Entity\User;
use App\Db\Service\UserDao;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegisterUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:register {email} {roleId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register User';

    private UserDao $userDao;
    private string $env;

    public function __construct(
        UserDao $userDao
    )
    {
        parent::__construct();
        $this->userDao = $userDao;
        $this->env = config('app.env');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $validator = Validator::make([
            'email' => $this->argument('email'),
            'roleId' => $this->argument('roleId'),
        ], [
            'email' => [
                'required',
                Rule::unique(User::class, 'email')
            ],
            'roleId' => [
                'required',
                Rule::exists(Role::class, 'id')
            ],
        ]);

        if ($validator->fails()) {
            $this->info('User dont register. See error messages below:');

            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $name = $this->ask('Enter a name for the User', false);
        if (!$name)
        {
            $this->info("Name if required");
            return 1;
        }
        $surname = $this->ask('Enter a surname for the User (blank if not supplied)', false);
        $password = Str::random(8);

        $data = [
            'name' => $name,
            'surname' => $surname,
            'email' => $this->argument('email'),
            'roleId' => $this->argument('roleId'),
            'password' => $password,
        ];

        try
        {
            /** @var User $user */
            $user = $this->create($data);
            $authToken = $user->generateToken();
            $user->apiToken = $authToken->token;

            $this->userDao->setPremiumTariffUser($user);

            $this->info("User registered");
            $this->info("Password: $password");
        }
        catch (\Exception $exception)
        {
            Log::error($exception);
            $this->info("Sending error: {$exception->getMessage()}");
            return 1;
        }
        return 0;
    }

    protected function create(array $data) : Model
    {
        return User::query()->create([
            'account_type' => AccountType::$Individual->getValue(),
            'name' => $data['name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => $data['roleId'],
            'is_processed_personal_data' => true
        ]);
    }
}
