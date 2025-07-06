<?php

namespace App\Observers;

use App\Models\BrandOwner;

class BrandOwnerObserver
{
    /**
     * Handle the BrandOwner "created" event.
     */
    public function created(BrandOwner $brandOwner): void
    {
        //
    }

    /**
     * Handle the BrandOwner "updated" event.
     */
    public function updated(BrandOwner $owner): void
    {
        \Log::info('BrandOwner updated', [
            'id' => $owner->id,
            'verified_owner' => $owner->verified_owner,
        ]);

        if ($owner->wasChanged('verified_owner') && $owner->verified_owner === true) {
            $brand = $owner->brand;

            if ($brand && !$brand->verified) {
                $brand->verified = true;
                $brand->brand_owner_id = $owner->id;
                $brand->save();

                \Log::info('Brand gekoppeld aan verified eigenaar', [
                    'brand_id' => $brand->id,
                    'brand_owner_id' => $owner->id,
                ]);
            }
        }
    }

    /**
     * Handle the BrandOwner "deleted" event.
     */
    public function deleted(BrandOwner $brandOwner): void
    {
        //
    }

    /**
     * Handle the BrandOwner "restored" event.
     */
    public function restored(BrandOwner $brandOwner): void
    {
        //
    }

    /**
     * Handle the BrandOwner "force deleted" event.
     */
    public function forceDeleted(BrandOwner $brandOwner): void
    {
        //
    }
}
