<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "ulang://i18n/constants.dtd:file">
	<!-- *** MEDCENTER VERSION 1.7.5 *** -->
	<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:umi="http://www.umi-cms.ru/TR/umi" xmlns:xlink="http://www.w3.org/TR/xlink">
		<xsl:output encoding="utf-8" method="html" indent="yes" />
		<xsl:include href="../__common.xsl"/>

		<xsl:template match="/">
			<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
			<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<meta http-equiv="X-UA-Compatible" content="IE=edge" />
					<title>
						<xsl:choose>
							<xsl:when test="document(concat('upage://',$pageId))//udata//property[@name='title']">
								<xsl:value-of select="document(concat('upage://', $pageId, '.title'))/udata//value" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="result/@title" />
							</xsl:otherwise>
						</xsl:choose>
					</title>
					<meta name="description" content="{result/meta/description}" />
					<meta name="keywords" content="{result/meta/keywords}" />
					<meta name="robots" content="index, follow" />
					<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

					<xsl:choose>
						<xsl:when test="$conf//property[@name='favicon']/value">
							<link href="{$conf//property[@name='favicon']/value}" rel="shortcut icon" type="image/ico" />
						</xsl:when>
						<xsl:otherwise>
							<link href="{$template-resources}images/favicon.ico" rel="shortcut icon" type="image/ico" />
						</xsl:otherwise>
					</xsl:choose>

			        <!-- <link rel="stylesheet" href="{$template-resources}css/bootstrap.css"/>
			        <link rel="stylesheet" href="{$template-resources}css/chosen.css"/>
			        <link rel="stylesheet" href="{$template-resources}css/jquery-ui.css" />
			        <link rel="stylesheet" href="{$template-resources}css/style.css" />
					<link rel="stylesheet" href="{$template-resources}css/mediaqueries.css" /> -->

			        <!-- MINIFY -->
			        <link type="text/css" rel="stylesheet" href="/min/b=templates/medcenter/css&amp;f=bootstrap.css,chosen.css,jquery-ui.css,style.css,mediaqueries.css" />
			        <!-- END MINIFY -->

					<!-- <base href="http://{concat(result/@domain, result/@request-uri)}"/> -->
					<xsl:text disable-output-escaping="yes">
						&lt;!--[if lt IE 9]&gt;
						&lt;script src="/templates/medcenter/js/html5shiv.js"&gt;&lt;/script&gt;
						&lt;![endif]--&gt;
					</xsl:text>

					<!-- Быстрое редактирование -->
				    <xsl:value-of select="document('udata://system/includeQuickEditJs')/udata" disable-output-escaping="yes"/>
				    <!-- <xsl:value-of select="document('udata://system/includeEditInPlaceJs')/udata" disable-output-escaping="yes"/> -->

			        <!-- MINIFY -->
			        <script type="text/javascript" src="/min/b=templates/medcenter/js&amp;f=bootstrap.js,jquery.form.js,jquery.maskedinput.js,jquery.scrollTo-1.4.3.1.js,chosen.jquery.min.js,jquery.tablesorter.min.js,jquery.bxslider.min.js,scripts.js"></script>
			        <!-- END MINIFY -->
				</head>

				<body>
					<xsl:value-of select="$conf//property[@name='manager_google']/value" disable-output-escaping="yes"/>
					<header itemscope="itemscope" itemtype="http://schema.org/LocalBusiness">
				        <div class="top-panel" style="background:{$conf//property[@name='top-panel-color']/value}">
				            <div class="container">
				                <div class="row">
				                    <div class="col-md-5 xs-c sm-c">
				                        <div class="phone">
											<xsl:if test="$conf//property[@name='telephone']/value">
			                                    <a class="phone-top" itemprop="telephone" umi:field-name="telephone" umi:object-id="{$conf//object/@id}" target="_blank" value="{$conf//property[@name='telephone']/value}" href="tel:{$conf//property[@name='telephone']/value}">
													<xsl:value-of select="$conf//property[@name='telephone']/value" />
			                                    </a>
											</xsl:if>
											<xsl:if test="$conf//property[@name='telephone2']/value">
					                            <span>  |  </span>
			                                    <a class="phone-top" itemprop="telephone" umi:field-name="telephone2" umi:object-id="{$conf//object/@id}" target="_blank" value="{$conf//property[@name='telephone2']/value}" href="tel:{$conf//property[@name='telephone2']/value}">
													<xsl:value-of select="$conf//property[@name='telephone2']/value" />
			                                    </a>
											</xsl:if>
				                        </div>
				                    </div>
				                    <div class="col-md-4">
				                    	<xsl:apply-templates select="document('udata://search/insert_form')/udata"/>
				                    </div>
				                    <div class="col-md-3 xs-c sm-c text-right">
				                        <a href="#order" class="btn scroll" umi:field-name="btn_value" umi:object-id="{$conf//object/@id}">
				                        	<xsl:value-of select="$conf//property[@name='btn_value']/value" />
				                        </a>
				                    </div>
				                </div>
				            </div>
				        </div>

			            <div class="header">
			                <div class="container">
			                    <div class="row">
			                        <div class="col-md-2 col-lg-3 text-center">
			                            <a href="/" class="logo text-center">
			                                <span class="logo-img" umi:field-name="logo" umi:object-id="{$conf//object/@id}">
												<xsl:call-template name="makeThumbnail">
													<xsl:with-param name="object_id" select="$conf//object/@id" />
													<xsl:with-param name="field_name">logo</xsl:with-param>
													<xsl:with-param name="width">150</xsl:with-param>
													<xsl:with-param name="height">60</xsl:with-param>
													<xsl:with-param name="alt" select="$conf//property[@name='company_name']/value" />
												</xsl:call-template>
			                                </span>

			                                <span itemprop="name" class="name" umi:field-name="company_name" umi:object-id="{$conf//object/@id}">
												<xsl:value-of select="$conf//property[@name='company_name']/value"/>
			                                </span>

			                                <span umi:field-name="slogan" umi:object-id="{$conf//object/@id}">
												<xsl:value-of select="$conf//property[@name='slogan']/value" />
			                                </span>
			                            </a>
			                        </div>

			                        <div class="col-md-10 col-lg-9">
		                                <nav class="navbar">
		                                    <div class="navbar-header text-center">
		                                    	<a class="hidden visible-xs-inline-block btn-menu finger" data-toggle="collapse" data-target="#navbar">Меню:</a>

		                                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
		                                            <span class="sr-only">Toggle navigation</span>
		                                            <span class="icon-bar"></span>
		                                            <span class="icon-bar"></span>
		                                            <span class="icon-bar"></span>
		                                        </button>
		                                    </div>
		                                    <div id="navbar" class="collapse navbar-collapse">
	                                        	<xsl:apply-templates select="document('udata://content/menu/0/1/0/')/udata" mode="menu_top"/>
		                                    </div>
		                                </nav>

			                            <div class="contacts xs-c">
			                                <span class="blue">Наш адрес: </span>
			                                <address itemprop="address" itemscope="itemscope" itemtype="http://schema.org/PostalAddress">
			                                	<xsl:if test="$conf//property[@name='postalcode']/value">
													<span itemprop="postalCode" umi:field-name="postalcode" umi:object-id="{$conf//object/@id}">
														<xsl:value-of select="$conf//property[@name='postalcode']/value" />
													</span>,&nbsp;
												</xsl:if>
												<span itemprop="addressLocality" umi:field-name="addresslocality" umi:object-id="{$conf//object/@id}">
													<xsl:value-of select="$conf//property[@name='addresslocality']/value" />
												</span>,
												<span itemprop="streetAddress" umi:field-name="streetaddress" umi:object-id="{$conf//object/@id}" >
													<xsl:value-of select="$conf//property[@name='streetaddress']/value" />
												</span>
			                                </address>

											<xsl:if test="$conf//property[@name='openinghours_start']/value or $conf//property[@name='openinghours_end']/value">
				                                <span class="divider">•</span>
				                                <span class="blue">График работы: </span>
				                                <time itemprop="openingHours" datetime="{$conf//property[@name='week_day']/value} {$conf//property[@name='openinghours_start']/value}−{$conf//property[@name='openinghours_end']/value}">
				                                    <xsl:text>с </xsl:text>
				                                    <span umi:field-name="openinghours_start" umi:object-id="{$conf//object/@id}" >
														<xsl:value-of select="$conf//property[@name='openinghours_start']/value" />
				                                    </span>
				                                    <xsl:text> до </xsl:text>
				                                    <span umi:field-name="openinghours_end" umi:object-id="{$conf//object/@id}" >
														<xsl:value-of select="$conf//property[@name='openinghours_end']/value" />
				                                    </span>
				                                </time>
											</xsl:if>
			                            </div>
			                        </div>
			                    </div>
			                </div>
			            </div>
			        </header>

					<xsl:if test="result/page/@is-default = '1'">
				        <div id="banner" class="carousel slide" data-ride="carousel" data-interval="15000" data-pause="hover">
					    	<xsl:if test="$conf//property[@name='banner_pause']/value">
						    	<xsl:attribute name="data-interval">
						    		<xsl:value-of select="$conf//property[@name='banner_pause']/value"/>
						    	</xsl:attribute>
							</xsl:if>

							<xsl:apply-templates select="document(concat('usel://slider/', $sharesId))//udata" mode="slider"/>
				            <a class="left carousel-control" href="#banner" role="button" data-slide="prev">
				                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
				                <span class="sr-only">Previous</span>
				            </a>
				            <a class="right carousel-control" href="#banner" role="button" data-slide="next">
				                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
				                <span class="sr-only">Next</span>
				            </a>
				        </div>
					</xsl:if>
			        <div class="container" role="main">
			            <div class="row">
			                <div class="col-md-3">
			                    <div class="mb30">
			                        <div class="block-caption" style="background:{$conf//property[@name='block-caption-color']/value}">
			                            <a href="{document(concat('udata://content/get_page_url/', $activitiesId,'/'))/udata}" class="activities" umi:element-id="{$activitiesId}" umi:field-name="name">
											<xsl:value-of select="document(concat('upage://', $activitiesId))//udata//page/name"/>
			                            </a>
			                        </div>
		                            <xsl:apply-templates select="document(concat('udata://content/menu/0/1/', $activitiesId))//udata" mode="aside"/>
			                    </div>

			                    <!-- SHARES -->
								<xsl:if test="not($pageId = $main)">
				                    <div class="mb30">
				                        <div class="block-caption" style="background:{$conf//property[@name='block-caption-color']/value}">
				                            <a href="{document(concat('udata://content/get_page_url/', $sharesId,'/'))/udata}" class="shares" umi:element-id="{$sharesId}" umi:field-name="h1">
												<xsl:value-of select="document(concat('upage://', $sharesId, '.h1'))//udata//value"/>
				                            </a>
				                        </div>
										<xsl:apply-templates select="document(concat('usel://getIndexAnons/', $sharesId))//udata" mode="index_anons"/>
				                    </div>
								</xsl:if>

			                    <!-- ARTICLES -->
			                    <div class="mb30">
			                        <div class="block-caption" style="background:{$conf//property[@name='block-caption-color']/value}">
			                            <a href="{document(concat('udata://content/get_page_url/', $articlesId,'/'))/udata}" class="articles" umi:element-id="{$articlesId}" umi:field-name="h1">
											<xsl:value-of select="document(concat('upage://', $articlesId, '.h1'))//udata//value"/>
			                            </a>
			                        </div>
									<xsl:choose>
										<xsl:when test="$pageId = $main">
											<xsl:apply-templates select="document(concat('usel://getIndexAnons/', $articlesId))//udata" mode="index_anons"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:apply-templates select="document(concat('usel://getIndexAnons/', $articlesId, '/1'))//udata" mode="index_anons"/>
			                    		</xsl:otherwise>
			                    	</xsl:choose>
			                    </div>
			                </div>
			                <div class="col-md-9">
								<!-- ACTIVITIES -->
			                    <xsl:if test="result/page/@is-default = '1'">
			                    	<xsl:apply-templates select="document(concat('usel://getCategoryRand/', $activitiesId, '/3/'))/udata" mode="activities"/>
								</xsl:if>

			                    <xsl:apply-templates select="document('udata://core/navibar/')/udata"/>
								<xsl:apply-templates select="document('udata://system/listErrorMessages/')/udata" />

			                    <xsl:apply-templates select="/result"/>

								<!-- FILES -->
								<xsl:if test="document(concat('udata://filemanager/list_files/', $pageId, '/0/'))/udata/items">
								    <xsl:apply-templates select="document(concat('udata://filemanager/list_files/', $pageId, '/0/'))/udata"  />
								</xsl:if>
								<!-- END FILES -->

								<xsl:if test="result/page/@is-default = '1'">
					                <!-- DOCTORS -->
				                    <div class="h2">
				                        <a href="{document(concat('udata://content/get_page_url/', $doctorsId,'/'))/udata}" umi:element-id="{$doctorsId}" umi:field-name="h1">
											<xsl:value-of select="document(concat('upage://', $doctorsId, '.h1'))//udata//value"/>
											<!-- <xsl:value-of select="$isMob"/> -->
				                        </a>
				                    </div>

				                    <div class="slider">
				                        <ul id="doctors">
				                        	<xsl:for-each select="document(concat('usel://getDoctorsRand/', $doctorsId))//udata/page">
					                            <li>
					                                <a rel="nofollow" href="{@link}" class="portrait block">
					                                    <img alt="{.//property[@name='h1']/value}" src="{document(concat('udata://system/makeThumbnailFull/(',substring(document(concat('upage://',@id,'.anons_pic'))//value,2),')/324/324'))//src}"/>
					                                </a>
					                                <a class="h5" href="{@link}" umi:field-name="h1" umi:element-id="{@id}">
														<xsl:value-of select=".//property[@name='h1']/value"/>
					                                </a>
					                                <div class="small" umi:field-name="doctor_specialization" umi:element-id="{@id}">
														<xsl:value-of select=".//property[@name='doctor_specialization']/value"/>
					                                </div>
					                            </li>
				                            </xsl:for-each>
				                        </ul>
				                    </div>

									<script><![CDATA[

										    $('#doctors').bxSlider({
												]]>
												<xsl:choose>
													<xsl:when test="$isMob = 5">
														minSlides: 4,
														maxSlides: 4,
													</xsl:when>
													<xsl:otherwise>
														minSlides: 3,
														maxSlides: 3,
													</xsl:otherwise>
												</xsl:choose>

												<![CDATA[
												slideWidth: 200,
												slideMargin: 10,
										    	captions: true,
										    	controls: true,
										        pager: false,
										        nextText:'',
										        prevText:'',
										    	auto: true,
										    	speed: 400,
										    	]]>
										    	<xsl:if test="$conf//property[@name='slider_pause']/value">
											    	<xsl:text>pause: </xsl:text>
											    	<xsl:value-of select="$conf//property[@name='slider_pause']/value"/>
											    	<xsl:text>,</xsl:text>
												</xsl:if>
										    	<![CDATA[
										        touchEnabled: true,
										    });

										     window.onscroll = function() {
										    	showSlider();
										    };

										    function showSlider(){
										      var scrolled = window.pageYOffset || document.documentElement.scrollTop;
										      var innerHeight = document.documentElement.clientHeight;
										      var sliderPosition = 0;

										      var sliderPosition = $('.slider').offset().top;

										      if(scrolled+innerHeight >= sliderPosition){
										        $('.slider').animate({opacity: 1}, 1000);
										      };
										    };

										    showSlider(); 


									    ]]>
									</script>

				                </xsl:if>

								<xsl:if test="not(result/@method = 'object' or $parentId = $doctorsId)">
				                    <!-- ORDER -->
				                    <div class="h2" id="order" umi:object-id="{$conf//object/@id}" umi:field-name="form_title">
										<xsl:value-of select="$conf//property[@name='form_title']/value" />
				                    </div>
				                    <div class="border form" style="border-color:{$conf//property[@name='border-color']/value}">
				                    	<xsl:apply-templates select="document('udata://webforms/add/medcenter/')//udata" />
				                    </div>
								</xsl:if>
			                </div>
			            </div>
			        </div>

			        <footer>
			            <div class="footer">
			                <div class="container">
			                    <div class="row">
			                        <div class="col-md-2 col-sm-2 text-center">
			                            <a href="/" class="logo text-center">
			                                <span class="logo-img" umi:field-name="logo_footer" umi:object-id="{$conf//object/@id}">
												<xsl:call-template name="makeThumbnail">
													<xsl:with-param name="object_id" select="$conf//object/@id" />
													<xsl:with-param name="field_name">logo_footer</xsl:with-param>
													<xsl:with-param name="width">60</xsl:with-param>
													<xsl:with-param name="height">50</xsl:with-param>
													<xsl:with-param name="alt" select="$conf//property[@name='company_name']/value" />
												</xsl:call-template>
			                                </span>
			                                <span class="name footer_name" umi:field-name="company_name" umi:object-id="{$conf//object/@id}">
												<xsl:value-of select="$conf//property[@name='company_name']/value"/>
			                                </span>
			                                <span umi:field-name="slogan" umi:object-id="{$conf//object/@id}">
												<xsl:value-of select="$conf//property[@name='slogan']/value" />
			                                </span>
			                            </a>
										<div class="copyright visible-xs-block">
											<xsl:text>© </xsl:text>
											<span umi:object-id="{$conf//object/@id}" umi:field-name="foundation_year">
												<xsl:value-of select="$conf//property[@name='foundation_year']/value"/>
											</span>
											<xsl:text> </xsl:text>
											<span umi:object-id="{$conf//object/@id}" umi:field-name="company_name">
												<xsl:value-of select="$conf//property[@name='company_name']/value"/>
											</span>
										</div>
			                        </div>
			                        <div class="col-md-2 col-sm-2 vert-divider xs-c">
			                            <div class="footer-header" umi:object-id="{$conf//object/@id}" umi:field-name="menu_footer_header">
											<xsl:value-of select="$conf//property[@name='menu_footer_header']/value"/>
			                            </div>
										<xsl:apply-templates select="document('udata://content/menu/0/1/0/')/udata" mode="menu_footer"/>
			                        </div>
			                        <div class="col-md-4 col-sm-4 vert-divider xs-c">
			                            <div class="footer-header" umi:element-id="{$activitiesId}" umi:field-name="h1">
											<xsl:value-of select="document(concat('upage://', $activitiesId, '.h1'))//udata//value"/>
			                            </div>
			                            <xsl:apply-templates select="document(concat('udata://content/menu/0/1/', $activitiesId))//udata" mode="menu_activities"/>

		                            <div class="clear"></div>
			                        </div>
			                        <div class="col-md-4 col-sm-4 vert-divider xs-c">
			                        	<noindex>
			                        		<div class="share-wrap">
				                        		<span class="share-title">Поделиться:</span>
					                            <div class="social-networks">
													<xsl:value-of select="$conf//property[@name='social_networks']/value" disable-output-escaping="yes"/>
					                            </div>
			                        		</div>
			                           </noindex>

		                                <p>
		                                	<xsl:if test="$conf//property[@name='postalcode']/value">
												<span umi:field-name="postalcode" umi:object-id="{$conf//object/@id}">
													<xsl:value-of select="$conf//property[@name='postalcode']/value" />
												</span>,&nbsp;
											</xsl:if>

											<span umi:field-name="addresslocality" umi:object-id="{$conf//object/@id}">
												<xsl:value-of select="$conf//property[@name='addresslocality']/value" />
											</span>,
											<br/>

											<span umi:field-name="streetaddress" umi:object-id="{$conf//object/@id}" >
												<xsl:value-of select="$conf//property[@name='streetaddress']/value" />
											</span>
											<br/>

											<xsl:if test="$conf//property[@name='telephone']/value">
			                                    <a umi:field-name="telephone" umi:object-id="{$conf//object/@id}" target="_blank" value="{$conf//property[@name='telephone']/value}" href="tel:{$conf//property[@name='telephone']/value}">
													<xsl:value-of select="$conf//property[@name='telephone']/value" />
			                                    </a>,
			                                    <br/>
											</xsl:if>

											<xsl:if test="$conf//property[@name='telephone2']/value">
			                                    <a umi:field-name="telephone2" umi:object-id="{$conf//object/@id}" target="_blank" value="{$conf//property[@name='telephone2']/value}" href="tel:{$conf//property[@name='telephone2']/value}">
													<xsl:value-of select="$conf//property[@name='telephone2']/value" />
			                                    </a>,
			                                    <br/>
											</xsl:if>

											<xsl:if test="$conf//property[@name='email']/value">
			                                    <a umi:field-name="email" umi:object-id="{$conf//object/@id}" href="mailto:{$conf//property[@name='email']/value}">
													<xsl:value-of select="$conf//property[@name='email']/value" />
			                                    </a>
											</xsl:if>
		                                </p>

			                            <p>
			                                <a href="{document(concat('udata://content/get_page_url/', $sitemapId,'/'))/udata}" class="site-map" umi:element-id="{$sitemapId}" umi:field-name="h1">
												<xsl:value-of select="document(concat('upage://', $sitemapId, '.h1'))//udata//value"/>
			                                </a>
			                            </p>

										<p class="designer_info">
											<xsl:if test="$conf//property[@name='designer_logo']/value">
												<span class="designer_logo">
													<img alt="{$conf//property[@name='designer']/value}" src="{document(concat('udata://system/makeThumbnail/(',substring($conf//property[@name='designer_logo']/value,2),')/15/(auto)'))//src}"/>
												</span>
											</xsl:if>

											<xsl:if test="$conf//property[@name='designer']/value">
												<span>Создание сайта – </span>
												<a href="{$conf//property[@name='designer_link']/value}">
													<xsl:value-of select="$conf//property[@name='designer']/value"/>
												</a>
												<span>
													<xsl:value-of select="$conf//property[@name='designer_year']/value"/>
												</span>
												<br/>
											</xsl:if>

											<xsl:if test="$conf//property[@name='umi_prefix']/value">
												<span umi:object-id='{$conf//object/@id}' umi:field-name='umi_prefix'>
													<xsl:value-of select="$conf//property[@name='umi_prefix']/value"/>
												</span>&nbsp;
											</xsl:if>
											<xsl:if test="$conf//property[@name='umi']/value">
												<a href="{$conf//property[@name='umi_link']/value}" umi:object-id='{$conf//object/@id}' umi:field-name='umi'>
													<xsl:value-of select="$conf//property[@name='umi']/value"/>
												</a>
											</xsl:if>
										</p>
			                        </div>
			                    </div>
								<div class="row hidden-xs">
									<div class="col-md-2 col-sm-2 text-center copyright">
										<xsl:text>© </xsl:text>
										<span umi:object-id="{$conf//object/@id}" umi:field-name="foundation_year">
											<xsl:value-of select="$conf//property[@name='foundation_year']/value"/>
										</span>
										<xsl:text> </xsl:text>
										<span umi:object-id="{$conf//object/@id}" umi:field-name="company_name">
											<xsl:value-of select="$conf//property[@name='company_name']/value"/>
										</span>
									</div>
								</div>
			                </div>
			            </div>
			        </footer>
			        <span id="go_top" title="Наверх"></span>
					<noindex>
						<xsl:value-of select="$conf//property[@name='yandex_metrika']/value" disable-output-escaping="yes"/>
					</noindex>

			        <!-- <script src="{$template-resources}js/bootstrap.js"></script>
					<script src="{$template-resources}js/jquery.form.js"></script>
			        <script src="{$template-resources}js/jquery.maskedinput.js"></script>
			        <script src="{$template-resources}js/jquery.scrollTo-1.4.3.1.js"></script>
			        <script src="{$template-resources}js/chosen.jquery.min.js"></script>
					<script src="{$template-resources}js/jquery.tablesorter.min.js"></script>
			        <script src="{$template-resources}js/scripts.js"></script> -->

				</body>
			</html>
		</xsl:template>
	</xsl:stylesheet>
