<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;
use R7\Booking\Models\Interfaces\SettingsInterface;

class Tblsettings extends Model implements SettingsInterface
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('r7.booking.tables.settings'));
    }

    public function get_timing_data()
    {
        // TODO: Implement get_timing_data() method.
    }

    public function get_disabled_dates_data()
    {
        // TODO: Implement get_disabled_dates_data() method.
    }
}
