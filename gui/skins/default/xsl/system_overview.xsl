<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
    doctype-public="-//W3C//DTD XHTML 1.1//EN" version="1.0"
    encoding="utf-8" indent="yes"/>
    <xsl:param name="language-file" select="//system/i18nfile" />
    <xsl:param name="language" select="//system/i18nfile/@language" />
    <xsl:param name="skindir" select="//system/skindir" />

    <xsl:template match="/system">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
        <head>
            <title>XAMS</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <meta http-equiv="Pragma" content="no-cache"/>
            <meta http-equiv="Expires" content="-1"/>
            <link rel="stylesheet" type="text/css" href="{$skindir}/css/xams.css"/>
            <link rel="stylesheet" type="text/css" href="{$skindir}/css/system_overview.css"/>
            <link rel="SHORTCUT ICON" href="favicon.ico"/>
            <script language="javascript" type="text/javascript">
               window.onload = function(){
               if (parent.adjustIFrameSize) parent.adjustIFrameSize(window);
               }
            </script>
        </head>
        <body>

        <div><a id="top"><span/></a></div>
        <br xmlns="" />

	<h1><xsl:call-template name="translate">
	<xsl:with-param name="word">System Overview</xsl:with-param>
	</xsl:call-template></h1>	

        <xsl:if test="count (//site) &gt; 1">
        <h4 style="display:block; width:715px;"><xsl:call-template name="translate">
                <xsl:with-param name="word">Showing</xsl:with-param>
                </xsl:call-template><xsl:text> </xsl:text><xsl:value-of select="count (//site)"/><xsl:text> </xsl:text>
                <xsl:call-template name="translate">
                <xsl:with-param name="word">Sites</xsl:with-param>
                </xsl:call-template></h4>
        <p>
          <table class="hsite" cellspacing="0" id="header">
            <colgroup>
                <col width="356"/>
                <col width="356"/>
            </colgroup>
            <xsl:apply-templates mode="first" select="//site" />
          </table>
        </p>
        </xsl:if>
    <br xmlns="" />
    <br xmlns="" />
    <br xmlns="" />
        <xsl:if test="count(reseller) &gt; 0">
            <xsl:call-template name="reseller"/>
        </xsl:if>
        <xsl:if test="count(reseller) = 0">
            <p><xsl:value-of select="info"/></p>
        </xsl:if>
<!--
        <p>
        <a href="system_overview.php?xmloutput=1" xmlns="">
          <xsl:call-template name="translate">
            <xsl:with-param name="word">XML Output</xsl:with-param>
          </xsl:call-template>
        </a>
        </p>
-->
        </body>
        </html>
    </xsl:template>
    
    <!-- Sites list -->
  <xsl:template match="site[(position() mod 2) = 1]" mode="first">
    <tr xmlns="">
      <xsl:apply-templates select="." />

      <xsl:choose>
        <xsl:when test="following-sibling::site[1]">
          <xsl:apply-templates select="following-sibling::site[1]" />
        </xsl:when>

        <xsl:otherwise>
          <td >&#160;</td>
        </xsl:otherwise>
      </xsl:choose>
    </tr>
  </xsl:template>

  <xsl:template match="site" mode="first" />

  <xsl:template match="site" xmlns="">
    <td class="hsite_cell">
      <xsl:element name="a">
        <xsl:attribute name="href">#s<xsl:value-of
        select="@id" /></xsl:attribute>

        <xsl:value-of select="@name" />
      </xsl:element>
    </td>
  </xsl:template>    
    
    <!-- Resellers -->
    <xsl:template name="reseller">
        <xsl:for-each select="reseller">
            <xsl:variable name="resellerid" select="@id"/>
            <table cellspacing="0" class="reseller">
                <colgroup>
                    <col width="5"/>
                    <col width="670"/>
                    <col width="5"/>
                </colgroup>
                <tr>
                    <td colspan="3" class="resellerheader" style="padding-left:5px;">
                        <xsl:choose>
                            <xsl:when test="$resellerid &gt;= 1"><a href="reseller.php?id={$resellerid}&amp;mode=update" class="resellerheadertext"><xsl:value-of select="@name"/></a></xsl:when>
                            <xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise>
                        </xsl:choose>
                        <xsl:if test="@addressbook = 'true'">
                            <xsl:element name="img">
                                <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/address.png</xsl:attribute>
                                <xsl:attribute name="title"><xsl:call-template name="translate">
                                        <xsl:with-param name="word">Addressbook available</xsl:with-param>
                                    </xsl:call-template></xsl:attribute>
                                <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                            </xsl:element>
                        </xsl:if>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td><br/>
                        <xsl:call-template name="site"/>
                    </td>
                    <td/>
                </tr>
                <tr>
                    <td colspan="3" class="resellerfooter">
                        <xsl:call-template name="translate">
                        <xsl:with-param name="word">Sites</xsl:with-param>
                    </xsl:call-template>:
                            <xsl:value-of select="count(site)"/>/<xsl:call-template name="getvalue"><xsl:with-param name="value" select="@maxsites"/></xsl:call-template> <xsl:if test="@maxsites &gt; 0">(<xsl:value-of select="round (100 div @maxsites * count(site))"/>%)</xsl:if> <strong> &#183; </strong>
                        <xsl:call-template name="translate">
                        <xsl:with-param name="word">Domains</xsl:with-param>
                    </xsl:call-template>: <xsl:value-of select="count(site/domains/domain)"/>/<xsl:call-template name="getvalue"><xsl:with-param name="value" select="@maxdomains"/></xsl:call-template> <xsl:if test="@maxdomains &gt; 0">(<xsl:value-of select="round (100 div @maxdomains * count(site/domains/domain))"/>%)</xsl:if> <strong> &#183; </strong>
                        <xsl:call-template name="translate">
                        <xsl:with-param name="word">Users</xsl:with-param>
                    </xsl:call-template>: <xsl:value-of select="count(site/users/user)"/>/<xsl:call-template name="getvalue"><xsl:with-param name="value" select="sum(site/@maxusers)"/></xsl:call-template> <xsl:if test="site/@maxusers &gt; 0">(<xsl:value-of select="round (100 div sum(site/@maxusers) * count(site/users/user))"/>%)</xsl:if> |
                        <xsl:call-template name="translate">
                        <xsl:with-param name="word">Delegated</xsl:with-param>
                    </xsl:call-template>: <xsl:call-template name="getvalue"><xsl:with-param name="value" select="sum(site/@maxusers)"/></xsl:call-template>/<xsl:call-template name="getvalue"><xsl:with-param name="value" select="@maxusers"/></xsl:call-template> <xsl:if test="@maxusers &gt; 0">(<xsl:value-of select="round (100 div @maxusers * sum(site/@maxusers))"/>%)</xsl:if> <strong> &#183; </strong>
                        <xsl:call-template name="translate">
                        <xsl:with-param name="word">Aliases</xsl:with-param>
                    </xsl:call-template>: <xsl:value-of select="count(site/aliases/alias)"/>/<xsl:call-template name="getvalue"><xsl:with-param name="value" select="sum(site/@maxaliases)"/></xsl:call-template> <xsl:if test="site/@maxaliases &gt; 0">(<xsl:value-of select="round (100 div sum(site/@maxaliases) * count(site/aliases/alias))"/>%)</xsl:if> |
                        <xsl:call-template name="translate">
                        <xsl:with-param name="word">Delegated</xsl:with-param>
                    </xsl:call-template>: <xsl:call-template name="getvalue"><xsl:with-param name="value" select="sum(site/@maxaliases)"/></xsl:call-template>/<xsl:call-template name="getvalue"><xsl:with-param name="value" select="@maxaliases"/></xsl:call-template> <xsl:if test="@maxaliases &gt; 0">(<xsl:value-of select="round (100 div @maxaliases * sum(site/@maxaliases))"/>%)</xsl:if>
                    </td>
                </tr>
            </table>
            <div><br/><br/><br/></div>
        </xsl:for-each>
    </xsl:template>

    <!-- Sites -->
    <xsl:template name="site">
        <xsl:for-each select="site">

            <xsl:variable name="siteid" select="@id"/>

            <xsl:variable name="sitestate">
                <xsl:choose>
                    <xsl:when test="not(@status)">
                        <xsl:text>default</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="@status"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>

            <table cellspacing="0" class="siteshadow">
            <tr>
            <td>

                <table width="670" cellspacing="0">
                    <tr class="{$sitestate}site">
                        <td style="width: 523px;">
                            <a id="s{$siteid}" href="site.php?mode=update&amp;id={$siteid}"><xsl:value-of select="@name"/></a>
                            <xsl:if test="@addressbook = 'true'">
                                <xsl:element name="img">
                                    <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/address.png</xsl:attribute>
                                    <xsl:attribute name="title"><xsl:call-template name="translate">
                                            <xsl:with-param name="word">Addressbook available</xsl:with-param>
                                        </xsl:call-template></xsl:attribute>
                                    <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                                </xsl:element>
                            </xsl:if>
                            <xsl:choose>
                                <xsl:when test="@viruscheckin = 'true'">
                                    <xsl:choose>
                                        <xsl:when test="@viruscheckout = 'true'">
                                            <xsl:element name="img">
                                                <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/viruscheck.png</xsl:attribute>
                                                <xsl:attribute name="title"><xsl:call-template name="translate">
                                                        <xsl:with-param name="word">Incoming and Outgoing Viruscheck</xsl:with-param>
                                                    </xsl:call-template></xsl:attribute>
                                                <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                                            </xsl:element>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:element name="img">
                                                <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/viruscheckin.png</xsl:attribute>
                                                <xsl:attribute name="title"><xsl:call-template name="translate">
                                                        <xsl:with-param name="word">Incoming Viruscheck</xsl:with-param>
                                                    </xsl:call-template></xsl:attribute>
                                                <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                                            </xsl:element>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:when>
                                <xsl:when test="@viruscheckout = 'true'">
                                    <xsl:element name="img">
                                        <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/viruscheckout.png</xsl:attribute>
                                        <xsl:attribute name="title"><xsl:call-template name="translate">
                                                <xsl:with-param name="word">Outgoing Viruscheck</xsl:with-param>
                                            </xsl:call-template></xsl:attribute>
                                        <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                                    </xsl:element>
                                </xsl:when>
                            </xsl:choose>
                            <xsl:choose>
                                <xsl:when test="@spamcheckin = 'true'">
                                    <xsl:choose>
                                        <xsl:when test="@spamcheckout = 'true'">
                                            <xsl:element name="img">
                                                <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/spamcheck.png</xsl:attribute>
                                                <xsl:attribute name="title"><xsl:call-template name="translate">
                                                        <xsl:with-param name="word">Incoming and Outgoing Spamcheck</xsl:with-param>
                                                    </xsl:call-template></xsl:attribute>
                                                <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                                            </xsl:element>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:element name="img">
                                                <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/spamcheckin.png</xsl:attribute>
                                                <xsl:attribute name="title"><xsl:call-template name="translate">
                                                        <xsl:with-param name="word">Incoming Spamcheck</xsl:with-param>
                                                    </xsl:call-template></xsl:attribute>
                                                <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                                            </xsl:element>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:when>
                                <xsl:when test="@spamcheckout = 'true'">
                                    <xsl:element name="img">
                                        <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/spamcheckout.png</xsl:attribute>
                                        <xsl:attribute name="title"><xsl:call-template name="translate">
                                                <xsl:with-param name="word">Outgoing Spamcheck</xsl:with-param>
                                            </xsl:call-template></xsl:attribute>
                                        <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                                    </xsl:element>
                                </xsl:when>
                            </xsl:choose>     
                        </td>
                        <td style="width: 50px; align:center;" class="accountdata"/>
                        <td style="width: 50px; align:center;;" class="accountdata"><img src="{$skindir}/img/utilisateurs.png" width="16" height="16" alt="icone_utilisateur"/><xsl:call-template name="formatsize">
                                <xsl:with-param name="size" select="number (@maxusers)"/>
                                <xsl:with-param name="div">1000</xsl:with-param>
                            </xsl:call-template></td>
                        <td style="width: 45px; align:center;;" class="accountdata"><img src="{$skindir}/img/alias.png" width="16" height="16" alt="icone_alias"/><xsl:call-template name="formatsize">
                                <xsl:with-param name="size" select="number (@maxaliases)"/>
                                <xsl:with-param name="div">1000</xsl:with-param>
                            </xsl:call-template></td>
                        <td style="width: 75px; align:center;;" class="accountdata"><img src="{$skindir}/img/quotat.png" width="16" height="16" alt="icone_quotat"/><xsl:call-template name="formatsize">
                                <xsl:with-param name="size" select="number (@maxquota)"/>
                            </xsl:call-template>/<xsl:call-template name="formatsize">
                                <xsl:with-param name="size" select="number (@maxuserquota)"/>
                            </xsl:call-template>
                        </td>
                        <td style="width: 90px; align:center;" class="accountdata"><img src="{$skindir}/img/protocole.png" width="16" height="16" alt="icone_protocole"/><xsl:value-of select="@addrtype"/></td>
                        <td style="width: 5px; align:center;"/>
                        <td style="width: 15px; align:center;"><a href="#top"><img src="{$skindir}/img/up.png" width="13" height="15" alt=""/></a></td>
                    </tr>
                </table>

                <table width="670" cellspacing="0" style="border-top: 2px solid #216CB3; background-color:#daeaf7">
                    <tr class="{$sitestate}domain">
                        <td style="width: 70px;" class="{$sitestate}site"/>
                        <td style="width: 500px;" class="{$sitestate}domain"><xsl:call-template name="domains"/></td>
                        
                    </tr>
                </table>

                <table width="670" cellspacing="0" style="border-top: 1px solid #216CB3;">
                    <xsl:call-template name="users"><xsl:with-param name="sitestate" select="$sitestate"/></xsl:call-template>
                </table>

                <table width="670" cellspacing="0" style="border-top: 0px solid #216CB3; background-color:#daeaf7">
                    <xsl:call-template name="aliases"><xsl:with-param name="sitestate" select="$sitestate"/></xsl:call-template>
                </table>

            </td>
            </tr>
            </table>
            <p/>
        </xsl:for-each>
    </xsl:template>

    <!-- Domains -->
    <xsl:template name="domains">
        <xsl:variable name="domainsofthissite" select="count(domains/domain)"/>
        <xsl:if test="$domainsofthissite = 0">
            <div style="color: red;"><xsl:call-template name="translate">
            <xsl:with-param name="word">No Domains</xsl:with-param>
            </xsl:call-template></div>
        </xsl:if>
        <xsl:for-each select="domains/domain">
            <xsl:choose>
                <xsl:when test="@zoneid">
                    <xsl:element name="a">
                        <xsl:attribute name="href">dns_zone.php?mode=update&amp;dnsid=<xsl:value-of select="@zoneid"/></xsl:attribute>
                        <xsl:value-of select="."/>
                    </xsl:element>
                    <xsl:element name="img">
                        <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/zone.png</xsl:attribute>
                        <xsl:attribute name="title"><xsl:call-template name="translate">
                        <xsl:with-param name="word">Zone available</xsl:with-param>
                        </xsl:call-template></xsl:attribute>
                        <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                    </xsl:element>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="."/>
                </xsl:otherwise>
            </xsl:choose>
            <xsl:if test="position()!=last()">, </xsl:if>
        </xsl:for-each>
    </xsl:template>

    <!-- Users -->
    <xsl:template name="users">
        <xsl:param name="sitestate"/>
        <xsl:variable name="usersofthissite" select="count(users/user)"/>
        <xsl:variable name="siteid" select="@id"/>

        <xsl:choose>
            <xsl:when test="$usersofthissite = 0">
                <tr class="{$sitestate}user">
                    <td style="width: 70px;" class="{$sitestate}site"/>
                    <td style="width: 80px;" class="{$sitestate}domain"/>
                    <td style="width: 280px;"><a href="user.php?mode=new&amp;siteid={$siteid}"><xsl:call-template name="translate">
                <xsl:with-param name="word">Create User</xsl:with-param>
                </xsl:call-template></a></td>
                    <td style="width: 270px;"/>
                </tr>
            </xsl:when>
            <xsl:otherwise>
                <colgroup>
                    <col width="10"/>
                    <col width="110"/>
                    <col width="200"/>
                    <col width="50"/>
                    <col width="50"/>
                    <col width="45"/>
                    <col width="75"/>
                    <col width="60"/>
                    <col width="40"/>
                </colgroup>
            </xsl:otherwise>
        </xsl:choose>

        <xsl:variable name="adduser"><xsl:call-template name="translate">
            <xsl:with-param name="word">Add new user to this site</xsl:with-param>
            </xsl:call-template>
        </xsl:variable>

        <xsl:for-each select="users/user">
            <xsl:variable name="userid" select="@id"/>

            <xsl:variable name="userstate">
                <xsl:choose>
                    <xsl:when test="not(@status)">
                        <xsl:text>default</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="@status"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>

            <xsl:variable name="accountstate">
                <xsl:choose>
                    <xsl:when test="$sitestate = 'lockedbounce'">
                        <xsl:text>lockedbounce</xsl:text>
                    </xsl:when>
                    <xsl:when test="$sitestate = 'locked'">
                        <xsl:if test="$userstate = 'lockedbounce'">
                            <xsl:text>lockedbounce</xsl:text>
                        </xsl:if>
                        <xsl:if test="not($userstate = 'lockedbounce')">
                            <xsl:text>locked</xsl:text>
                        </xsl:if>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$userstate"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>

            <tr class="{$accountstate}user">
                <xsl:if test="position()=1">
                    <td rowspan="{$usersofthissite+1}" class="{$sitestate}site"/>
                    <td rowspan="{$usersofthissite}" class="{$sitestate}domain" align="center">
                        <xsl:if test="$usersofthissite &gt; 4">
                        <xsl:value-of select="$usersofthissite"/><br/><xsl:call-template name="translate">
                        <xsl:with-param name="word">Users</xsl:with-param>
                    </xsl:call-template>
                    </xsl:if></td>
                </xsl:if>

                <td><a href="user.php?mode=update&amp;id={$userid}"><xsl:value-of select="@name"/></a>
                    <xsl:if test="@addressbook = 'true'">
                        <xsl:element name="img">
                            <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/address.png</xsl:attribute>
                            <xsl:attribute name="title"><xsl:call-template name="translate">
                                    <xsl:with-param name="word">Addressbook available</xsl:with-param>
                                </xsl:call-template></xsl:attribute>
                            <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                        </xsl:element>
                    </xsl:if>
                    <xsl:choose>
                        <xsl:when test="@viruscheckin = 'true'">
                            <xsl:choose>
                                <xsl:when test="@viruscheckout = 'true'">
                                    <xsl:element name="img">
                                        <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/viruscheck.png</xsl:attribute>
                                        <xsl:attribute name="title"><xsl:call-template name="translate">
                                                <xsl:with-param name="word">Incoming and Outgoing Viruscheck</xsl:with-param>
                                            </xsl:call-template></xsl:attribute>
                                        <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                                    </xsl:element>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:element name="img">
                                        <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/viruscheckin.png</xsl:attribute>
                                        <xsl:attribute name="title"><xsl:call-template name="translate">
                                                <xsl:with-param name="word">Incoming Viruscheck</xsl:with-param>
                                            </xsl:call-template></xsl:attribute>
                                        <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                                    </xsl:element>
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:when>
                        <xsl:when test="@viruscheckout = 'true'">
                            <xsl:element name="img">
                                <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/viruscheckout.png</xsl:attribute>
                                <xsl:attribute name="title"><xsl:call-template name="translate">
                                        <xsl:with-param name="word">Outgoing Viruscheck</xsl:with-param>
                                    </xsl:call-template></xsl:attribute>
                                <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                            </xsl:element>
                        </xsl:when>
                    </xsl:choose>
                    <xsl:if test="@autoreply = 'true'">
                        <xsl:element name="img">
                            <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/autoreply.png</xsl:attribute>
                            <xsl:attribute name="title"><xsl:call-template name="translate">
                                    <xsl:with-param name="word">Auto reply enabled</xsl:with-param>
                                </xsl:call-template></xsl:attribute>
                            <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                        </xsl:element>
                    </xsl:if>
                </td>
                <td class="accountdata"/>
                <td class="accountdata">R=<xsl:call-template name="translate">
                                    <xsl:with-param name="word"><xsl:value-of select="@relaytype"/></xsl:with-param>
                                </xsl:call-template></td>
                <td class="accountdata"/>
                <td class="accountdata">Q=<xsl:call-template name="formatsize">
                    <xsl:with-param name="size" select="number (@uquota)" />
                    </xsl:call-template>/<xsl:call-template name="formatsize">
                    <xsl:with-param name="size" select="number (@quota)" />
            </xsl:call-template></td>
                <td class="accountdata">T=<xsl:value-of select="@addrtype"/></td>
                <xsl:choose>
                    <xsl:when test="position()=1"><td> <a href="user.php?mode=new&amp;siteid={$siteid}"><img src="{$skindir}/img/explode.png" width="9" height="9" alt="" title="{$adduser}"/></a></td></xsl:when>
                    <xsl:otherwise><td/></xsl:otherwise>
                </xsl:choose>
            </tr>

        </xsl:for-each>
    </xsl:template>

    <!-- Aliases -->
    <xsl:template name="aliases">
        <xsl:param name="sitestate"/>
        <xsl:variable name="aliasesofthissite" select="count(aliases/alias)"/>
        <xsl:variable name="siteid" select="@id"/>

        <xsl:choose>
            <xsl:when test="$aliasesofthissite = 0">
                <tr class="{$sitestate}alias">
                    <td style="width: 70px;" class="{$sitestate}site"/>
                    <td style="width: 70px;" class="{$sitestate}domain"/>
                    <td style="width: 195px;" class="accountdata"><a href="alias.php?mode=new&amp;siteid={$siteid}"><xsl:call-template name="translate">
                <xsl:with-param name="word">Create Alias</xsl:with-param>
                </xsl:call-template></a></td>
                    <td style="width: 320px;"/>
                </tr>
            </xsl:when>
            <xsl:otherwise>
                <colgroup>
                    <col width="10"/>
                    <col width="140"/>
                    <col width="200"/>
                    <col width="50"/>
                    <col width="310"/>
                    <col width="40"/>
                </colgroup>
            </xsl:otherwise>
        </xsl:choose>

        <xsl:variable name="addalias"><xsl:call-template name="translate">
            <xsl:with-param name="word">Add new alias to this site</xsl:with-param>
            </xsl:call-template>
        </xsl:variable>

        <xsl:variable name="fwbounce"><xsl:call-template name="translate">
            <xsl:with-param name="word">is bounced and forwarded to</xsl:with-param>
            </xsl:call-template>
        </xsl:variable>

        <xsl:variable name="fw"><xsl:call-template name="translate">
            <xsl:with-param name="word">is forwarded to</xsl:with-param>
            </xsl:call-template>
        </xsl:variable>

        <xsl:for-each select="aliases/alias">
            <xsl:variable name="aliasid" select="@id"/>

            <tr class="{$sitestate}alias">
                <xsl:if test="position()=1">
                    <td rowspan="{$aliasesofthissite+1}" class="{$sitestate}site"/>
                    <td rowspan="{$aliasesofthissite}" class="{$sitestate}domain" align="center">
                        <xsl:if test="$aliasesofthissite &gt; 4">
                        <xsl:value-of select="$aliasesofthissite"/><br/><xsl:call-template name="translate">
                        <xsl:with-param name="word">Aliases</xsl:with-param>
                        </xsl:call-template>
                        </xsl:if></td>
                </xsl:if>

                <td class="aliassep">
                    <a href="alias.php?mode=update&amp;id={$aliasid}"><xsl:value-of select="@name"/></a>
                    <xsl:if test="@addressbook = 'true'">
                        <xsl:element name="img">
                            <xsl:attribute name="src"><xsl:value-of select="$skindir"/>/img/address.png</xsl:attribute>
                            <xsl:attribute name="title"><xsl:call-template name="translate">
                                    <xsl:with-param name="word">Addressbook available</xsl:with-param>
                                </xsl:call-template></xsl:attribute>
                            <xsl:attribute name="alt"><xsl:text></xsl:text></xsl:attribute>
                        </xsl:element>
                    </xsl:if>
                </td>
                <td class="aliassep">
                <xsl:choose>
                    <xsl:when test="@bounceforward = 'true'">
                        <img src="{$skindir}/img/leftright.png" width="20" height="15" alt="" title="{$fwbounce}"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <img src="{$skindir}/img/right.png" width="20" height="15" alt="" title="{$fw}"/>
                    </xsl:otherwise>
                </xsl:choose>
                </td>
                <td class="aliassep"><xsl:call-template name="targets"/></td>
                <xsl:choose>
                    <xsl:when test="position()=1"><td class="aliassep"> <a href="alias.php?mode=new&amp;siteid={$siteid}"><img src="{$skindir}/img/explode.png" width="9" height="9" alt="" title="{$addalias}"/></a></td></xsl:when>
                    <xsl:otherwise><td class="aliassep"></td></xsl:otherwise>
                </xsl:choose>
            </tr>
        </xsl:for-each>
    </xsl:template>

    <!-- Alias targets -->
    <xsl:template name="targets">
        <xsl:for-each select="targets/target">
            <xsl:value-of select="."/>
            <xsl:if test="position()!=last()"><br/></xsl:if>
        </xsl:for-each>
    </xsl:template>

    <!-- Format size in units (>=1024) -->
    <xsl:template name="formatsize">
        <xsl:param name="size"/>
        <xsl:param name="div">1024</xsl:param>
        <xsl:param name="unit">2</xsl:param>
        <xsl:variable name="units"> KMGT</xsl:variable>
        <xsl:choose>
    <xsl:when test="(string($size) = 'NaN') or $size = -1"><span style="color: red;">U</span></xsl:when>
            <xsl:when test="$size &gt;= $div">
                <xsl:call-template name="formatsize">
                    <xsl:with-param name="size" select="$size div $div"/>
                    <xsl:with-param name="div" select="$div"/>
                    <xsl:with-param name="unit" select="$unit + 1"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="round ($size)"/>
                <xsl:choose>
                    <xsl:when test="not ($div = 1000)">
                        <xsl:value-of select="substring ($units, $unit, 1)"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="substring ($units, $unit - 1, 1)"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
       </xsl:choose>
    </xsl:template>

<!-- returns infinity if needed &#8734; -->
<xsl:template name="getvalue">
    <xsl:param name="value"/>
    <xsl:choose>
        <xsl:when test="(string($value) = 'NaN') or ($value &lt; 0)"><span style="color: #AA0000;"><xsl:text> U</xsl:text></span></xsl:when>
        <xsl:otherwise><xsl:value-of select="$value"/></xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- i18n translate function -->
    <xsl:key name="translations"
        match="msgs/msg"
        use="@id"/>
    <xsl:template name="translate">
        <xsl:param name="word"/>
        <xsl:for-each select="document($language-file)">
            <xsl:choose><!--
                --><xsl:when test="string-length (key('translations', $word)) &gt; 0"><!--
                    --><xsl:value-of select="key('translations', $word)"/><!--
                --></xsl:when>
                <xsl:otherwise><!--
                    --><xsl:value-of select="$word"/><!--
                --></xsl:otherwise><!--
            --></xsl:choose>
        </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>
