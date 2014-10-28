{$id = $id|default:"call_request_{$obj_prefix}{$product.product_id}"}

{capture name="call_request_popup"}
    {include file="addons/call_requests/views/call_requests/components/call_requests_content.tpl" product=$product}
{/capture}

{include file="common/popupbox.tpl"
    content=$smarty.capture.call_request_popup
    link_text=$link_text
    text=$text|default:$link_text
    id=$id
    link_meta=$link_meta
}