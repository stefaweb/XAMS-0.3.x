#!/bin/sh
echo '# XAMS Release File-Catalog' > xams-release-catalog
echo '# $Id$' >> xams-release-catalog
find -printf '%P,%y,%m\n' | grep -Ev '(\.svn|^,|^TODO|^RELEASE-HOWTO|^xams-release-catalog|^release\.pl|^autogen\.conf,|^gen-xams-release-catalog\.sh,)' >> xams-release-catalog

