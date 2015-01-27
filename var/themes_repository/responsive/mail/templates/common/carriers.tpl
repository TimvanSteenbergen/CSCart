{hook name="carriers:list"}

{if $carrier == "usps"}
    {$url = "https://tools.usps.com/go/TrackConfirmAction_input?strOrigTrackNum=`$tracking_number`"}
    {$carrier_name = __("usps")}
{elseif $carrier == "ups"}
    {$url = "http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=`$tracking_number`"}
    {$carrier_name = __("ups")}
{elseif $carrier == "fedex"}
    {$url = "http://fedex.com/Tracking?action=track&amp;tracknumbers=`$tracking_number`"}
    {$carrier_name = __("fedex")}
{elseif $carrier == "aup"}
    {$url = "http://auspost.com.au/track/track.html?id=`$tracking_number`"}
    {$carrier_name = __("australia_post")}
{elseif $carrier == "can"}
    {$url = "http://www.canadapost.com/cpotools/apps/track/personal/findByTrackNumber?trackingNumber=`$tracking_number`"}
    {$carrier_name = __("canada_post")}
{elseif $carrier == "dhl" || $shipping.carrier == "ARB"}
    {$url = "http://www.dhl-usa.com/en/express/tracking.shtml?ShipmentNumber=`$tracking_number`"}
    {$carrier_name = __("dhl")}
{elseif $carrier == "swisspost"}
    {$url = "http://www.post.ch/swisspost-tracking?formattedParcelCodes=`$tracking_number`"}
    {$carrier_name = __("chp")}
{elseif $carrier == "temando"}
    {$url = "https://temando.com/education-centre/support/track-your-item?token=`$tracking_number`"}
    {$carrier_name = __("temando")}
{else}
    {$url = ""}
    {$carrier_name = $carrier}
{/if}

{/hook}

{capture name="carrier_name"}
{$carrier_name}
{/capture}

{capture name="carrier_url"}
{$url nofilter}
{/capture}