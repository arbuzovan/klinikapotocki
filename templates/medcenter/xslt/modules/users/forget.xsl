<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM	"ulang://i18n/constants.dtd:file">

<xsl:stylesheet	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xlink="http://www.w3.org/TR/xlink">

	<xsl:template match="result[@method = 'forget']">
		<h1>Восстановление пароля</h1>
		<form method="post" action="/users/forget_do/" id="forget">
			<script>
				<![CDATA[
				jQuery(document).ready(function(){
					jQuery('#forget input:radio').click(function() {
						jQuery('#forget input:text').attr('name', jQuery(this).attr('id'));
					});
				});

				]]>
			</script>

			<div class="control-group">
					<lable>
					<input type="radio" id="forget_login" name="choose_forget" checked="checked" />
					<span class="ml10"><xsl:text>&login;:</xsl:text></span>
			         </lable>
				</div>

			<div class="control-group">
				<lable>
					<input type="radio" id="forget_email" name="choose_forget" />
					<span class="ml10"><xsl:text>&e-mail;:</xsl:text></span>
				</lable>
			</div>
				<div class="control-group">	
             <ul class="forget_password">
				<li>	
					<input class="small_input" type="text" name="forget_login" />
				</li>

				<li>	
					<input type="submit" class="btn_login" value="ВЫСЛАТЬ ПАРОЛЬ" />
				</li>
			</ul>
			
				</div>
		</form>
	</xsl:template>

	<xsl:template match="result[@method = 'forget_do']">
		<h1>Восстановление пароля</h1>
		<p>
			<xsl:text>&registration-activation-note;</xsl:text>
		</p>
	</xsl:template>
</xsl:stylesheet>