<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi">

	<xsl:template match="udata[@module = 'forum'][@method = 'message_post']">
		<xsl:call-template name="feedback_form"/>
	</xsl:template>

	<xsl:template match="udata[@module = 'forum'][@method = 'message_post'][action]">
		<xsl:call-template name="feedback_form"/>
	</xsl:template>

	<xsl:template name="feedback_form">
		<xsl:param name="formType" select="$formType"/>

		<h2 id="review_header">Ваш отзыв:</h2>

		<div class="form_wrap">
			<form  enctype="multipart/form-data"  id="add_review" method="post" action="/forum/message_post_do/54/" >
				<input required="required" type="text" name="title" class="textinputs" placeholder="Ваше имя">
					<xsl:attribute name="pattern">^[а-яёА-ЯЁa-zA-Z\s]{2,20}$</xsl:attribute>
				</input>

				<textarea required="required" name="body" placeholder="Ваш отзыв"></textarea>

				<input required="required" type="text" name="useremail" class="textinputs" placeholder="E-mail">
					<xsl:attribute name="pattern">^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$</xsl:attribute>
				</input>

				<div class="row">
					<xsl:apply-templates select="document('udata://system/captcha/')/udata" >
						<xsl:with-param name="formType" select="'review'"/>
					</xsl:apply-templates>
				</div>
				<div class="btn_wrap mob_center">
					<input id="reset" type="reset" class="btn btn-lg btn-default" value="Очистить" />
					<input type="submit" class="ajax-send btn btn-lg btn-primary" value="Отправить"/>
				</div>

				<div class="alert alert-success">
					<h3>Спасибо за ваш отзыв!</h3>
				</div>
				<div class="alert alert-danger">
					<h3>Введен неверный код с картинки!</h3>
				</div>
			</form>
		</div>
	</xsl:template>

</xsl:stylesheet>