<?php

namespace App\Db\Service;

use App\Db\Entity\Promotion\AffiliatedPerson;
use App\Http\Requests\Promotion\AddAffiliatedPersonRequest;
use App\Http\Requests\Promotion\UpdateAffiliatedPersonRequest;
use Carbon\Carbon;
use PhpParser\Node\Scalar\String_;

class AffiliatedPersonDao
{
    /** @returns AffiliatedPerson | null */
    public function first(int $id): ?AffiliatedPerson
    {
        /** @var  AffiliatedPerson $affiliatedPerson */
        $affiliatedPerson = AffiliatedPerson::query()
            ->where('id', $id)
            ->first();

        return $affiliatedPerson;
    }


    public function list()
    {
        /** @var  AffiliatedPerson $affiliatedPersonList */
        $affiliatedPersonList = AffiliatedPerson::query();
        return $affiliatedPersonList;
    }

    /**
     * @param AddAffiliatedPersonRequest $request
     * @return AffiliatedPerson
     */
    public function add(AddAffiliatedPersonRequest $request): AffiliatedPerson
    {
        $affiliatedPerson = new AffiliatedPerson();
        $affiliatedPerson->name = $request->name;
        $affiliatedPerson->email = $request->email;
        $affiliatedPerson->phone = $request->phone;
        $affiliatedPerson->city = $request->city;
        $affiliatedPerson->promo_code = $this->generatePromoCode($request->name);

        $affiliatedPerson->save();

        return $affiliatedPerson;
    }

    /**
     * @param AffiliatedPerson $affiliatedPerson
     * @param UpdateAffiliatedPersonRequest $request
     * @return AffiliatedPerson
     */
    public function update(AffiliatedPerson $affiliatedPerson, UpdateAffiliatedPersonRequest $request): AffiliatedPerson
    {
        $affiliatedPerson->name = $request->name;
        $affiliatedPerson->email = $request->email;
        $affiliatedPerson->phone = $request->phone;
        $affiliatedPerson->city = $request->city;

        $affiliatedPerson->save();

        return $affiliatedPerson;
    }

    /**
     * @param AffiliatedPerson $affiliatedPerson
     */
    public function delete(AffiliatedPerson $affiliatedPerson)
    {
        $affiliatedPerson->delete();
    }

    /**
     * @param $affiliatedPersonName
     * @return string
     */
    public function generatePromoCode($affiliatedPersonName): String
    {
        $frontUrl = config('app.front_url');

        $promoCode = $frontUrl. '/' .base64_encode($affiliatedPersonName);

        return $promoCode;
    }

    /**
     * @param $promo
     * @return AffiliatedPerson
     */
    public function getByPromo($promo): AffiliatedPerson
    {
        $affiliatePersonName = $this->decodePromoCode($promo);
        /** @var AffiliatedPerson $affiliatePerson */
        $affiliatePerson = AffiliatedPerson::query()
            ->where('name', $affiliatePersonName)
            ->first();

        return $affiliatePerson;
    }
    /**
     * @param $promo
     * @return String
     */
    public function decodePromoCode($promo): String
    {
        $frontUrl = config('app.front_url');

        $promo = str_replace($frontUrl. '/', '', $promo);

        $promoCode = base64_decode($promo[0]);

        return $promoCode;
    }
}
