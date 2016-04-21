<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
    doctype-public="-//W3C//DTD XHTML 1.1//EN" version="1.0"
    encoding="utf-8" indent="yes"/>
    <xsl:param name="skindir" select="//help/skindir" />
    <xsl:param name="usertype" select="//help/usertype" />

    <xsl:template match="/help">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
        <head>
        <title>XAMS Online help: <xsl:value-of select="@title"/></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link rel="stylesheet" href="{$skindir}/css/help.css" type="text/css"/>
        </head>
        <body>
        <table width="900" style="margin:0 auto; padding-left:10px; padding-right:10px; border:1px solid white" >
        <tr>
        <td>
        <p style="text-align: center; background-color:#FFF;"><a href="#" onclick="window.open('http://www.xams.org');"><img src="{$skindir}/img/logo.png" width="88" height="59" title="eXtended Account Management System" alt="XAMS" /></a></p>
        <p/>
        <table id="header" cellspacing="0" style="text-align: center;">
            <tr>
                <td id="headerleft"></td>
                <td id="headermid"><xsl:value-of select="@title"/></td>
                <td id="headerright"></td>
            </tr>
        </table>
        <p/>
        <xsl:if test="information&gt;''">
        <p class="information">
            <xsl:value-of select="information"/>
        </p>
        </xsl:if>

        <xsl:apply-templates select="section"/>

        </td>
        </tr>
        </table>
        <p/>
        
        <p style=" text-align:center;">{{XAMS Online help}}: <xsl:value-of select="@title"/> - {{Last modification}}: <xsl:value-of select="@date"/></p>
        </body>
        </html>
    </xsl:template>

    <xsl:template match="section">
        <br/><h2><xsl:value-of select="head"/></h2>
        <p class="section">
            <xsl:choose>
            <xsl:when test="number($usertype) != NaN">
                <xsl:choose>
                <xsl:when test="$usertype = @usertype">
                    <xsl:value-of select="description"/>
                </xsl:when>
                <xsl:otherwise>
                <xsl:if test="$usertype &gt;= @minusertype">
                    <xsl:value-of select="description"/>
                </xsl:if>
                </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="description"/>
            </xsl:otherwise>
            </xsl:choose>
        </p>
    </xsl:template>

</xsl:stylesheet>
