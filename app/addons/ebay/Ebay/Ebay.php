<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Ebay;

use Tygh\Settings;
use Tygh\Registry;
use Tygh\Http;
use Tygh\Helpdesk;

class Ebay
{
    const X_EBAY_API_COMPATIBILITY_LEVEL = '823';
    
    const DEFAULT_REQUEST_FORMAT = 'text/xml';
    
    const STAGING_URL = 'https://api.sandbox.ebay.com/ws/api.dll';
    
    const PRODUCTION_URL = 'https://api.ebay.com/ws/api.dll';
    
    /**
     * Instance of class
     * @static
     * @var Ebay
     */
    private static $_instance;

    private $ebay_url;

    private $credentials = '';
    
    public  $site_id = null;
    
    public static $errors = array();
    
    public function GetOrders($params)
    {
        $credentials = $this->credentials;
    
        if (empty($params['CreateTimeFrom'])) {
            $params['NumberOfDays'] = 30;
        }
        
        foreach ($params as $k => $v) {
            $params[$k] = "<$k>$v</$k>";
        }
        $params = implode("\n", $params);
    
        $xml = <<<EOT
            <?xml version="1.0" encoding="utf-8"?>
            <GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                $credentials
                $params
            </GetOrdersRequest>
EOT;
        list($trans_id, $result) = $this->_request($xml, 'GetOrders');
        $_result = array();
        if ($result->Ack == 'Success' && !empty($result->OrderArray)) {
        
            $total_orders = (int) $result->PaginationResult->TotalNumberOfEntries;
            if ($total_orders > 1) {

                foreach ($result->OrderArray->Order as $order) {
                        $_result[] = (array) $order;
                }
            } else {

                foreach ($result->OrderArray as $order) {
                        $_result[] = (array) $order->Order;
                }
            }
        }

        return array($trans_id, $_result);
    }

    private function prepareShipping($product, $template)
    {
        $shippings = '';
        if ($template['shipping_type'] == 'C') {
            $package_info = unserialize($product['shipping_params']);
            $PackageDepth =  $package_info['box_height'];
            $PackageLength = $package_info['box_length'];
            $PackageWidth = $package_info['box_width'];
            $weight_lbs = ($product['weight'] * Registry::get('settings.General.weight_symbol_grams')) / '453.6';
            $WeightMajor = floor($weight_lbs);
            $WeightMinor = ($weight_lbs - $WeightMajor) * 16;
            $shippings = <<<EOT
                <ReturnPolicy>
                    <ReturnsAcceptedOption>$template[return_policy]</ReturnsAcceptedOption>
                    <RefundOption>$template[refund_method]</RefundOption>
                    <ReturnsWithinOption>$template[contact_time]</ReturnsWithinOption>
                    <Description>$template[return_policy_descr]</Description>
                    <ShippingCostPaidByOption>$template[cost_paid_by]</ShippingCostPaidByOption>
                </ReturnPolicy>
                <ShippingDetails>
                    <ShippingType>Calculated</ShippingType>
                    <CalculatedShippingRate>
                        <PackageDepth>$PackageDepth</PackageDepth>
                        <PackageLength>$PackageLength</PackageLength>
                        <PackageWidth>$PackageWidth</PackageWidth>
                        <ShippingPackage>$product[package_type]</ShippingPackage>
                        <WeightMajor>$WeightMajor</WeightMajor>
                        <WeightMinor>$WeightMinor</WeightMinor>
                        <OriginatingPostalCode>95125</OriginatingPostalCode>
                    </CalculatedShippingRate>
                    <ShippingServiceOptions>
                        <ShippingService>$template[shippings]</ShippingService>
                        <ShippingServicePriority>1</ShippingServicePriority>
                    </ShippingServiceOptions>
                </ShippingDetails>
EOT;
        } else {
            $template['shipping_cost'] = number_format($template['shipping_cost'], 2, '.', '');
            $template['shipping_cost_additional'] = number_format($template['shipping_cost_additional'], 2, '.', '');

            if ($template['free_shipping'] == 'N') {
                $free_shipping = "false";
                $shipping_cost = '<ShippingServiceCost currencyID="' . CART_PRIMARY_CURRENCY . '">'. $template['shipping_cost'] . '</ShippingServiceCost>';
                $shipping_cost_additional = '<ShippingServiceAdditionalCost currencyID="' . CART_PRIMARY_CURRENCY . '">'. $template['shipping_cost_additional'] . '</ShippingServiceAdditionalCost>';
            } else {
                $shipping_cost = null;
                $shipping_cost_additional = '<ShippingServiceAdditionalCost currencyID="' . CART_PRIMARY_CURRENCY . '">'. $template['shipping_cost_additional'] . '</ShippingServiceAdditionalCost>';
                $free_shipping = 'true';
            }

            $shippings = <<<EOT
            <ReturnPolicy>
                <ReturnsAcceptedOption>$template[return_policy]</ReturnsAcceptedOption>
                <RefundOption>$template[refund_method]</RefundOption>
                <ReturnsWithinOption>$template[contact_time]</ReturnsWithinOption>
                <Description>$template[return_policy_descr]</Description>
                <ShippingCostPaidByOption>$template[cost_paid_by]</ShippingCostPaidByOption>
            </ReturnPolicy>
            <ShippingDetails>
                <ShippingType>Flat</ShippingType>
                <ShippingServiceOptions>
                    <FreeShipping>$free_shipping</FreeShipping>
                    $shipping_cost
                    <ShippingService>$template[shippings]</ShippingService>
                    <ShippingServicePriority>1</ShippingServicePriority>
                    $shipping_cost_additional
                </ShippingServiceOptions>
            </ShippingDetails>
EOT;
        }

        return $shippings;
    }

    public function AddItems($products = array(), $template, $images_data)
    {
        $credentials = $this->credentials;
        $currency = CART_PRIMARY_CURRENCY;
        $company = fn_get_company_placement_info(0);
        $site = $template['site'];

        $items = '';
        $i = 0;
        $item_hashes = array();

        foreach ($products as $k => $product) {
            $i += 1;
            $shipping = $this->prepareShipping($product, $template);
            $price = fn_format_price($product['price']);
            $uuid = md5($product['product_id'] . Helpdesk::getStoreKey() . $product['ebay_template_id']);
            $payments = '<PaymentMethods>' . implode("</PaymentMethods>\n<PaymentMethods>", $template['payment_methods']) . '</PaymentMethods>';
            if (in_array('PayPal', $template['payment_methods'])) {
                $payments .= "\n<PayPalEmailAddress>$template[paypal_email]</PayPalEmailAddress>";
            }
            if ($product['override'] == "Y") {
                $title = substr(strip_tags($product['ebay_title']), 0, 80);
                $description = !empty($product['ebay_description']) ? $product['ebay_description'] : $product['full_description'];
            } else {
                $title = substr(strip_tags($product['product']), 0, 80);
                $description = $product['full_description'];
            }

            $hash_data = array(
                'price' => $price,
                'title' => $title,
                'description' => $description,
            );

            $picture_details = '';
            if (!empty($product['main_pair']) && !empty($product['main_pair']['detailed']) && !empty($product['main_pair']['detailed']['http_image_path'])) {
                if ($images_data[md5($product['main_pair']['detailed']['http_image_path'])]) {
                    $picture_details .= "<PictureURL>" . $images_data[md5($product['main_pair']['detailed']['http_image_path'])] . "</PictureURL>\n";
                } else {
                    $picture_details .= "<PictureURL>" . $product['main_pair']['detailed']['http_image_path'] . "</PictureURL>\n";
                }
            }
            if ($product['image_pairs']) {
                foreach ($product['image_pairs'] as $image_pair) {
                    if (!empty($image_pair['detailed']) && !empty($image_pair['detailed']['http_image_path'])) {
                        if ($images_data[md5($image_pair['detailed']['http_image_path'])]) {
                            $picture_details .= "<PictureURL>" . $images_data[md5($image_pair['detailed']['http_image_path'])] . "</PictureURL>\n";
                        }
                    }
                }
            }

            $picture_details = empty($picture_details) ? '' : "<PictureDetails>\n$picture_details</PictureDetails>";
            
            $product_features = '';
            if (!empty($product['product_features'])) {
                $hash_data['product_features'] = serialize($product['product_features']);
                $product_features = '<ItemSpecifics>' . fn_prepare_xml_product_features($product['product_features']) . '</ItemSpecifics>';
            }
            
            $product_options = '';
            $inventory_combinations = db_get_array("SELECT combination FROM ?:product_options_inventory WHERE product_id = ?i AND amount > 0 AND combination != ''", $product['product_id']);
            if (!empty($product['product_options']) && !empty($inventory_combinations)) {
                    $params = array (
                        'page' => 1,
                        'product_id' => $product['product_id'],
                    );
                $product_options = '<Variations>' . fn_prepare_xml_product_options($params, $product, $images_data) . '</Variations>';
            }
            if (empty($product_options)) {
                $start_price = <<<EOT
                <StartPrice currencyID="$currency">$price</StartPrice>
EOT;
                $product_quantity = <<<EOT
                <Quantity>$product[amount]</Quantity>

EOT;
            } else {
                $start_price = '';
                $product_quantity = '';
            }
            $product_out_of_weight = array();
            if ($product['weight'] == 0) {
                $product_out_of_weight[] = $product['product_id'];
            }
            $items .= <<<EOT
            <AddItemRequestContainer>
                <MessageID>$i</MessageID>
                <Item>
                    <UUID>$uuid</UUID>
                    <Site>$site</Site>
                    <ListingType>FixedPriceItem</ListingType>
                    <Currency>$currency</Currency>
                    <PrimaryCategory>
                        <CategoryID>$template[category]</CategoryID>
                    </PrimaryCategory>
                    <SecondaryCategory>
                        <CategoryID>$template[sec_category]</CategoryID>
                    </SecondaryCategory>
                    <ConditionID>$template[condition_id]</ConditionID>
                    <CategoryMappingAllowed>true</CategoryMappingAllowed>
                    <Country>$company[company_country]</Country>
                    <PostalCode>$company[company_zipcode]</PostalCode>
                    <Title><![CDATA[$title]]></Title>
                    <Description><![CDATA[$description]]></Description>
                    $payments
                    <ListingDuration>$template[ebay_duration]</ListingDuration>
                    <DispatchTimeMax>$template[dispatch_days]</DispatchTimeMax>
                    $shipping
                    $picture_details
                    $product_features
                    $product_options
                    $start_price
                    $product_quantity
                </Item>
            </AddItemRequestContainer>
EOT;
            $item_hashes[$k] = fn_crc32(implode('_', $hash_data));
        }
        $xml = <<<EOT
            <?xml version="1.0" encoding="utf-8"?>
            <AddItemsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                $credentials
                <MessageID>1</MessageID>
                $items
            </AddItemsRequest>
EOT;

        list($trans_id, $result) = $this->_request($xml, 'AddItems');
        $_result = array();

        if ($result->Ack != 'Failure') {
            if ($result->AddItemResponseContainer) {
                $count = 0;
                foreach ($result->AddItemResponseContainer as $k => $item) {
                    if (!empty($item->Errors)) {
                        $this->_errors($item, $trans_id, 'AddItems');
                    }
                    $_result[$count] = (array) $item;
                    $_result[$count]['product_hash'] = $item_hashes[$count];
                    $count ++;
                }
            }
        }
        if (!empty($product_out_of_weight) && $template['shipping_type'] == 'C') {
            fn_set_notification('W', __('warning'), __('product_out_of_weight', array("[ids]" => implode(",", $product_out_of_weight))));
        }

        return array($trans_id, $_result);
    }

    public function RelistItem($product = array(), $template, $images_data)
    {
        $credentials = $this->credentials;
        $items = '<Item>' . $this->_getBaseItemData($product, $template, $images_data) . '</Item>';
        $deleted_fields = $this->_getDeletedFields($template);
        $xml = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<RelistItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
$credentials
$items
$deleted_fields
</RelistItemRequest>
EOT;

        list($trans_id, $result) = $this->_request($xml, 'RelistItem');
        $error_code = 0;
        if ($result->Ack == 'Failure') {
            $error_code = (int)$result->Errors->ErrorCode;
            $this->_errors($result, $trans_id, 'RelistItem');
        }

        return array($trans_id, $result, $error_code);
    }

    private function _getBaseItemData($product, $template, $images_data)
    {
        $currency = CART_PRIMARY_CURRENCY;
        $shippings = $this->prepareShipping($product, $template);
        $company = fn_get_company_placement_info(Registry::get('runtime.company_id'));
        $price = fn_format_price($product['price']);
        $picture_details = '';

        if ($product['override'] == "Y") {
            $title = substr(strip_tags($product['ebay_title']), 0, 80);
            $description = !empty($product['ebay_description']) ? $product['ebay_description'] : $product['full_description'];
        } else {
            $title = substr(strip_tags($product['product']), 0, 80);
            $description = $product['full_description'];
        }

        $payments = '<PaymentMethods>' . implode("</PaymentMethods>\n<PaymentMethods>", $template['payment_methods']) . '</PaymentMethods>';
        if (in_array('PayPal', $template['payment_methods'])) {
            $payments .= "\n<PayPalEmailAddress>$template[paypal_email]</PayPalEmailAddress>";
        }

        if (!empty($product['main_pair']) && !empty($product['main_pair']['detailed']) && !empty($product['main_pair']['detailed']['http_image_path']) && $images_data[md5($product['main_pair']['detailed']['http_image_path'])]) {
            $image_url = $images_data[md5($product['main_pair']['detailed']['http_image_path'])];
            $picture_details .= "<PictureURL>$image_url</PictureURL>\n";
        }
        if ($product['image_pairs']) {
            foreach ($product['image_pairs'] as $image_pair) {
                if (!empty($images_data[md5($image_pair['detailed']['http_image_path'])])) {
                    $image_url = $images_data[md5($image_pair['detailed']['http_image_path'])];
                    $picture_details .= "<PictureURL>$image_url</PictureURL>\n";
                }
            }
        }
        $picture_details = empty($picture_details) ? '' : "<PictureDetails>\n$picture_details</PictureDetails>";

        $product_features = '';
        if (!empty($product['product_features'])) {
            $product_features = '<ItemSpecifics>' . fn_prepare_xml_product_features($product['product_features']) . '</ItemSpecifics>';
        }

        $product_options = '';
        $start_price = '';
        $product_quantity = '';
        $inventory_combinations = db_get_array("SELECT combination FROM ?:product_options_inventory WHERE product_id = ?i AND amount > 0 AND combination != ''", $product['product_id']);
        if (!empty($product['product_options']) && !empty($inventory_combinations)) {
            $params = array (
                'page' => 1,
                'product_id' => $product['product_id'],
            );
            $product_options = '<Variations>' . fn_prepare_xml_product_options($params, $product, $images_data) . '</Variations>';
        }
        if (empty($product_options)) {
            $start_price = '<StartPrice currencyID="' . $currency . '">' . $price . '</StartPrice>';
            $product_quantity = '<Quantity>' . $product['amount'] . '</Quantity>';
        }

        $xml = <<<EOT
<ItemID>$product[ebay_item_id]</ItemID>
<Site>$template[site]</Site>
<ListingType>FixedPriceItem</ListingType>
<Currency>$currency</Currency>
<PrimaryCategory>
<CategoryID>$template[category]</CategoryID>
</PrimaryCategory>
<SecondaryCategory>
<CategoryID>$template[sec_category]</CategoryID>
</SecondaryCategory>
<ConditionID>$template[condition_id]</ConditionID>
<CategoryMappingAllowed>true</CategoryMappingAllowed>
<Country>$company[company_country]</Country>
<PostalCode>$company[company_zipcode]</PostalCode>
<Title><![CDATA[$title]]></Title>
<Description><![CDATA[$description]]></Description>
$payments
<ListingDuration>$template[ebay_duration]</ListingDuration>
<DispatchTimeMax>$template[dispatch_days]</DispatchTimeMax>
$shippings
$picture_details
$product_features
$product_options
$start_price
$product_quantity
EOT;
        return $xml;
    }

    private function _getDeletedFields($template)
    {
        $deleted_fields = '';
        if (empty($template['sec_category'])) {
            $deleted_fields .= '<DeletedField>Item.SecondaryCategory</DeletedField>';
        }

        return $deleted_fields;
    }

    public function ReviseItem($product = array(), $template, $images_data)
    {
        $credentials = $this->credentials;
        $items = '<Item>' . $this->_getBaseItemData($product, $template, $images_data) . '</Item>';

        $xml = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
$credentials
<MessageID>1</MessageID>
$items
</ReviseItemRequest>
EOT;
        list($trans_id, $result) = $this->_request($xml, 'ReviseItem');
        $_result = array();
        $error_code = 0;
        if ($result->Ack == 'Failure') {
            $error_code = (int)$result->Errors->ErrorCode;
            if ($error_code != 291) {
                $this->_errors($result, $trans_id, 'ReviseItem');
            }
        }
        
        return array($trans_id, $result, $error_code);
    }

    private function _uploadImage($img_url)
    {
        $result = '';
        if ($img_url) {
            $credentials = $this->credentials;
            $pic_name = md5($img_url);
            $xml = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<UploadSiteHostedPictures xmlns="urn:ebay:apis:eBLBaseComponents">
$credentials
<PictureName>$pic_name</PictureName>
<ExternalPictureURL>$img_url</ExternalPictureURL>
</UploadSiteHostedPictures>
EOT;
            list($trans_id, $_result) = $this->_request($xml, 'UploadSiteHostedPictures');
            $result = $_result->SiteHostedPictureDetails->FullURL;
        }

        return $result;
    }

    public function UploadImages($products)
    {
        $image_urls = array();
        if (!empty($products)) {
            $image_urls[md5('no_image.png')] = $this->_uploadImage(Registry::get('config.http_location') . '/images/no_image.png');
            foreach ($products as $product) {
                if (!empty($product['main_pair']) && !empty($product['main_pair']['detailed']) && !empty($product['main_pair']['detailed']['absolute_path'])) {
                    $image_urls[md5($product['main_pair']['detailed']['http_image_path'])] = $this->_uploadImage($product['main_pair']['detailed']['http_image_path']);
                }
                if ($product['image_pairs']) {
                    foreach ($product['image_pairs'] as $image_pair) {
                        if (!empty($image_pair['detailed']) && !empty($image_pair['detailed']['http_image_path'])) {
                            $image_urls[md5($image_pair['detailed']['http_image_path'])] = $this->_uploadImage($image_pair['detailed']['http_image_path']);
                        }
                    }
                }
                if ($product['product_options']) {
                    foreach ($product['product_options'] as $product_option) {
                        if ($product_option['variants']) {
                            foreach ($product_option['variants'] as $option_variant) {
                                if ($option_variant['image_pair'] && $option_variant['image_pair']['icon'] && $option_variant['image_pair']['icon']['http_image_path']) {
                                    $image_urls[md5($option_variant['image_pair']['icon']['http_image_path'])] = $this->_uploadImage($option_variant['image_pair']['icon']['http_image_path']);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $image_urls;
    }

    public function GetEbayDetails($details = 'SiteDetails')
    {
        $credentials = $this->credentials;
    
        $xml = <<<EOT
            <?xml version="1.0" encoding="utf-8"?>
            <GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                $credentials
                <DetailName>$details</DetailName>
            </GeteBayDetailsRequest>
EOT;

        list($trans_id, $result) = $this->_request($xml, 'GeteBayDetails');

        $_result = array();

        if ($result->Ack == 'Success' && $result->SiteDetails && $details == 'SiteDetails') {
            $sites = $result->SiteDetails;

            for ($i = 0; $i < count($sites); $i++) {
                $site_id = (string) $sites[$i]->SiteID;
                $_result[$site_id] = (string) $sites[$i]->Site;
            }
        } elseif ($result->Ack == 'Success' && $result->ShippingServiceDetails && $details == 'ShippingServiceDetails') {
            foreach ($result->ShippingServiceDetails as $service) {
                unset($service->ShippingServicePackageDetails);
                $_result[] = (array) $service;
            }
        } elseif ($result->Ack == 'Failure') {
            return array($trans_id, false);
        }

        return array($trans_id, $_result);
    }

    public function GetCategoryFeatures($category_id, $features_list = array())
    {
        $credentials = $this->credentials;
        $features = '';
        if (!empty($features_list)) {
            $features = '<FeatureID>' . implode('</FeatureID><FeatureID>', $features_list) . '</FeatureID>';
        }
    
        $xml = <<<EOT
            <?xml version="1.0" encoding="utf-8"?>
            <GetCategoryFeaturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                $credentials
                <CategoryID>$category_id</CategoryID>
                $features
                <DetailLevel>ReturnAll</DetailLevel>
                <ViewAllNodes>true</ViewAllNodes>
            </GetCategoryFeaturesRequest>
EOT;
        list($trans_id, $result) = $this->_request($xml, 'GetCategoryFeatures');

        $features = array();
        if ($result->Ack == 'Success' && ($result->Category || $result->SiteDefaults)) {
        
            if ($result->SiteDefaults) {
                $features = (array) $result->SiteDefaults;
                $features['ListingDurationIds'] = $this->_convertListingDuration($result->SiteDefaults);
            }

            if ($result->Category) {
                $features = fn_array_merge($features, (array) $result->Category);
                $features['ListingDurationIds'] = $this->_convertListingDuration($result->SiteDefaults);
            }
            $features['listing_duration'] = array();
            foreach ($result->FeatureDefinitions->ListingDurations[0] as $key => $value) {
                foreach ($value->attributes() as $a => $b) {
                    if ((string)$a == 'durationSetID' && (int)$b == $features['ListingDurationIds']['FixedPriceItem']) {
                        foreach ($value->Duration as $v) {
                            $features['listing_duration'][] = (string)$v;
                        }
                    }
                }
            }
        }

        return array($trans_id, $features);
    }

    private function _convertListingDuration($data)
    {
        $result = array();
        if ($data) {
            foreach ($data->ListingDuration as $key => $value) {
                foreach ($value->attributes() as $a => $b) {
                    $result[(string)$b] = (string)$value;
                }
            }
        }

        return $result;
    }

    public function GetCategories($details = 'ReturnAll', $level = '', $parent = '')
    {
        $credentials = $this->credentials;
        
        if (!empty($level)) {
            $level = "<LevelLimit>$level</LevelLimit>";
        }
    
        if (!empty($details)) {
            $details = "<DetailLevel>$details</DetailLevel>";
        }
        
        if (!empty($parent)) {
            $parent = "<CategoryParent>$parent</CategoryParent>";
        }
    
        $xml = <<<EOT
            <?xml version="1.0" encoding="utf-8"?>
            <GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                $credentials
                $details
                $level
                $parent
            </GetCategoriesRequest>
EOT;

        list($trans_id, $result) = $this->_request($xml, 'GetCategories');
        $categories = array();

        if ($result->Ack == 'Success' && $result->CategoryArray && $result->CategoryArray->Category) {
            $_categories = $result->CategoryArray->Category;

            for ($i = 0; $i < count($_categories); $i++) {
                if (!$_categories[$i]->Expired) {
                    $category = (array) $_categories[$i];
                    $categories[$category['CategoryID']] = $category;
                }
            }
        }

        return array($trans_id, $categories);
    }

    public function GetCategoryVersion()
    {
        $credentials = $this->credentials;
        
        $xml = <<<EOT
            <?xml version="1.0" encoding="utf-8"?>
            <GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                $credentials
            </GetCategoriesRequest>
EOT;

        list($trans_id, $result) = $this->_request($xml, 'GetCategories');

        $category_version = 0;
        if ($result->Ack == 'Success' && $result->CategoryVersion) {
            $category_version = $result->CategoryVersion;
        }

        return array($trans_id, $category_version);
    }

    private function _request($xml, $method, $extra = array())
    {
        if (empty($extra)) {
            $extra = array(
                'headers' => $this->_headers($method)
            );
        }
        $id = md5(uniqid(rand()));

        $response = Http::post($this->ebay_url, $xml, $extra);

        return $this->_response($response, $id, $method);
    }
    
    private function _response($xml, $id, $method)
    {
        $response = simplexml_load_string($xml);

        return array($id, $response);
    }

    private function _errors($response, $id, $method)
    {
        if (empty($response)) {
            return false;
        }
        $errors = array();
        $status = '';
        $log_errors = array();
        if ($response->Ack != 'Success') {
            $errors = $response->Errors;
            $status = !empty($response->Ack) ? (string) $response->Ack : __('error');
            
            $extra = !empty(self::$errors[$id]) ? count(self::$errors[$id]) : 0;
            if (is_array($errors)) {
                for ($i = $extra; $i < (count($errors) + $extra); $i++) {
                    $log_errors[] = self::$errors[$id][$i] = (array) $errors[$i];
                    fn_set_notification('W', __('warning'), (string) $errors[$i]->LongMessage);
                }
            } else {
                $log_errors[] = self::$errors[$id][$extra] = (array) $errors;
                fn_set_notification('W', __('warning'), (string) $errors->LongMessage);
            }
        }
        
        fn_log_event('ebay_requests', 'all', array(
            'method' => $method,
            'status' => $status,
            'errors' => $log_errors,
        ));
        return true;
    }
    
    private function _headers($method)
    {
        return array(
            'X-EBAY-API-COMPATIBILITY-LEVEL: ' . self::X_EBAY_API_COMPATIBILITY_LEVEL,
            'X-EBAY-API-CALL-NAME: ' . $method,
            'X-EBAY-API-APP-NAME: ' . Registry::get('addons.ebay.app_id'),
            'X-EBAY-API-DEV-NAME: ' . Registry::get('addons.ebay.dev_id'),
            'X-EBAY-API-CERT-NAME: ' . Registry::get('addons.ebay.cert_id'),
            'X-EBAY-API-SITEID: ' . $this->site_id,
            'Content-Type: ' . self::DEFAULT_REQUEST_FORMAT
        );
    }

    /**
     * Returns static object of Ebay class or create it if it is not exists.
     *
     * @return Ebay Instance of class
     */
    public static function instance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new Ebay();
        }

        return self::$_instance;
    }

    private function __construct()
    {
        $this->ebay_url = (Registry::get('addons.ebay.listing_mode') == 'P') ? self::PRODUCTION_URL : self::STAGING_URL;
        $this->site_id = ($this->site_id === null) ? Registry::get('addons.ebay.site_id') : $this->site_id;
        
        $token = Registry::get('addons.ebay.token');
        if (empty($token)) {
            return false;
        }
        $this->credentials = "<RequesterCredentials><eBayAuthToken>$token</eBayAuthToken></RequesterCredentials>";
        
        return true;
    }
}
