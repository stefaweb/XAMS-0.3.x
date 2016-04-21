#!/usr/bin/perl -w

# You can use this command to generate the needed xams-catalog
# by this program and finish the job by hand.
# find . -name "*" -printf '%h/%f,%y,%m\n' | cut -c3- | sed -e '/.svn/d' > xams-catalog

use strict;
use File::Copy;
use File::Path;

die("Too few parameters given.") if ($#ARGV < 1);

my $catalog = $ARGV[0];
my $package = $ARGV[1];

my $reldir = '../xams-releases/';
my $relpck = $reldir . $package;

rmtree($relpck) if (-d $relpck);
die("Can't find xams release catalog '$catalog'!\n") unless (-e $catalog);

mkdir($relpck, 0750);
open(FH, '<', $catalog) or
    die("Can't find xams release catalog '$catalog'!\n");
print "Reading xams-release-catalog '$catalog'...\n";
while (<FH>)
{
    next if ($_ =~ /^#/);
    my ($name, $type, $mode) = split(/,/, $_);
    if ($type eq 'd')
    {
        mkdir($relpck . '/' . $name, oct($mode)) or
            die("Can't create directory '$name'!\n");
    }
    elsif ($type eq 'f')
    {
        copy($name, $relpck . '/' . $name) or
            die("Can't copy file '$name'!\n");
        chmod oct($mode), $relpck . '/' . $name;
    }
    else
    {
        die("Unknown type - '$type'");
    }
}
close(FH);

chdir($reldir);
print "Generating Archive...\n";
system("tar cf $package.tar $package");
system("gzip -9c $package.tar > $package.tar.gz");
system("bzip2 -f9 $package.tar");

print "Create checksums...\n";
system("md5sum $package.tar.gz > $package.tar.gz.md5");
system("md5sum $package.tar.bz2 > $package.tar.bz2.md5");

print "Create GPG signatures...\n";
system("gpg -bsau arthur\@aweb.fr $package.tar.gz");
system("gpg -bsau arthur\@aweb.fr $package.tar.bz2");
