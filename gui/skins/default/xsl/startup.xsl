<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
    doctype-public="-//W3C//DTD XHTML 1.1//EN" version="1.0"
    encoding="utf-8" indent="yes"/>
    <xsl:param name="skindir" select="//help/skindir" />
    <xsl:param name="usertype" select="//help/usertype" />

    <xsl:template match="/help">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
        <title>XAMS - eXtended Account Management System</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link rel="stylesheet" href="{$skindir}/css/xams.css" type="text/css"/>
		<script language="javascript" type="text/javascript">
                   window.onload = function(){
                   if (parent.adjustIFrameSize) parent.adjustIFrameSize(window);
                   }
		</script>
        </head>
        <body style="padding: 15px 5px 0px 5px; border:4px solid red;">
        
        <h1><xsl:value-of select="@title"/></h1> 
        <p><br/></p>

        <div class="menu1n"><br/></div>
        <div class="menu2n">
		<table width="660">
		<tr><td style="padding-right: 25px;">
			
        <xsl:if test="information&gt;''">
        <span class="information">
            <p><xsl:value-of select="information"/></p>
        </span>
        </xsl:if>

        <xsl:apply-templates select="section"/>
		</td></tr>
		</table>
        </div>
        <div class="menu3n"></div>
        
        </body>
        </html>
    </xsl:template>

    <xsl:template match="section">
    	<xsl:if test="$usertype &gt;= @minusertype">
        <span class="section">
            <h3><xsl:value-of select="head"/></h3>
            <p><xsl:value-of select="description"/></p>
        </span>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>
