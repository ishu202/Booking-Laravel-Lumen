<?php


namespace R7\Booking\Models;


use Illuminate\Database\Eloquent\Model;
use R7\Booking\Models\Interfaces\ItemInterface;

class Tbltool extends Model implements ItemInterface
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('r7.booking.tables.tool'));
    }

    public function display_product_info()
    {
        return self::all()->get([
            'id',
            't_name',
            'img1'
        ]);
    }

    public function get_sellable_tool()
    {
        return self::query()->join('tblbrand',function ($join){
            /** $join self::join() */

            $join->on('tbltool.brand_id','=','tblbrand.id')
                ->where('tbltool.sell_p','!=','NULL')
                ->orWhere('tbltool.sell_p','!=',0);
        })->get([
            'tbltool.id',
            'tbltool.t_name',
            'tblbrand.b_name',
            'tbltool.ppd',
            'tbltool.sell_p',
            'tbltool.units',
            'tbltool.status',
            'tbltool.p_year'
        ]);
    }

    public function get_rentable_tool()
    {
        return self::query()->join('tblbrand',function ($join){
            /** $join self::join() */

            $join->on('tbltool.brand_id','=','tblbrand.id')
                ->where('tbltool.ppd','!=','NULL')
                ->orWhere('tbltool.ppd','!=',0);
        })->get([
            'tbltool.id',
            'tbltool.t_name',
            'tblbrand.b_name',
            'tbltool.ppd',
            'tbltool.sell_p',
            'tbltool.units',
            'tbltool.status',
            'tbltool.p_year'
        ]);
    }

    public function item_count(): int
    {
        return self::query()->count('id');
    }
}
