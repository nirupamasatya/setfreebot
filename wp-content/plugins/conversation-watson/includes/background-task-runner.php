<?php
namespace WatsonConv;

// Requiring WP Background Processing files
require_once(WATSON_CONV_PATH.'includes/background-processing/wp-async-request.php');
require_once(WATSON_CONV_PATH.'includes/background-processing/wp-background-process.php');

// Wrapper class for Watsonconv_Process
class Background_Task_Runner {
    // Description of all the fields in database scheme related to task runner,
    // their types and associated properties
    public static $schema_description = array(
        "task_runner_queue" => array(
            "id" => array("property" => "_db_id", "type" => "integer"),
            "p_callback" => array("property" => "callback", "type" => "string"),
            "p_data" => array("property" => "data", "type" => "json"),
            "p_status" => array("property" => "status", "type" => "string"),
            "s_created" => array("property" => "_db_created", "type" => "timestamp")
        )
    );

    // Batch size limit
    private static $batch_size = 100;

    // Instance of Watsonconv_Process
    public $process;

    // Gets the status of WP-Cron functionality on the site by performing a test spawn.
    // Code derived from WP-Crontrol.
    public static function perform_cron_reliability_check() {
        // Getting Wordpress version
        global $wp_version;
        // Checking if WP-Cron disabled
        if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
            return new \WP_Error( 'cron_disabled', "The DISABLE_WP_CRON constant is set to true. WP-Cron is disabled and will not run on its own.");
        }
        // Checking if alternate calling method for cron is enabled
        if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
            return new \WP_Error( 'cron_alternate', "The ALTERNATE_WP_CRON constant is set to true. This plugin cannot relibaly determine the status of your WP-Cron system.");
        }
        // Preparing and executing HTTP request
        $sslverify     = version_compare( $wp_version, 4.0, '<' );
        $doing_wp_cron = sprintf( '%.22F', microtime( true ) );

        $cron_request = apply_filters( 'cron_request', array(
            'url'  => site_url( 'wp-cron.php?doing_wp_cron=' . $doing_wp_cron ),
            'key'  => $doing_wp_cron,
            'args' => array(
                'timeout'   => 3,
                'blocking'  => true,
                'sslverify' => apply_filters( 'https_local_ssl_verify', $sslverify ),
            ),
        ) );

        $cron_request['args']['blocking'] = true;
        $result = wp_remote_post( $cron_request['url'], $cron_request['args'] );
        // Checking if request ended with error or unexpected HTTP response code
        if ( is_wp_error( $result ) ) {
            return $result;
        } else if ( wp_remote_retrieve_response_code( $result ) >= 300 ) {
            return new \WP_Error( 'cron_unexpected_http_response_code', sprintf(
                'Unexpected HTTP response code: %s',
                intval( wp_remote_retrieve_response_code( $result ) )
            ) );
        }
    }

    public static function is_cron_reliable() {
        // Timestamp of last cron reliability check
        $last_check = (integer)get_option("watsonconv_last_cron_check", 0);
        // Was WP-Cron reliable last time?
        $was_reliable_last_time = get_option("watsonconv_cron_was_reliable", "no") == "yes";
        // Current timestamp
        $timestamp = time();
        // Is it reliable now?
        $reliable = $was_reliable_last_time;

        // If last check was more than 10 minutes ago, performing new one
        if( ($timestamp - $last_check) > (10 * 60) ) {
            // Performing check
            $reliability_check_result = self::perform_cron_reliability_check();
            // If WP_Error is returned, we cannot rely on WP-Cron
            $reliable = is_wp_error($reliability_check_result) ? false : true;
            // Updating time of the last check
            update_option("watsonconv_last_cron_check", $timestamp, "yes");
            // Writing if WP-Cron was reliable this time
            $cron_works = $reliable ? "yes" : "no";
            update_option("watsonconv_cron_was_reliable", $cron_works, "yes");
        }
        
        // Returning WP-Cron status
        return $reliable;
    }

    // Background_Task_Runner constructor
    public function __construct() {
        // Checking database table and options
        self::check_installation();
        // Adding task runner's table to Storage class schema description
        \WatsonConv\Storage::$schema_description["task_runner_queue"] = self::$schema_description["task_runner_queue"];

        // Initializing $this->process after plugins loading
        add_action("plugins_loaded", array($this, "init"));
        // Collecting data
        add_action("init", array($this, "data_collector"));
    }

    // Initializing $this->process
    public function init() {
        // Requiring Watsonconv_Process file
        require_once(WATSON_CONV_PATH.'includes/watsonconv-process.php');
        // Creating Watsonconv_Process object
        $this->process = new \Watsonconv_Process();
    }

    // Task data collector
    public function data_collector() {
        // Checking if we can rely on WP-Cron
        $cron_reliable = self::is_cron_reliable();
        // If WP-Cron is unreliable, using fallback mechanism
        if($cron_reliable == false) {
            $this->fallback_handler();
            return false;
        }

        // Setting upper batch limit according to Cron reliability
        $batch_limit = 100;
        // Checking if runner is free. If busy, exiting
        if(get_option("watsonconv_runner_state") == "busy") {
            return false;
        }

        // Variable for new tasks
        $new_tasks = NULL;
        // Checking if there are new tasks
        $new_tasks = Storage::select_by_field("task_runner_queue", "p_status", "new", NULL, $batch_limit);
        // If there are no new tasks, exiting
        if(empty($new_tasks)) {
            return false;
        }
        // If there are new tasks in database, passing them to batch handler
        $this->handle_batch($new_tasks);
    }

    // Processing batch of tasks from database in fallback mode
    public function fallback_handler() {
        // Current time
        $timestamp = time();
        // batch limit
        $batch_limit = 5;

        // Determining if fallback runner is free
        $runner_free = get_option("watsonconv_fallback_state", "free") == "free";
        // Last launch time of fallback runner
        $runner_time = (integer)get_option("watsonconv_fallback_time", 0);

        // If runner is busy
        if($runner_free == false) {
            // If runner was launched less than 3 minutes ago
            if(($timestamp - $runner_time) < 180) {
                return false;
            }
        }

        // Getting new tasks from database
        $new_tasks = NULL;
        $new_tasks = Storage::select("task_runner_queue", NULL, NULL, $batch_limit);
        // If there's no new tasks, returning false
        if(empty($new_tasks)) {
            return false;
        }

        // Reporting that runner is busy and launch time
        update_option("watsonconv_fallback_state", "busy", "yes");
        update_option("watsonconv_fallback_time", $timestamp, "yes");

        // Iterating through array of tasks and working on them
        foreach($new_tasks as $raw_task) {
            // Task id
            $id = $raw_task["id"];
            // Task callback
            $callback = $raw_task["p_callback"];
            // Task data
            $data = NULL;
            if( isset($raw_task["p_data"])) {
                $data = $raw_task["p_data"];
            }
            // Executing callback function
            \Watsonconv_Process::$callback($data);
            // Deleting task from queue
            Storage::delete_by_id("task_runner_queue", $id);
        }

        // Reporting that runner is free
        update_option("watsonconv_fallback_state", "free", "yes");
    }

    // Processing batch of tasks from database
    public function handle_batch($new_tasks) {
        // Id of last processed task
        $last_id = 0;
        // Processing array of tasks and adding them to queue
        foreach($new_tasks as $task) {
            // Processing the task for adding to queue
            $processed_task = array();
            $processed_task["callback"] = $task["p_callback"];
            if(isset($task["p_data"])) {
                $processed_task["data"] = $task["p_data"];
            }
            // Pushing processed task to queue
            $this->process->push_to_queue($processed_task);
            // Updating id of last processed task
            $last_id = $task["id"];
        }
        // Getting full runner queue table name for request
        $full_table_name = \WatsonConv\Storage::get_full_table_name("task_runner_queue");
        // Marking tasks added to queue as "processing"
        $query = "UPDATE {$full_table_name} SET p_status = 'processing' WHERE id <= {$last_id}";
        // Wordpress database object
        global $wpdb;
        $rows_affected = $wpdb->query($query);
        // Reporting that runner is busy
        update_option("watsonconv_runner_state", "busy", "yes");
        // Executing queue
        $this->process->save()->dispatch();

    }

    // Function for adding new task to queue
    public static function new_task($callback, $data = NULL) {
        // Preparing task data for writing to database
        $prepared_data = array(
            "callback" => $callback,
            "data" => $data
        );
        // Writing to database, and getting id of new task
        $new_task_id = \WatsonConv\Storage::insert("task_runner_queue", $prepared_data);
        // Returning task id
        return $new_task_id;
    }

    // Function to determine if task already exists
    public static function task_already_exists($callback, $data) {
        // Preparing task data for WHERE clause
        $where_array = array(
            Storage::where("task_runner_queue", "p_callback", "=", $callback),
            Storage::where("task_runner_queue", "p_data", "=", $data)
        );
        // We need only id of task in queue
        $fields_to_get = array("id");

        $query_result = Storage::select("task_runner_queue", $where_array, $fields_to_get);
        // Checking if returned value is "empty"
        if(empty($query_result)) {
            return false;
        }
        // If there is at least one returned record, returning true
        else {
            return true;
        }
    }

    // Checking if Background Task Runner was properly installed
    // If it's not properly installed, creating options and table for tasks
    private static function check_installation() {
        // Name of option that indicates if task runner is enabled
        $enabled_option_name = "watsonconv_runner";
        // Name of option with state of task runner (free/busy)
        $state_option_name = "watsonconv_runner_state";
        // Name of option with fallback state of task runner
        $fallback_option_name = "watsonconv_fallback_state";
        // Name of option with fallback task launch time
        $fallback_time_name = "watsonconv_fallback_time";

        // Getting option
        $runner_status = get_option($enabled_option_name, "not present");
        // If there's no such option, then creating it and creating table
        if($runner_status == "not present") {
            // Wordpress global database object
            global $wpdb;
            // Wordpress collation
            $collate = '';
            if ( $wpdb->has_cap('collation') ) {
                $collate = $wpdb->get_charset_collate();
            }
            // Constructing full table name
            $full_table_name = \WatsonConv\Storage::get_full_table_name("task_runner_queue");
            // Fields array
            $table_fields = array(
                'id integer(64) UNSIGNED NOT NULL AUTO_INCREMENT',
                'p_callback varchar(256) NOT NULL',
                'p_data json',
                'p_status enum("new", "processing") DEFAULT "new"',
                's_created timestamp DEFAULT CURRENT_TIMESTAMP',
                'PRIMARY KEY  (id)'
            );
            // Constructing CREATE TABLE expression
            $fields_expression = "\n\t" . implode(",\n\t", $table_fields) . "\n";
            $full_expression = "CREATE TABLE {$full_table_name}({$fields_expression}){$collate};";

            // Wordpress file with dbDelta
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            // Writing changes to db
            dbDelta($full_expression);

            // Adding options
            // Enabled/disabled
            add_option($enabled_option_name, "enabled");
            // Free/busy normal processing
            add_option($state_option_name, "free");
            // Free/busy fallback processing
            add_option($fallback_option_name, "free");
            // Fallback queue launch time
            add_option($fallback_time_name, 0);
        }
    }
}
new Background_Task_Runner();
