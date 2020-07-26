<?php


include_once('ELF_LifeCycle.php');

class ELF_Plugin extends ELF_LifeCycle {

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return array of option meta data.
     */

    public function getCurrentPostTypes()
    {
        $post_types = get_post_types( [ 'publicly_queryable'=>1 ] );
        $post_types['page'] = 'page';       // встроенный тип не имеет publicly_queryable
        unset( $post_types['attachment'] ); // удалим attachment
        return ($post_types);
    }

    public function getListPostTypes() {
        $post_types = $this->getCurrentPostTypes();
        var_dump($post_types);
        $post_types_list = "'" . implode("', '", $post_types) . "'";
        var_dump($post_types_list);
        return $post_types_list;
    }

    public function getOptionMetaData() {
        //  http://plugin.michael-simpson.com/?page_id=31
        //$post_types_list = $this->getListPostTypes();
        return array(
            //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
            'AttrNofollow' => array(__('Nofollow attribute for external links', 'my-awesome-plugin'), 'No', 'Yes'),
            'AttrBlank' => array(__('Open links in a new tab', 'my-awesome-plugin'), 'No', 'Yes'),
            //'checkPage' => array(__('Which post types work', 'my-awesome-plugin'), 'on'),
            //'AllPostTypes' => array(__('Which post types work', 'my-awesome-plugin'),
                //'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
        );
    }


//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName() {
        return 'External links filter';
    }

    protected function getMainPluginFileName() {
        return 'external-links-filter.php';
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }


    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     * @return void
     */
    public function upgrade() {
    }

    public function addActionsAndFilters() {

        // Add options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        // Example adding a script & style just for the options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        }


        // Add Actions & Filters
        // http://plugin.michael-simpson.com/?page_id=37


        // Adding scripts & styles to all pages
        // Examples:
        //        wp_enqueue_script('jquery');
        //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));


        // Register short codes
        // http://plugin.michael-simpson.com/?page_id=39


        // Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41

        $currentOptionNofollow = $this->getOption('AttrNofollow');
        $currentOptionBlank = $this ->getOption('AttrBlank');
        //var_dump($currentOptionNofollow);
        //var_dump($currentOptionBlank);

        if($currentOptionNofollow == 'Yes') {
            add_filter('the_content', 'my_nofollow');
            add_filter('the_excerpt', 'my_nofollow');

            function my_nofollow($content) {
                return preg_replace_callback('/<a[^>]+/', 'my_nofollow_callback', $content);
            }

            function my_nofollow_callback($matches) {
                $link = $matches[0];
                $site_link = get_bloginfo('url');

                if (strpos($link, 'rel') === false) {
                    $link = preg_replace("%(href=\S(?!$site_link))%i", 'rel="nofollow" $1', $link);
                } elseif (preg_match("%href=\S(?!$site_link)%i", $link)) {
                    //var_dump($link);
                    $link = preg_replace('/rel=\S(?!nofollow)\S*/i', 'rel="nofollow"', $link);
                }
                return $link;
            }
        }

        if($currentOptionBlank == 'Yes') {
            add_filter('the_content', 'my_attr_blank');
            add_filter('the_excerpt', 'my_attr_blank');

            function my_attr_blank($content)
            {
                return preg_replace_callback('/<a[^>]+/', 'my_attr_blank_callback', $content);
            }

            function my_attr_blank_callback($matches)
            {
                $link = $matches[0];
                $site_link = get_bloginfo('url');

                if (strpos($link, 'target') === false) {
                    $link = preg_replace("%(href=\S(?!$site_link))%i", 'target="_blank" $1', $link);
                } elseif (preg_match("%href=\S(?!$site_link)%i", $link)) {
                    $link = preg_replace('/target=\S(?!_blank)\S*/i', 'target="_blank"', $link);
                }
                return $link;
            }
        }

    }


}
