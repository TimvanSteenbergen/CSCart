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

namespace Tygh\Shippings;

/**
 * Shipping services interface
 */
interface IService
{
    /**
     * Prepares request data for real-time shipping services.
     *
     * @return array Prepated shipping service information
     */
    public function prepareData($shipping_info);

    /**
     * Gets information about prices
     *
     * @param  string $response response information from real-time shipping services
     * @return array  Service shipping rate as 'cost' and returned errors as 'error'
     */
    public function processResponse($response);

    /**
    * Gets errors information from response
    *
    * @param string $response response information from real-time shipping services
    * @return string Error message from shipping service
    */
    public function processErrors($response);

    /**
     * Gets information about availability multithreading in the module
     *
     * @return bool True if service allows multithreading
     */
    public function allowMultithreading();

    /**
     * Gets data for request
     *
     * @return array Prepared data (method, url, post data, content type)
     */
    public function getRequestData();

    /**
     * Return calculated shipping rates
     *
     * @return array Shipping rates
     */
    public function getSimpleRates();
}
