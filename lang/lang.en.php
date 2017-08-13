<?php
/* Admin Stuff *********************** */

// denied messages
$lang['accessdenied'] = "Authorization Required.  You are not logged in.";
$lang['accessdenied-cookie'] = "Authorization Required. Please reauthenticate.";
$lang['subscribers must log in'] = "Please log in as a subscriber to set up reminders.";

// eventform.php
$lang['editeventtitle'] = "Event Edit Screen";
$lang['editheader'] = "Edit Event";
$lang['editbutton'] = "Update";
$lang['resetbutton'] = "Reset Form Values";
$lang['updated'] = "Updated";
$lang['addeventtitle'] = "Event Add Screen";
$lang['addheader'] = "Add New Event";
$lang['addbutton'] = "Post";
$lang['added'] = "Added";
$lang['datetext'] = "Date";
$lang['title'] = "Title";
$lang['text'] = "Text";
$lang['starttime'] = "Start Time";
$lang['endtime'] = "End Time";
$lang['timezone'] = "Time Zone";
$lang['Include Related'] = "Include Related";
$lang['future only'] = "Future Only";
$lang['All Day'] = "All Day";
$lang['category'] = "Category";
$lang['unspecified'] = "Unspecified";
$lang['new-category'] = "New Category: ";
$lang['missingevent'] = "Error: Event does not exist.";
$lang['blanktitle'] = "Error: Every event must have a title.";

// eventsubmit.php
$lang['event deleted'] = "The event has been deleted.";
$lang['operationcancelled'] = "The operation was cancelled.";

// categoryadmin.php
$lang['admincategorytitle'] = "Category Administration";
$lang['admincategoryheader'] = "Make Changes to Categories";
$lang['submitbutton'] = "Submit";
$lang['savebutton'] = "Save and Continue Modifications";

// categories
$lang['hidecategorynote'] = "Hidden categories are not visible to visitors.";
$lang['delcategorynote'] = "Deleting a category also deletes all its events.";
$lang['categorystylenote'] = "Improper style specifications will be ignored by your browser.";
$lang['hide'] = "Hide";
$lang['suppress key listing'] = "Suppress in Key";
$lang['confirmdelete'] = "Check here to confirm deletion of the selected items.";
$lang['checkall'] = "All in column";

// admincategorysubmit.php
$lang['categorieshidden'] = "Hidden categories: ";
$lang['categoriesdeleted'] = "These categories and their events have been deleted: ";
$lang['categoriessuppressed'] = "These categories will not appear in the Category Key: ";
$lang['categoriesrenamed'] = "Renamed categories: ";
$lang['categoriesrestyled'] = "Restyled categories: ";
$lang['hidestr'] = "Hide";
$lang['deletestr'] = "Delete";
$lang['relatestr'] = "Relate";
$lang['abortnoconfirm'] = "Aborting: the 'confirm' box was not checked.";
$lang['newname'] = "New Category Name";
$lang['style'] = "Title Style Properties";

// copyform.php
$lang['duration'] = "Duration";
$lang['titlemissing'] = "Please include a title.";
$lang['repeatskipnan'] = "Repeat Skip must be a number.";
$lang['copyheader'] = "Copy Event";
$lang['repeattype'] = "Repeat: ";
$lang['singlerepeat'] = "Single";
$lang['dailyrepeat'] = "Daily";
$lang['weeklyrepeat'] = "Weekly";
$lang['monthlydayrepeat'] = "Monthly on Day";
$lang['monthlydaterepeat'] = "Monthly on Date";
$lang['annualrepeat'] = "Annually";
$lang['repeatskip'] = "Repeat Skip:";
$lang['repeatcount'] = "Repeat Count:";
$lang['Zero = Use date instead'] = "Zero = Use date instead";
$lang['Extra time spans between each repetition.'] = "Extra time spans between each repetition.";
$lang['repeatcutoff'] = "Repeat on/until: ";
$lang['datescopied'] = "Dates Copied: ";
$lang['make copies related'] = "Make Copies Related: ";

// login.php
$lang['login'] = "Login";
$lang['logout'] = "Logout";
$lang['logintitle'] = "Login Page";
$lang['loginheader'] = "Calendar Login";
$lang['wronglogin'] = "Wrong Username or Password";
$lang['username'] = "Username";
$lang['password'] = "Password";
$lang['accesswarning'] = "You can't do that.";
$lang['logged in-0'] = "You are logged in. You can set up event reminders.";
$lang['logged in-1'] = "You are logged in. You will now see hidden events.";
$lang['logged in-2'] = "You are logged in. You can now create new events and edit existing events. Notice that the menu at the bottom of the screen has changed.";
$lang['logged in-3'] = "You are logged in. You can now edit and create events, as well as modify the list of allowed users.";
$lang['logged out'] = "You are now logged out.";
$lang['forgot password'] = "Forgot my password";
$lang['setup subscriber'] = "Subscribe to reminders";

// useradmin.php
$lang['ulistheader'] = "Calendar Users";
$lang['deleteconf'] = "Are you sure you want to delete user";
$lang['deleteown'] = "Sorry. You cannot delete your own username.";
$lang['adduser'] = "Add User";
$lang['return'] = "Return to Calendar";
$lang['username'] = "Username";
$lang['name'] = "Name";
$lang['email'] = "Email";
$lang['userlevel'] = "User Level";
$lang['edit'] = "Edit";
$lang['delete'] = "Delete";
$lang['admin'] = "Admin";
$lang['editor'] = "Editor";
$lang['logins'][1] = "User";
$lang['logins'][2] = "Editor";
$lang['logins'][3] = "Admin";
$lang['account created'] = "Account Created";

$lang['chpassheader'] = "Change Password";
$lang['pwconfirm'] = "Confirm Password";
$lang['no change'] = "No Change";
$lang['changepw'] = "Change Password";
$lang['useradmin'] = "Edit Users";
$lang['cancel'] = "Cancel";
$lang['adminoption'] = "Administrator (can add users)";
$lang['editoroption'] = "Editor (cannot add users)";
$lang['useroption'] = "User (can see privileged entries)";
$lang['subscriberoption'] = "Subscriber (can receive email notices)";

$lang['fnameblank'] = "The first name field is blank.  Please enter a first name";
$lang['lnameblank'] = "The last name field is blank.  Please enter a last name";
$lang['emailblank'] = "The email field is blank.  Please enter a valid email address";
$lang['unameblank'] = "The username field is blank.  Please enter a username";
$lang['unamelength'] = "The username must be at least 4 characters in length.  Please enter a new username";
$lang['unameillegal'] = "The username may only contain alpha-numeric characters.  Spaces are also not allowed.  Please try again";
$lang['pwblank'] = "The password field is blank.  Please enter a password";
$lang['pwmatch'] = "The password values you entered do not match.  Please retype the password.";
$lang['pwlength'] = "The password must be at least 4 characters in length.  Please enter a new password";
$lang['pwchars'] = "The password may only contain alpha-numeric characters.  Spaces are also not allowed.  Please try again";


$lang['fname'] = "First Name";
$lang['lname'] = "Last Name";

$lang['pwchanged'] = "Password changed for user ";
$lang['problem changing password'] = "There was a problem updating the password.";

$lang['userinuse'] = "The username you selected is already in use.";
$lang['emailinuse'] = "The email address you selected is already in use.";
$lang['edituser'] = "Edit Calendar User";
$lang['adduser'] = "Add Calendar User";

$lang['userspresent'] = "User records already exist.  Please log in as one of them.";
$lang['commit'] = "Commit";

$lang["invalid or expired reset auth"] = "Invalid or expired reset code.";
$lang['pwresetsubject'] = "Password reset request";
$lang['pwresetmessage'] = "
Either you or someone else requested a password reset at our calendar
web site, and we have your email address on record for that purpose.  If
you think you are receiving this in error, it may be safely ignored.  To
continue resetting your password, visit the link below in your web
browser.  The link will expire after six days." ;

// config.php
$lang['backtoconfiguration'] = "Back to Configuration";
$lang['version_major'] = "Major Version Number";
$lang['version_minor'] = "Minor Version Number";
$lang['version_tick'] = "Minor Version Increment";
$lang['language'] = "Language Code";
$lang['site_title'] = "Site Title";
$lang['sitetabs'] = "Site Tabs (one per line)";
$lang['default_action'] = "Default Tab";
$lang['title_limit'] = "Max Events Per Day in Month View";
$lang['compact_title_limit'] = "Max Events/Day when Compact";
$lang['title_char_limit'] = "Max Characters in Month View Title";
$lang['category_key_limit'] = "Max Categories in Month View Key";
$lang['show_category_key'] = "Display Category Key in Month View?";
$lang['include_end_times'] = "Display End Times in Month View?";
$lang['default_time'] = "Use 12 or 24-hour time?";
$lang['default_open_time'] = "Show Open Time by Default in Event View?";
$lang['cross_links'] = "Cross-site links (JSON)";
$lang['email_from_address'] = "From Address for Password Reset Messages";
$lang['google_user'] = "Username for Google Calendar Export";
$lang['google_password'] = "Password for Google Calendar Export";
$lang['default_timezone'] = "Default Timezone for New Events";
$lang['local_php_library'] = "PHP Library for Google Export";
$lang['authcookie_max_age'] = "Age (in days) to extend logins via cookie";
$lang['authcookie_path'] = "Path for managing extended auth cookies";
$lang['remotes'] = "Available remote installations and categories";
$lang['configuration history'] = "Configuration History";
$lang['config history deleted'] = "The selected configuration(s) have been deleted from the history.";
$lang['configuration selected'] = "The chosen configuration has been reactivated.";

// sendpw.php
$lang['sendpwtitle'] = "Forgotten Password";
$lang['sendpwinstruction'] = "Enter either your user name or email
address below, and if it exists in our user database, you will be sent
an email at the address we have recorded.  In that message, you will
find a link that will allow you to set a new password.";
$lang['find and send'] = "Send a Reset Link";
$lang['no users found'] = "No users found with that name or email";
$lang['password reset sent'] = "An email was sent to you with a link to reset your password.";

// initialuser.php
$lang['initialusertitle'] = "Initial User";

// db initialization
$lang['badsettings'] = "Something is wrong here.  Please change the settings and try again.";
$lang['dbhost'] = "Database Host Name/Address";
$lang['dbname'] = "Database Name";
$lang['dbuser'] = "DB Login Name";
$lang['dbpassword'] = "DB Login Password";
$lang['dbtableprefix'] = "Table Prefix (for DB sharing)";

// DB Restore
$lang['restoretitle'] = "Restore from Database Backup";
$lang['selectfile'] = "Please select the backup (dump) file.";
$lang['send'] = "Send";
$lang['problemuploadingbackup'] = "Problem uploading backup file.";
$lang['restoresucceeded'] = "Restore succeeded.";

// Doing things with filtered events
$lang['deletefiltered'] = "Delete Filtered Events";
$lang['relatefiltered'] = "Make Filtered Events Related";
$lang['retitlefiltered'] = "Change Title of Filtered Events";
$lang['retextfiltered'] = "Change the Text Description of Filtered Events";
$lang['retimefiltered'] = "Change the Time of Filtered Events";
$lang['recategoryfiltered'] = "Change the Category of Filtered Events";
$lang['sendtogoogle'] = "Add to Google Calendar";
$lang['events deleted'] = " Events deleted. ";
$lang['events related'] = " Events are now related. ";
$lang['events retitled'] = " Events' titles changed to ";
$lang['events retexted'] = " Events' texts changed.";
$lang['events retimed'] = " Events' times changed to ";
$lang['events recategorized'] = " Events recategorized to ";
$lang['already related'] = "These events are already related!";

/* Non-Admin Stuff ********************** */

// calendar view
$lang['help'] = "Help";
$lang['manual'] = "Manual";
$lang['allsetup'] = "Congratulations! Flexical is now set up.";
$lang['current'] = "Show&nbsp;Current";
$lang['month count'] = "Month Count";
$lang['months'] = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$lang['days'] = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
$lang['abrvdays'] = array("Sun", "Mon", "Tue", "Wed", "Thur", "Fri", "Sat");
$lang['Su Mo Tu We Th Fr Sa'] = 'Su Mo Tu We Th Fr Sa';
$lang['generationdate'] = "UTC/GMT Timestamp: ";
$lang['modificationdate'] = "Last update:";
$lang['writesqlerror'] = "Problem writing SQL file for backup restoration.";
$lang['cancel'] = "Cancel";
$lang['tabtext-calendar'] = "Calendar Month";
$lang['tabtext-eventlist'] = "Event List";
$lang['tabtext-eventdisplay'] = "Event";
$lang['tabtext-summary'] = "Range Summary";
$lang['tabtext-customstyles'] = "Custom Stylesheet Editor";
$lang['catkeytitle'] = "Category Key";
$lang['filternotice'] = "The events here are filtered.  Click here to remove the filter.";
$lang['extra-message-placeholder'] = "Before printing, you may type an extra message in this area that will be printed but not saved.";
$lang['Remote Event'] = "Imported Event";

// eventdisplay view
$lang['otheritems'] = "Also On This Day:";
$lang['deleteconfirm'] = "Are you sure you want to delete this item?";
$lang['futureonlyconfirm'] = "Limit delete to this and future related events?";
$lang['postedby'] = "Last edited by";
$lang['allday'] = "All Day Event";
$lang['backtocalendar'] = "Back to Calendar";
$lang['previous related'] = "previous related";
$lang['next related'] = "next related";
$lang['no id provided'] = "Please choose an event first from another view.";
$lang['hour'] = "Hour";

// Custom stylesheet editor
$lang['custom stylesheet editor'] = "Custom Stylesheet Editor";
$lang['customstyles'] = "Custom Stylesheet";

// index.php
$lang['adminlnk'] = "User Admin";
$lang['DBDump'] = "Backup";
$lang['DBRestore'] = "Restore";
$lang['modcategories'] = "Modify Categories";
$lang['addanevent'] = "Add an Event";
$lang['batchdeleteconfirm'] = "Are you sure you want to delete all matching items?";
$lang['batchrelateconfirm'] = "Are you sure you want to make all matching items related (existing relations will be overwritten)?";

// eventlist view
$lang['eventtitle'] = "Event List";
$lang['date'] = "Date";
$lang['starttime'] = "Starts";
$lang['endtime'] = "Ends";
$lang['title'] = "Title";
$lang['description'] = "Description";
$lang['category'] = "Category";
$lang['units'] = array("days", "weeks", "months", "years");
$lang['year'] = "Year";
$lang['month'] = "Month";
$lang['day'] = "Day";
$lang['span'] = "Span";
$lang['totalfound'] = "Total records found in this time span:";
$lang['opentime'] = "Open time";

// summary view
$lang['print'] = "Print";
$lang['show day tally'] = "Tally";
$lang['direct link'] = "Direct link to this summary page";
$lang['boxed dates appear here'] = "Boxed dates appear in this list.";

// categories
$lang['show'] = "Show";
$lang['choosecategorytitle'] = "Category Chooser";
$lang['categoryheader'] = "Select the categories to display.";
$lang['categorybutton'] = "Update";
$lang['categoryset'] = "Your category selection has been saved.";
$lang['all'] = "All";
$lang['none'] = "None";

// footprint
$lang['categories'] = "Categories";
$lang['timeformat'] = "Time Format";
$lang['astable'] = "Month as Table";
$lang['toggle'] = "Toggle";
$lang['Events Filtered'] = 'Events Filtered';
$lang['Events Unfiltered'] = 'Events Unfiltered';
$lang['Choose'] = 'Choose';
$lang['yes'] = 'Yes';
$lang['no'] = 'No';
$lang['logged out'] = "Logged Out";
$lang['configure'] = "Configure" ;
$lang['usertz'] = " User Time Zone";
$lang['on'] = "on";
$lang['off'] = "off";

// search form
$lang['searchbutton'] = "Find Matching Events";
$lang['cancel'] = "Cancel";

// input errors
$lang['daynumeric'] = "The day must be numeric.";
$lang['monnumeric'] = "The month must be numeric.";
$lang['yearnumeric'] = "The year must be numeric.";

// Action icon text
$lang['show'] = "Show";
$lang['remind'] = "Remind";
$lang['delete'] = "Delete";
$lang['edit'] = "Edit";
$lang['copy'] = "Copy";
$lang['delete related'] = "Delete Related";
$lang['show related'] = "Show Related";

// filter
$lang['filter'] = "Filter";
$lang['filtertitle'] = "Filter Events";
$lang['filtersubmitstr'] = "Submit Filter";
$lang['filterremovestr'] = "Remove Current Filter";
$lang['filterremoved'] = "The filter has been removed.  You can now see all items in the selected categories.";
$lang['already unfiltered'] = "There is no filter to remove.";
$lang['unfilter'] = "Unfilter";
$lang['unfiltered'] = "Unfiltered";
$lang['filterset'] = "A filter has been set.  You can only see items that match it.";
$lang['emptyfilter'] = "You have selected no filter conditions, so the filter was not set.";
$lang['time'] = "Time";
$lang['weekday'] = "Weekday";
$lang['showing related events'] = "Showing all events related to the one you chose.";
$lang['regexphelptext'] = "This form allows you to set or remove a filter on the events that will be displayed.  You can search based upon the {$lang['title']}, {$lang['description']}, {$lang['starttime']} or {$lang['endtime']} fields, whether the event falls on certain weekdays, or some combination of all these parameters.  The {$lang['title']} and {$lang['description']} fields are extended regular expressions.  They will match only a part of the actual text in that event field, and they are not case sensitive.  To match the entire text for that field, prefix your search text with '^' and end it with '$'.  For more information, see a reference on regular expressions.  Also, please note that events are further filtered by the categories you have selected and the date range being displayed.";
$lang['config history helptext'] = "This is the history of all configurations recorded in this calendar installation.  The newest configuration is on the left, with progressively older ones to the right.  Older configurations only show the fields that differ from the newer configurations to their left.  To make an old configuration current, click on its timestamp at the top of the column.  (Be warned that some version upgrades introduce database incompatibilities.)  To delete any configurations from the history, check the box near the timestamp and click the Delete button.";

// Reminders
$lang['Reminder exists'] = "You already have a reminder set up for that.";
$lang['Remind Header'] = "Reminder Setup";

// SiteTabs
$lang['unknown template:'] = "Unknown Template: ";

// vim: set tags+=../../**/tags :
