<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml" doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
    doctype-public="-//W3C//DTD XHTML 1.1//EN" version="1.0"
    encoding="utf-8" indent="yes"/>
    <xsl:param name="language-file" select="//menuroot/i18nfile" />
    <xsl:param name="language" select="//menuroot/i18nfile/@language" />
    <xsl:param name="skindir" select="//menuroot/skindir" />
    <xsl:variable name="apos">
        <xsl:text>'</xsl:text>
    </xsl:variable>

    <xsl:template match="menuroot/i18nfile"/>
    <xsl:template match="menuroot/skindir"/>

    <xsl:template match="menuroot/menu">
        <!--html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
        <head>
            <title>XAMS Menu</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/e-->
          
            <link rel="stylesheet" type="text/css" href="{$skindir}/css/menu.css"/>
            <script type="text/javascript">
            <xsl:text disable-output-escaping="yes">
            &lt;!--
            </xsl:text>
                <xsl:if test="usertype = 'administrator'">
                    pics = new Array("account","lens","addressbook","preferences","log_overview",
                                     "template","dns","site","user","alias",
                                     "system_overview","database_analyse","info");
                </xsl:if>
                <xsl:if test="usertype = 'reseller'">
                    pics = new Array("preferences","account","lens","template",
                                     "site","user","alias","system_overview");
                </xsl:if>
                <xsl:if test="usertype = 'customer'">
                    pics = new Array("preferences","lens","template","user","alias",
                                     "system_overview");
                </xsl:if>
                <xsl:if test="usertype = 'user'">
                    pics = new Array("preferences");
                </xsl:if>
                buttons = new Array();
                skindir = "<xsl:value-of select="$skindir"/>";
                
                language = "<xsl:value-of select="$language"/>";
                <xsl:text disable-output-escaping="yes">
                    for (i=1; i&lt;=2; i++) {
                        buttons[i] = new Image();
                        buttons[i].src = skindir + "/img/buttons/" + "/red_logout" + (i==1 ? "_h" : "_p") + ".png";
                    }
                    pics_array = new Array();
                    for (i=0; i&lt;pics.length; i++) {
                        pics_array[pics[i]] = new Image();
                        pics_array[pics[i]].src = skindir + "/img/menu/" + pics[i] + "_h.png";
                    }
                // --&gt;
                </xsl:text>
                function mover(t,pid,status) { document.getElementById(pid).src = pics_array[pid].src; t.style.cursor = "pointer"; window.status = status; }
                function mout(pid) { document.getElementById(pid).src = skindir + "/img/menu/" + pid + ".png"; window.status = ''; }
                function c() { document.getElementById('logout').src = buttons[1].src; }
                function d() { document.getElementById('logout').src = buttons[2].src; }
                function e() { document.getElementById('logout').src =  skindir + "/img/buttons/" + "/red_logout.png"; }
               
               function return_ajax(ajax){
                   //alert(ajax.responseText);
                   document.getElementById('col_right').innerHTML = ajax.responseText;
               }
               
               function linkto(page,i) {                    
                    window.parent.document.getElementById("framecontenu").src=page;
                    
                    }
            <xsl:text disable-output-escaping="yes">
            </xsl:text>
            </script>
        <!--/head>
        <body-->

            <table id="menutable" cellspacing="0" cellpadding="0">
                <tr id="version">
                    <td>
                        <img src="{$skindir}/img/logo.png" width="88" height="59" alt="XAMS" title="eXtended Account Management System" onclick="linkto('startup.php',1)" onmouseover="this.style.cursor = 'pointer';" />
                        <br/><xsl:value-of select="//@xams-release"/>
                    </td>
                </tr>
                <tr class="spacer"><td><br/></td></tr>

                <tr>
                    <td id="userinfo">
                        <xsl:value-of select="username"/>
                    </td>
                </tr>

                <tr class="spacer"><td><br/></td></tr>

                <xsl:call-template name="groups"/>
                
                <tr class="spacer"><td><br/></td></tr>
                <tr>
                    <td class="menu-logout">
                        <xsl:variable name="button_name">
                            <xsl:call-template name="translate">
                                <xsl:with-param name="word">Logout</xsl:with-param>
                            </xsl:call-template>
                        </xsl:variable>
                        <img src="{$skindir}/img/buttons/red_logout.png" id="logout" alt="{$button_name}" title="{$button_name}" width="108" height="48" onmouseover="c();" onmouseout="e();" onmousedown="d();" onmouseup="e();" onclick="linkto('logout.php',1)" style="cursor: pointer;" />
                    </td>
                </tr>
                
                <tr><td><br/></td></tr>

            </table>
        <!--/body>
        </html-->
    </xsl:template>

    <xsl:template name="groups">
        <xsl:for-each select="groups">
            <xsl:if test="@usertype = //usertype">
                <xsl:for-each select="group">
                                <tr class="menuheader">
                                    <td><xsl:call-template name="translate"><xsl:with-param name="word"><xsl:value-of select="@name"/></xsl:with-param></xsl:call-template></td>
                                </tr>
                            <xsl:for-each select="item">
                                <xsl:variable name="pic" select="@pic"/>
                                <xsl:variable name="name" select="@name"/>
                                <xsl:variable name="expl">
                                    <xsl:call-template name="translate"><xsl:with-param name="word"><xsl:value-of select="@expl"/></xsl:with-param></xsl:call-template>
                                </xsl:variable>
                                <xsl:variable name="link" select="@link"/>
                                <xsl:variable name="counter" select="position()"/>
                                    <tr class="menuitem">
                                        <td>
                                            <xsl:element name="img"> 
                                                <xsl:attribute name="id"><xsl:value-of select="$pic"/></xsl:attribute>
                                                <xsl:attribute name="src"><xsl:value-of select="concat($skindir,'/img/menu/',$pic,'.png')"/></xsl:attribute>
                                                <xsl:attribute name="width">22</xsl:attribute>
                                                <xsl:attribute name="height">22</xsl:attribute>
                                                <xsl:attribute name="onmouseover"><xsl:value-of select="concat('mover(this,',$apos,$pic,$apos,',',$apos,$expl,$apos,');')"/></xsl:attribute>
                                                <xsl:attribute name="onmouseout"><xsl:value-of select="concat('mout(',$apos,$pic,$apos,');')"/></xsl:attribute>
                                                <xsl:attribute name="onclick"><xsl:value-of select="concat('linkto(',$apos,$link,$apos,');')"/></xsl:attribute>
                                                <xsl:attribute name="title"><xsl:call-template name="translate"><xsl:with-param name="word"><xsl:value-of select="@name"/></xsl:with-param></xsl:call-template></xsl:attribute>
                                            </xsl:element>
                                            <a onmouseover="mover(this,'{$pic}','{$expl}');" onmouseout="mout('{$pic}');" onclick="linkto('{$link}')"><xsl:call-template name="translate"><xsl:with-param name="word"><xsl:value-of select="@name"/></xsl:with-param></xsl:call-template></a>
                                        </td>
                                    </tr>
                            </xsl:for-each>
                </xsl:for-each>
            </xsl:if>
        </xsl:for-each>
    </xsl:template>

<!-- i18n translate function -->
    <xsl:key name="translations"
        match="msgs/msg"
        use="@id"/>
    <xsl:template name="translate">
        <xsl:param name="word"/>
    <xsl:for-each select="document(concat ('file://', $language-file))">
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
