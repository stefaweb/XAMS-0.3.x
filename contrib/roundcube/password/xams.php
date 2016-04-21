<?php

/**
 * XAMS Password Driver
 *
 * Driver for passwords stored in XAMS database
 *
 * Version 0.2.0 (to be used with RoundCube 0.9.x and later)
 *
 * By Stephane Leclerc <sleclerc@actionweb.fr>
 *
 */

class rcube_xams_password
{

   function save($curpass, $passwd)
   {
       $rcmail = rcmail::get_instance();

       $dsn = array();
       if (is_array($dsn) && empty($dsn['new_link']))
          $dsn['new_link'] = true;
       else if (!is_array($dsn) && !preg_match('/\?new_link=true/', $dsn))
          $dsn .= '?new_link=true';

       // CHANGE TO YOUR OWN SETTING
       $dsn = 'mysql://user:password@localhost/database';
       // Don't change below this line

       // to avoid error with version 0.9 and maintain backward compatibility
       if (!class_exists('rcube_db'))
       {
           $db = new rcube_mdb2($dsn, '', FALSE);
       }
       else
       {
           $db = rcube_db::factory($dsn, '', FALSE);
       }
       $db->set_debug((bool)$rcmail->config->get('sql_debug'));
       $db->db_connect('w');

       if ($err = $db->is_error())
       {
           return PLUGIN_ERROR_CONNECT;
       }

       $user_info = explode('@', $_SESSION['username']);

       if (count($user_info) == 2) 
       {
           $user = $user_info[0];
           $domain = $user_info[1];
       }
       else
       {
           return PLUGIN_ERROR_PROCESS;
       } 

       $sql = "UPDATE _users SET password = ";
       $sql = str_replace('%h', $db->quote($_SESSION['imap_host'],'text'), $sql);
       $sql = str_replace('%p', $db->quote($passwd,'text'), $sql);

       # TODO: make it work for unique users
       $types = array('text');
       $sql = 'SELECT siteid FROM pm_domains WHERE name = %domainname';
       $sql = str_replace('%domainname', $db->quote($domain,'text'), $sql);
       $res = $db->query($sql);
       if (!$db->is_error())
       { 
           $values = $db->fetch_array($res);
       }
       else
       {
           return PLUGIN_ERROR_PROCESS;
       }

       if (count($values) < 1)
       {
           return PASSWORD_ERROR;
       }

       $siteid = $values[0];
    
       $sql = 'UPDATE pm_users SET password = :newpass WHERE siteid = :siteid AND name = :username';
       $sql = str_replace(':siteid', $db->quote($siteid,'integer'), $sql);
       $sql = str_replace(':newpass', $db->quote(md5($passwd),'text'), $sql);
       $sql = str_replace(':username', $db->quote($user,'text'), $sql);
       $res = $db->query($sql);

       if (!$db->is_error())
       {
               return PLUGIN_SUCCESS;
       }
       return PLUGIN_ERROR_PROCESS;
   }
}

?>
