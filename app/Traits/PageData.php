<?php


namespace R7\Booking\Traits;


trait PageData
{
    protected $indexPage;
    protected $createBookingPage;
    protected $manageBooking;
    protected $editorder;
    protected $contact;
    protected $tax_data;



    public function __construct() {
        $this->setIndexData();
        $this->setCreateBookingData();
        $this->setManageBookingData();
        $this->setTaxData();
    }

    public function setTaxData() {
        $this->tax_data = [
            'tax_data' => app('r7.booking.tbltaxrate')->get_tax_rate()
        ];
    }

    public function getTaxData() {
        return $this->tax_data;
    }

    public function getIndexData(){
        return $this->indexPage;
    }

    public function getCreateBookingData(){
        return $this->createBookingPage;
    }

    public function getEditOrderData() {
        return $this->editorder;
    }

    public function setEditOrderData($id) : self
    {
        $this->editorder =  array(
            'pTitle' => "Edit Booking",
            'tools_rent' => app('r7.booking.tbltool')->get_rentable_tool(),
            'tools_sell' => app('r7.booking.tbltool')->get_sellable_tool(),
            'disabledDates' => app('r7.booking.tblsettings')->get_disabled_dates_data(),
            'timings' => app('r7.booking.tblsettings')->get_timing_data(),
            'state' => app('r7.booking.tblstate')->fetch_state(),
            'tax_percentage' => app('r7.booking.tbltaxrate')->get_tax_rate(),
            'results' => app('r7.booking.tblstate')->get_invoice($id)
        );
        return $this;
    }

    public function getStoreContactData() {
        return app('r7.booking.tblusers')->display_contact_info();
    }

    public function setManageBookingData(): array
    {
        return $this->manageBooking = array(
            'results' => app('r7.booking.tblrinfo')->display_orders_with_user_info(),
            'item_info' => app('r7.booking.tbltool')->display_product_info(),
            'status' => app('r7.booking.tblrinfo')->get_rental_status_types()
        );
    }

    public function setCreateBookingData(): array
    {
        return $this->createBookingPage = array(
            'tools_rent' => app('r7.booking.tbltool')->get_rentable_tool(),
            'disabledDates' => app('r7.booking.tblsettings')->get_disabled_dates_data(),
            'timings' => app('r7.booking.tblsettings')->get_timing_data(),
            'state' => app('r7.booking.tblstate')->fetch_state(),
            'tax_percentage' => app('r7.booking.tbltaxrate')->get_tax_rate()
        );
    }

    public function setIndexData(): array
    {
        return $this->indexPage = array(
            'regusers' => app('r7.booking.tblusers')->user_count('tblusers'),
            'totalTool' => app('r7.booking.tbltool')->item_count('tbltool'),
            'bookings' => app('r7.booking.tblrinfo')->booking_count('tblrinfo'),
            'incoming' => app('r7.booking.tblrinfo')->display_orders_with_user_info(
                date( 'Y-m-d', time() - ( 7 * 24 * 60 * 60 ) ),
                date( 'Y-m-d' )
            ),
            'outgoing' => app('r7.booking.tblrinfo')->display_orders_with_user_info(
                date( 'Y-m-d', time() - ( 7 * 24 * 60 * 60 ) ),
                date( 'Y-m-d' )
            ),
            'brands' => app('r7.booking.tblbrand')->brand_count('tblbrand')
        );
    }
}
