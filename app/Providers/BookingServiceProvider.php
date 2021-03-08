<?php
declare(strict_types=1);

namespace R7\Booking\Providers;

use Illuminate\Support\ServiceProvider;
use R7\Booking\Models\Tblmodorders;
use R7\Booking\Models\Tblrefundorder;
use R7\Booking\Models\Tblrinfo;
use R7\Booking\Models\Transaction;
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
            'r7.booking.tranaction' => Transaction::class
        ]);
    }

    public function boot()
    {
        $this->publishesConfig('r7booking/ishu8957');
        $this->publishesMigrations('r7booking/ishu8957');
        ! $this->autoloadMigrations('r7booking/ishu8957') || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

}
