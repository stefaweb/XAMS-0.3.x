#!/bin/bash

#set -x

# if user is root only
if [ $UID != "0" ]; then
	echo "Error: You must be root to run this script"
	exit 0
fi

if [ -d /etc/xams ]; then
	XAMS_CONF_DIR=/etc/xams
	if [ -r $XAMS_CONF_DIR/xams.conf ]; then
		XAMS_CONF=$XAMS_CONF_DIR/xams.conf
	fi
else
	echo "/etc/xams or /etc/xams/xams.conf do not exist"
	echo "Please check your XAMS installation"
	exit 0
fi

if [ -d /etc/courier ]; then
	COURIER_CONF_DIR=/etc/courier
	if [ -r $COURIER_CONF_DIR/authdaemonrc ]; then
		COURIER_AUTHD_CONF=$COURIER_CONF_DIR/authdaemonrc
	fi
else
	echo "/etc/courier or /etc/courier/authdaemonrc do not exist"
	echo "Please check your Courier daemons installation"
	exit 0
fi

echo "Checking XAMS configuration directory permissions"
ls -la $XAMS_CONF_DIR

echo "Reading $XAMS_CONF"
if grep -q ^USER $XAMS_CONF
then
	NEW_CONF=0
	XAMS_USER=`grep ^USER $XAMS_CONF| sed -e "s_USER = __"`
	XAMS_GROUP=`grep ^GROUP $XAMS_CONF| sed -e "s_GROUP = __"`;
elif grep -q ^UID $XAMS_CONF
then
	NEW_CONF=1
	XAMS_USER=`grep ^UID $XAMS_CONF| sed -e "s_UID = __"`
	XAMS_GROUP=`grep ^GID $XAMS_CONF| sed -e "s_GID = __"`;
else
	echo "$XAMS_CONF format not recognized!"
	exit 0
fi

if [ $NEW_CONF ]
then
	echo "Output the [authdaemon] and [xmu] users or user ID"
	for i in "$XAMS_USER"; do echo -e "$i";done

	echo "Output the [authdaemon] and [xmu] groups or group ID"
	for i in "$XAMS_GROUP"; do echo -e "$i";done
else
	echo "Old $XAMS_CONF format detected!"
	echo "IDs are numerical in $XAMS_CONF, both will be shown"
	echo "Output the [authdaemon] and [xmu] users or user ID"
	for i in "$XAMS_USER"; do
		echo -e "$i: `getent passwd $i|cut -f1 -d:`";
	done

	echo "Output the [authdaemon] and [xmu] groups or group ID"
	for i in "$XAMS_GROUP"; do
		echo -e "$i: `getent passwd $i|cut -f1 -d:`";
	done
fi

XAMS_PID=`grep ^pid_file $XAMS_CONF| sed -e "s/pid_file = //"`
echo "Output the PID file of authdaemon"
echo "$XAMS_PID"

XAMS_MAIL_DIR=`grep ^mail_dir $XAMS_CONF| sed -e "s/mail_dir = //"`
echo "Output the Maildir location of XAMS"
echo "$XAMS_MAIL_DIR"
ls -la $XAMS_MAIL_DIR

#echo "Checking Courier configuration directory permissions"
#ls -la $COURIER_CONF_DIR

XAMS_SOCKET_DIR=`grep ^courier_socket $XAMS_CONF| sed -e "s/courier_socket = //"`
echo "Reading $COURIER_AUTHD_CONF"
COURIER_AUTHD_VAR_DIR=`grep ^authdaemonvar $COURIER_AUTHD_CONF| sed -e "s_authdaemonvar=__"`

if [ "`dirname $XAMS_SOCKET_DIR`" != "$COURIER_AUTHD_VAR_DIR" ]; then
	echo $COURIER_AUTHD_VAR_DIR
	echo "Output the courier_socket directory in $XAMS_CONF"
else
	echo "Socket directories for Courier and XAMS match and are:"
fi
echo $XAMS_SOCKET_DIR
ls -la `dirname $XAMS_SOCKET_DIR`

#echo "Checking Courier permissions"
#ls -laR /var/run/courier/

