<?php
declare(strict_types=1);

namespace R7\Booking\Providers;

use Illuminate\Support\ServiceProvider;
use R7\Booking\Models\Tblbrand;
use R7\Booking\Models\Tblguest;
use R7\Booking\Models\Tblmodorders;
use R7\Booking\Models\Tblrefundorder;
use R7\Booking\Models\Tblrinfo;
use R7\Booking\Models\Tblsettings;
use R7\Booking\Models\Tblstate;
use R7\Booking\Models\TblstripeCustomers;
use R7\Booking\Models\Tbltaxrate;
use R7\Booking\Models\Tbltool;
use R7\Booking\Models\Transaction;
use R7\Booking\Models\User;
use Rinvex\Support\Traits\ConsoleTools;

class BookingServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'r7.booking');

        /**
         * {@inheritdoc }
         */
        $this->registerModels([
            'r7.booking.tblrinfo' => Tblrinfo::class,
            'r7.booking.tblmodorders' => Tblmodorders::class,
            'r7.booking.tblrefundorder' => Tblrefundorder::class,
            'r7.booking.transaction' => Transaction::class,
            'r7.booking.tblstripeCustomers' => TblstripeCustomers::class,
            'r7.booking.tblusers' => User::class,
            'r7.booking.tblguest' => Tblguest::class,
            'r7.booking.tbltool' => Tbltool::class,
            'r7.booking.tbltaxrate' => Tbltaxrate::class,
            'r7.booking.tblsettings' => Tblsettings::class,
            'r7.booking.tblbrand' => Tblbrand::class,
            'r7.booking.tblstate' => Tblstate::class
        ]);
    }

    public function boot()
    {
        $this->publishesConfig('r7booking/ishu8957');
        $this->publishesMigrations('r7booking/ishu8957');
        ! $this->autoloadMigrations('r7booking/ishu8957') || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

}
