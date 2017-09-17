<?php

/**
 * Class TFTM_Cron_Script
 */
class TFTM_Cron_Script {

    /**
     * Contain new tweets from Twitter
     *
     * @var null
     */
    private static $tweets_data = NULL;

    /**
     * Contain api settings for connection with twitter
     *
     * @var null
     */
    private $twitter_api_settings = array();

    /**
     * Contain coordinates and parameters for searching tweets
     *
     * @var array
     */
    private $map_search_settings = array();

    /**
     * Date from contain with twitter
     *
     * @var array
     */
    private $connaction = array();

    /**
     * TFTM_Cron_Script constructor.
     */
    public function __construct() {}

    /**
     * Create TFTM_Cron_Script class object.
     */
    public static function getInstance() {
        return new self();
    }

    /**
     * Cron script for getting new tweets from Twitter
     */
    public function tweets_cron() {
        $obj = self::getInstance();
        $obj->set_parameters();
        $obj->twitter_connection();
       if( $obj->connaction['state'] ) {
           $obj->get_tweets();
           if( $obj->tweets_data != NULL ){
               $obj->save_tweeets();
           }
       }
       file_put_contents('expiration_12_'.time().'.txt', json_encode($obj->tweets_data));
    }

    /**
     * Creating cron script schedule for a tweet update
     */
    public static function run_on_activate(){
        //$cron_tasks = get_option( 'cron' );
        if( !wp_next_scheduled( 'tftm_create_update' ) )
        {
            wp_schedule_event( time(), 'every_two_days', 'tftm_create_update' );
        }
    }

    /**
     * Creating cron script time interval for a tweet update
     */
    public static function set_cron_interval($raspisanie ) {
        $raspisanie['every_two_days'] = array(
            'interval' => 24 * 60 * MINUTE_IN_SECONDS,
            'display' => 'Every two days',
        );
        return $raspisanie;
    }

    /**
     * Set parameters for getting tweets from Twitter
     */
    private function set_parameters() {
        $this->twitter_api_settings = get_option( 'twitter_api_settings' );
        $this->map_search_settings = get_option( 'tweets_maps_settings' );
    }

    /**
     * Set connection with Twitter
     */
    private function twitter_connection() {
        $this->connaction = TFTM_List_Tweets::twetter_connection( $this->twitter_api_settings );
    }

    /**
     * Get tweets from the Twitter
     */
    private function get_tweets() {
        $this->tweets_data = TFTM_List_Tweets::get_tweets( $this->map_search_settings, $this->connaction['connection'] );
    }

    /**
     * Save tweets from the Twitter to DB
     */
    private function save_tweeets() {
        TFTM_BD_Queries::add_new_tweets( $this->tweets_data );
    }
}

?>