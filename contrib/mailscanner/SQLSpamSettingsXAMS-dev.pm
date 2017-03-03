#
#   MailScanner Custom Module SQLSpamSettingsXAMS
#   To be used with XAMS mail system (http://www.xams.org/)
#
#   SQLSpamSettingsXAMS.pm - 1.3 - 03/03/2017
#
#   This program is free software; you can redistribute it and/or modify
#   it under the terms of the GNU General Public License as published by
#   the Free Software Foundation; either version 2 of the License, or
#   (at your option) any later version.
#
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#
#   You should have received a copy of the GNU General Public License
#   along with this program; if not, write to the Free Software
#   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# This module uses entries in the user table to determine the Spam Settings
# for each user.
#
# To activate SQLNoScan use this value in MailScanner.conf:
# Use SpamAssassin = &SQLNoScan
#
# To activate SQLSpamScores use this value in MailScanner.conf:
# Required SpamAssassin Score = &SQLSpamScores
#
# To activate SQLHighSpamScores use this value in MailScanner.conf:
# High SpamAssassin Score  = &SQLHighSpamScores
#

package MailScanner::CustomConfig;

use strict 'vars';
use strict 'refs';
no  strict 'subs';              # Allow bare words for parameter %'s

use vars qw($VERSION);

### The package version, both in 1.23 style *and* usable by MakeMaker:
$VERSION = substr q$Revision: 1.3 $, 10;

use DBI;

my (%LowSpamScores, %HighSpamScores);
my (%ScanList);
my ($stime, $htime, $ntime);

# Default values
my ($refresh_time) = 5;         # Time in minutes before lists are refreshed
my ($DefaultScoreName) = "default";
my ($DefaultScore) = 5;          # Default score list
my ($DefaultHighScore) = 15;     # Default high score list

# Database setup
# Change values according to your setup
my ($db_name) = 'xams';
my ($db_host) = 'localhost';
my ($db_user) = 'xams';
my ($db_pass) = 'password';

#
# Initialise the arrays with the users Spam settings
#
sub InitSQLSpamScores {
  my ($entries) = CreateScoreList('spamscore', \%LowSpamScores);
  MailScanner::Log::InfoLog("XAMS SQLSpamSettings: Read %d SpamScores entries", $entries);
  $stime = time();
}

sub InitSQLHighSpamScores {
  my $entries = CreateScoreList('highspamscore', \%HighSpamScores);
  MailScanner::Log::InfoLog("XAMS SQLSpamSettings: Read %d HighSpamScores entries", $entries);
  $htime = time();
}

sub InitSQLNoScan {
  my $entries = CreateNoScanList('spamcheckin', \%ScanList);
  MailScanner::Log::InfoLog("XAMS SQLSpamSettings: Read %d NoScan spam entries", $entries);
  $ntime = time();
}

#
# Lookup a users Spam settings
#
sub SQLSpamScores {
  # Do we need to refresh the data?
  if ( (time() - $stime) >= ($refresh_time * 60) ) {
   MailScanner::Log::InfoLog("XAMS SQLSpamSettings: SpamScores refresh time reached");
   InitSQLSpamScores();
  }
  my ($message) = @_;
  my ($score)   = LookupScoreList($message, \%LowSpamScores);
  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: Returning %d from SQLSpamScores", $score);
  return $score;
}

sub SQLHighSpamScores {
  # Do we need to refresh the data?
  if ( (time() - $htime) >= ($refresh_time * 60) ) {
   MailScanner::Log::InfoLog("XAMS SQLSpamSettings: HighSpamScores refresh time reached");
   InitSQLHighSpamScores();
  }
  my ($message) = @_;
  my ($score)   = LookupScoreList($message, \%HighSpamScores);
  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: Returning %d from SQLHighSpamScores", $score);
  return $score;
}

#
# Lookup users not using Spam engine
#
sub SQLNoScan {
  # Do we need to refresh the data?
  if ( (time() - $ntime) >= ($refresh_time * 60) ) {
   MailScanner::Log::InfoLog("XAMS SQLSpamSettings: NoScan refresh time reached");
   InitSQLNoScan();
  }
  my ($message) = @_;
  my ($noscan)  = LookupNoScanList($message, \%ScanList);
  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: Returning %d from SQLNoScan", $noscan);
  return $noscan;
}

#
# Close down Spam Settings lists
#
sub EndSQLSpamScores {
  MailScanner::Log::InfoLog("XAMS SQLSpamSettings: Closing down XAMS SQL SpamScores");
}

sub EndSQLHighSpamScores {
  MailScanner::Log::InfoLog("XAMS SQLSpamSettings: Closing down XAMS SQL HighSpamScores");
}

sub EndSQLNoScan {
  MailScanner::Log::InfoLog("XAMS SQLSpamSettings: Closing down XAMS SQL NoScan");
}

# Read the list of users that have defined their own Spam Score value. Also
# read the XAMS SITE defaults and the XAMS system defaults.
sub CreateScoreList
{
  my ($type, $UserList) = @_;
  my ($dbh, $sth, $sql, $username, $count, $admin, $aliases, $default);

  $admin = $type;
  $aliases = $type;
  $default = $type;

  # Connect to the database
  $dbh = DBI->connect("DBI:mysql:database=$db_name;host=$db_host",
       $db_user, $db_pass,
       { PrintError => 0, RaiseError => 1, mysql_enable_utf8 => 1 }
  );

  # Check if connection was successfull - if it isn't
  # then generate a warning and return to MailScanner so it can continue processing.
  if (!$dbh)
  {
		MailScanner::Log::InfoLog("XAMS SQLSpamSettings: CreateList Unable to initialise database connection: %s",
		      $DBI::errstr);
		return;
		
		$dbh->do('SET NAMES utf8');
		
		# Store default value
		if ($default eq "spamscore") {
		      #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: Using default %s values.",$default);
		      $UserList->{lc($DefaultScoreName)} = $DefaultScore; # Store entry
		      $count++;
		}
		if ($default eq "highspamscore") {
		       #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: Using default %s values.",$default);
		       $UserList->{lc($DefaultScoreName)} = $DefaultHighScore; # Store entry
		       $count++;
		}
        return $count;
  }

  # SQL query in the XAMS database
  # For Users of ScoreList
  $sql = " \
  SELECT      LOWER( CONCAT( u.name, '\@', d.name ) ) AS username, u.$type \
  FROM        pm_sites AS s \
  INNER JOIN  pm_domains d ON s.id = d.siteid \
  INNER JOIN  pm_users u ON s.id = u.siteid \
  ";

  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: SQL request ScoreList Default value = %s", $sql);

  $sth = $dbh->prepare($sql);
  $sth->execute;
  $sth->bind_columns(undef, \$username, \$type);
  $count = 0;
  while($sth->fetch())
  {
    #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: -----------------------username : %s", $username);
    #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: -----------------------score : %s", $type);
    $UserList->{lc($username)} = $type; # Store entry
    $count++;
  }

  # SQL query in the XAMS database
  # For Aliases of ScoreList
  $sql = " \
  SELECT      LOWER( CONCAT( a.leftpart, '\@', d.name ) ) AS Username, u.$aliases \
  FROM        pm_sites AS s \
  INNER JOIN  pm_domains d ON s.id = d.siteid \
  INNER JOIN  pm_users u ON s.id = u.siteid \
  INNER JOIN  pm_aliases a ON s.id = a.siteid \
  WHERE       a.rightpart = u.name \
  ";

  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: SQL request ScoreList Default value = %s", $sql);

  $sth = $dbh->prepare($sql);
  $sth->execute;
  $sth->bind_columns(undef, \$username, \$type);
  while($sth->fetch())
  {
    #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: -----------------------username-Aliases : %s", $username);
    #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: -----------------------score : %s", $type);
    $UserList->{lc($username)} = $type; # Store entry
    $count++;
  }

  # SQL query in the XAMS database
  # For default value
  $sql = " \
  SELECT      p.admin AS username, p.$admin \
  FROM        pm_preferences AS p \
  ";

  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: SQL request ScoreList Default value = %s", $sql);

  $sth = $dbh->prepare($sql);
  $sth->execute;
  $sth->bind_columns(undef, \$username, \$type);
  while($sth->fetch())
  {
    #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: -----------------------username-Admin : %s", $username);
    #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: -----------------------type : %s", $type);
    $UserList->{lc($username)} = $type; # Store entry
    $count++;
  }

  # Close connections
  $sth->finish();
  $dbh->disconnect();

  return $count;
}

# Read the list of users that have defined that don't want Spam scanning.
sub CreateNoScanList {
  my ($type, $NoScanList) = @_;

  my ($dbh, $sth, $sql, $username, $count, $aliases, $sstatus, $ustatus);

  $aliases = $type;

  # Connect to the database
  $dbh = DBI->connect("DBI:mysql:database=$db_name;host=$db_host", $db_user, $db_pass, {PrintError => 0 });

  # Check if connection was successfull - if it isn't
  # then generate a warning and return to MailScanner so it can continue processing.
  if (!$dbh)
  {
    MailScanner::Log::InfoLog("XAMS SQLSpamSettings: CreateNoScanList Unable to initialise database connection: %s",
        $DBI::errstr);
    return;
  }

  $dbh->do('SET NAMES utf8');

  # SQL query in the XAMS database
  # For users of NoScanList
  $sql = " \
  SELECT      LOWER( CONCAT( u.name, '\@', d.name ) ) AS username, u.$type \
  FROM        pm_sites AS s \
  INNER JOIN  pm_domains d ON s.id = d.siteid AND s.sitestate !='lockedbounce' \
  INNER JOIN  pm_users u ON s.id = u.siteid \
  WHERE       ((s.$type  = 'false' OR s.$type IS NULL) OR u.$type != 'true') \
  ";

  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: SQL request ScoreList Default value = %s", $sql);

  $sth = $dbh->prepare($sql);
  $sth->execute;
  $sth->bind_columns(undef, \$username, \$type);
  $count = 0;
  while($sth->fetch())
  {
    #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: -----------------------username-NoScan : %s", $username);
    #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: -----------------------type : %s", $type);
    $NoScanList->{lc($username)} = 1; # Store entry
    $count++;
  }

  # SQL query in the XAMS database
  # For Aliases of NoScanList
  $sql = " \
  SELECT      DISTINCT( LOWER( CONCAT( a.leftpart, '\@', d.name ) )) as Username, u.$aliases, s.$aliases \
  FROM        pm_sites s \
  INNER JOIN  pm_domains d ON s.id = d.siteid AND s.sitestate !='lockedbounce' \
  INNER JOIN  pm_users u ON s.id = u.siteid \
  INNER JOIN  pm_aliases a ON s.id = a.siteid \
  WHERE       a.rightpart = u.name \
 ";

  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: SQL request ScoreList Default value = %s", $sql);

  $sth = $dbh->prepare($sql);
  $sth->execute;
  $sth->bind_columns(undef, \$username, \$ustatus, \$sstatus);
  while($sth->fetch())
  {
    #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: -----------------------username-NoScan-Aliases : %s", $username);
    #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: -----------------------Sstatus : %s", $sstatus);
    #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: -----------------------Ustatus : %s", $ustatus);
    if ( $sstatus eq "false" ) {
       #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: ---------username-NoScan-Aliases-stored : %s", $username);
       #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: ---------type : %s", $type);
       $NoScanList->{lc($username)} = 1; # Store entry
       $count++;
    } else {
      if ( $ustatus eq "false" ) {
         #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: ---------username-NoScan-Aliases-stored : %s", $username);
         #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: ---------type : %s", $type);
         $NoScanList->{lc($username)} = 1; # Store entry
         $count++;
      }
    }
  }

  # Close connections
  $sth->finish();
  $dbh->disconnect();

  return $count;
}

# Based on the address it is going to, choose the correct Spam score.
# If the actual "To:" user is not found, then use the domain defaults
# as supplied by the domain administrator. If there is no domain default
# then fallback to the system default as defined in the "admin" user.
# If the database is done, the system use the SQLSpamSettingsXAMS.pm
# internal default values for Score and HighScore.
#
sub LookupScoreList {
  my ($message, $LowHigh) = @_;

  return 0 unless $message; # Sanity check the input

  # Find the first "to" address
  my (@to, $to);
  @to         = @{$message->{to}};
  $to         = $to[0];

  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: LookupScoreList");
  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: ---------------to-message : %s", @to);
  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: ---------------to : %s", $to);
  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: ---------------LowHigh : %s", $LowHigh->{$to});
  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: ---------------Default value LowHigh : %s", $LowHigh->{"default"});

  # It is in the list with the exact address? if not found,
  # if that's not found,  get the system default otherwise return a high
  # value to just let the email through.
  return $LowHigh->{$to}         if $LowHigh->{$to};
  return $LowHigh->{"admin"}     if $LowHigh->{"admin"};
  return $LowHigh->{"default"}   if $LowHigh->{"default"};

  # There are no Spam scores to return if we made it this far, so let the email through.
  return 999;
}

# Based on the address it is going to, decide whether or not to scan.
# the users email for Spam.
sub LookupNoScanList {
  my ($message, $NoScan) = @_;

  return 0 unless $message; # Sanity check the input

  # Find the first "to" address
  my (@to, $to);
  @to         = @{$message->{to}};
  $to         = $to[0];

  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: ---------------to-message : %s", @to);
  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: ---------------to : %s", $to);
  #MailScanner::Log::InfoLog("XAMS SQLSpamSettings: ---------------NoScan : %s", $NoScan->{$to});

  # It is in the list with the exact address?
  # if that's not found, return 0
  return 0 if $NoScan->{$to};

  # There is no setting, then go ahead and scan for Spam, be on the safe side.
  return 1;
}

1;
