<?php
/*------------------------------------------------------------------------
	# SM Listing Tabs - Version 2.0.1
	# Copyright (c) 2014 YouTech Company. All Rights Reserved.
	# @license - Copyrighted Commercial Software
	# Author: YouTech Company
	# Websites: http://www.magentech.com
   -------------------------------------------------------------------------*/
$helper = Mage::helper('listingtabs/data');
if ($this->_isAjax()) {
    $type_filter = $this->_getConfig('filter_type');
    switch ($type_filter) {
        case 'categories':
            $catid = $this->getRequest()->getPost('categoryid');
            $catid = $this->getRequest()->getPost('categoryid');
            $child_items = $this->_getProductInfor($catid);
            break;
        case 'fieldproducts':
            $field_order = $this->getRequest()->getPost('categoryid');
            $catid = $this->_getCatIds();
            $child_items = $this->_getProductInfor($catid, $field_order);
            break;
    }
}

if (!empty($child_items)) {
    $k = $this->getRequest()->getPost('ajax_listingtags_start', 0);
    foreach ($child_items as $_product) {
        $k++; 

        $now = date("Y-m-d");
        $newsFrom = substr($_product->getData('news_from_date'), 0, 10);
        $newsTo = substr($_product->getData('news_to_date'), 0, 10);
        $specialprice = $_product->getData('special_price');

    ?>
        <div class="ltabs-item new-ltabs-item respl-item">
            <div class="item-inner">
                <?php
                if ($_product->_image) {
                    ?>
                    <div class="w-image-box">
                        <a class="item-image"
                           href="<?php echo $_product->link ?>">
                            <img title="<?php echo $_product->title; ?>"
                                 alt="<?php echo $_product->title; ?>"
                                 src="<?php echo $_product->_image; ?>"/>
                        </a>
                    </div>


                    <?php if ($newsFrom !="" && $now>=$newsFrom && ($now<=$newsTo || $newsTo=="")){?>
                            <div class="new-item">
                                <span><?php echo $this->__('New'); ?></span>
                            </div>
                        <?php }?>
                        
                        <?php if ( $specialprice ){ ?>
                            <!-- <div class="sale-item">
                                <span><?php echo $this->__('Sale'); ?></span>
                            </div> -->
                            <div class="DiscountPercent">
                                <label data-ajax-item="sales"><?php echo round((1-$_product->getData('special_price')/$_product->getData('price')) * 100)."%";?></label>
                                <div class="off"><?php echo $this->__('OFF'); ?></div>
                            </div>
                        <?php }?>


				<div class="item-info">
					
    					<div class="item-title ">
    						<a href="<?php echo $_product->link ?>" <?php echo $helper->parseTarget($this->_getConfig('product_links_target', '_self')) ?>
    						   title="<?php echo $_product->title ?>">
    							<?php echo $helper->truncate($_product->title, $this->_getConfig('product_title_maxlength', 25)); ?>
    						</a>
    					</div>
                        <?php if ((int)$this->_getConfig('product_reviews_count', 1) && $_product->num_rating_summary != null) { ?>
                        <div class="item-review">
                            <?php
                            $this->addReviewSummaryTemplate('sm', 'sm/listingtabs/default_summary.phtml');
                            echo $this->getReviewsSummaryHtml($_product, 'sm', false);
                            ?>
                        </div>
                    <?php } ?>
					<?php
					}
					if ((int)$this->_getConfig('product_price_display', 1)) {
						?>
						<div class="item-price">
							<div class="sale-price">
								<?php echo $this->getPriceHtml($_product, true); ?>
							</div>
						</div>
					<?php
					}
					if ($this->_getConfig('product_title_display', 1) == 1) {
						?>
						
					<?php } ?>
					

					<?php if ($this->_getConfig('product_description_display', 1) == 1 && $helper->_trimEncode($_product->_description) != '') { ?>
						<div class="item-desc">
							<?php echo $_product->_description; ?>
						</div>
					<?php } ?>

					<?php if ($this->_getConfig('product_date_display', 1) == 1) { ?>
						<div class="created-date ">
							<?php echo date("d F Y", strtotime($_product->created_at)); ?>
						</div>
					<?php } ?>
				</div>
                <div class="add-info">
                    <!-- WISTLIST -->
                    <div class="add-wishlist">
                        <?php if ((int)$this->_getConfig('product_addwishlist_display', 1)) { ?>
                            <?php if ($this->helper('wishlist')->isAllow()) { ?>
                               
                                    <a data-toggle="tooltip" data-placement="top" title="<?php echo $this->__('Wishlist') ?>" href="<?php echo $this->helper('wishlist')->getAddUrl($_product) ?>"
                                       class="btn-pd item-wishlist"><?php echo $this->__('Add to Wishlist') ?>
                                    </a>
                            
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <!-- CART -->
    				<div class="add-to-cart">
    					<?php if ((int)$this->_getConfig('product_addcart_display', 1)) { ?>
    						<?php if ($_product->isSaleable()) { ?>
    							<p class="item-addcart">
    								<button type="button" title="<?php echo $this->__('Add to Cart') ?>"
    										class="btn-pd btn-cart"
    										onclick="setLocation('<?php echo $this->getAddToCartUrl($_product) ?>')">
    					 <span>
    					 <span>
    					 <?php echo $this->__('Add to Cart') ?>
    					 </span>
    					 </span>
    								</button>
    							</p>
    						<?php } else { ?>
    							<p class="availability out-of-stock">
    					 <span>
    					 <?php echo $this->__('Out of stock') ?>
    					 </span>
    							</p>
    						<?php } ?>
    					<?php } ?>
    				</div>
                    <!-- COMPARE -->
                    <div class="add-compare">
                        <?php if ((int)$this->_getConfig('product_addcompare_display', 1)) { ?>
                            <?php if ($_compareUrl = $this->getAddToCompareUrl($_product)) { ?>
                          
                            
                            <a data-toggle="tooltip" data-placement="top" title="<?php echo $this->__('Compare') ?>" href="<?php echo $_compareUrl ?>"
                               class="btn-pd item-compare">
                            </a>
                              
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
                <?php //if ((int)$this->_getConfig('product_addwishlist_display', 1) || (int)$this->_getConfig('product_addcompare_display', 1)) { // Commented on Jan 11,2017?>
                    <div class="add-to-links">
                        <a style="display:none;" href="<?php echo $_product->getProductUrl() ?>"></a>
                        <!-- QUICLVIEW -->
                    </div>
                <?php //} // Commented on Jan 11,2017?>

                <?php if ((int)$this->_getConfig('product_readmore_display', 1)) { ?>
                    <div class="item-readmore">
                        <a href="<?php echo $_product->link; ?>"
                           title="<?php echo $_product->title ?>" <?php echo $helper->parseTarget($this->_getConfig('product_links_target', '_self')); ?> >
                            <?php echo $this->_getConfig('product_readmore_text', 'Detail'); ?>
                        </a>
                    </div>
                <?php } ?>
                <div class="other-infor">
                    <?php if ($this->_getConfig('product_hits_display')) { ?>
                        <div class="hits"><?php echo 'Read'; ?>
                            <?php
                            if ($_product->num_view_counts > 1) {
                                echo $_product->num_view_counts . ' times';
                            } else {
                                echo $_product->num_view_counts . ' time';
                            }?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php $clear = 'clr1';
        if ($k % 2 == 0) $clear .= ' clr2';
        if ($k % 3 == 0) $clear .= ' clr3';
        if ($k % 4 == 0) $clear .= ' clr4';
        if ($k % 5 == 0) $clear .= ' clr5';
        if ($k % 6 == 0) $clear .= ' clr6';
        ?>
        <?php if( $this->_getConfig('show_loadmore_slider') == 'loadmore'){ ?>
            <div class="<?php echo $clear; ?>"></div>
        <?php } ?>
    <?php
    }
}?>