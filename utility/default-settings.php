<?php

$default = array(
   "language" => "en",
   "site_title" => "Online Calendar",
   "sitetabs" => array("calendar", "eventlist", "summary"),
   "default_action" => "calendar",
   "title_limit" => 8,
   "compact_title_limit" => 4,
   "title_char_limit" => 37,
   "category_key_limit" => 6,
   "show_category_key" => True,
   "include_end_times" => False,
   "default_time" => "twelve",
   "default_open_time" => False,
   "cross_links" => json_encode(
       array("Home"=>"http://{$_SERVER['HTTP_HOST']}/")),
   "email_from_address" => "",
   "default_timezone" => $_SESSION[$sprefix]["authdata"]["timezone"],
   "google_user" => "",
   "google_password" => "",
   "local_php_library" => "",
   "authcookie_max_age" => 0,
   "authcookie_path" => "authcookies"
);
// vim: set tags+=../../**/tags :
