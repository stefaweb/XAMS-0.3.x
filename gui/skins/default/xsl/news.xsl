<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
    doctype-public="-//W3C//DTD XHTML 1.1//EN" version="1.0"
    encoding="utf-8" indent="yes"/>
    <xsl:param name="skindir" select="//newsroot/skindir" />

    <xsl:template match="newsroot/skindir"/>

    <xsl:template match="newsroot">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
        <head>
            <title>XAMS News-System</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <link rel="stylesheet" type="text/css" href="{$skindir}/css/xams.css"/>
        </head>
        <body style="padding: 15px 5px 0px 5px;">

        <p><h1>XAMS Online News</h1></p>
        <p><br/></p>

        <p><xsl:value-of select="head"/></p>

        <p><br/></p>

        <xsl:call-template name="news"/>

        </body>
        </html>
    </xsl:template>

    <xsl:template name="news">
        <xsl:for-each select="news">
            <div class="menu1n"><span/></div>
            <div class="menu2n">
                <table width="680" style="padding: 5px;">
                    <colgroup>
                        <col width="20"/>
                        <col width="530"/>
                        <col width="100"/>
                    </colgroup>
                    <tr>
                        <td colspan="2"><strong><xsl:value-of select="@subject"/></strong></td>
                        <td style="text-align: right;"><strong><em><xsl:value-of select="@date"/></em></strong></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding-left: 20px"><xsl:value-of select="."/></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: right;"><em>-- <xsl:value-of select="@author"/></em></td>
                    </tr>
                </table>
            </div>
            <div class="menu3n"><span/></div>
            <p><br/></p>
        </xsl:for-each>
    </xsl:template>

</xsl:stylesheet>
