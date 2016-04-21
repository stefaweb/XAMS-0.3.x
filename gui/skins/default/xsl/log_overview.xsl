<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
    doctype-public="-//W3C//DTD XHTML 1.1//EN" version="1.0"
    encoding="utf-8" indent="yes"/>
    <xsl:param name="language-file" select="//eventlog/i18nfile" />
    <xsl:param name="language" select="//eventlog/i18nfile/@language" />
    <xsl:param name="skindir" select="//eventlog/skindir" />

<xsl:template match="/eventlog">
	<xsl:apply-templates select="logentry"/>
</xsl:template>

<xsl:template match="logentry">
    <xsl:variable name="usercolor">
    	<xsl:choose>
				<xsl:when test="@usertype = 'adminid'">
					none
				</xsl:when>
				<xsl:when test="@usertype = 'resellerid'">
					#FADCA7
				</xsl:when>
				<xsl:when test="@usertype = 'customerid'">
					#DADCA7
				</xsl:when>
				<xsl:when test="@usertype = 'userid'">
					#C1E1D7
				</xsl:when>
				<xsl:otherwise>none</xsl:otherwise>
    	</xsl:choose>
    </xsl:variable>
		<tr style="height: 15px; background-color: {$usercolor};"> 
        <xsl:call-template name="msgtype_display">
          <xsl:with-param name="msgtype"><xsl:value-of select="@event"/></xsl:with-param>
        </xsl:call-template>
        <td><xsl:value-of select="@date"/></td>
        <td><xsl:value-of select="@time"/></td>
        <td><xsl:call-template name="translate"><xsl:with-param name="word"><xsl:value-of select="@user"/></xsl:with-param></xsl:call-template></td>
        <td><xsl:call-template name="translate"><xsl:with-param name="word"><xsl:value-of select="@resource"/></xsl:with-param></xsl:call-template></td>
        <td><xsl:call-template name="translate"><xsl:with-param name="word"><xsl:value-of select="@event"/></xsl:with-param></xsl:call-template></td>
        <td><xsl:value-of select="."/></td>
    </tr>
</xsl:template>

<!-- msgtype_display -->
	<xsl:template name="msgtype_display">
		<xsl:param name="msgtype"/>
		<xsl:choose>
			<xsl:when test="$msgtype = 'failed'">
				<td align="center" style="color: red; font-weight: bold;">!</td>
			</xsl:when>
			<xsl:when test="$msgtype = 'Insertion'">
				<td align="center" style="color: blue;">+</td>
			</xsl:when>
			<xsl:when test="$msgtype = 'Update'">
				<td align="center" style="color: green;">x</td>
			</xsl:when>
			<xsl:when test="$msgtype = 'Selection'">
				<td align="center" style="color: #216CB3;">#</td>
			</xsl:when>
			<xsl:when test="$msgtype = 'Deletion'">
				<td align="center" style="color: red;">-</td>
			</xsl:when>
			<xsl:when test="$msgtype = 'Login'">
				<td align="center" style="color: cadetblue;">&gt;</td>
			</xsl:when>
			<xsl:otherwise><td align="center" style="color: cadetblue;"> </td></xsl:otherwise>
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
