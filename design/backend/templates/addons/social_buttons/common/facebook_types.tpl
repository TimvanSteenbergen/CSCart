{include file="common/subheader.tpl" title=__("facebook") target="#facebook_settings"}
<div id="facebook_settings" class="in collapse">
    <div class="control-group cm-no-hide-input">
        <label for="facebook_obj_type" class="control-label">{__("facebook_obj_type")}:</label>
        <div class="controls">
            <select name="{$object_type}[facebook_obj_type]" id="facebook_obj_type">
                <optgroup label="{__("fb_activities")}">
                    <option value="activity" {if $object_data.facebook_obj_type == "activity"}selected="selected"{/if}>activity</option>
                    <option value="sport" {if $object_data.facebook_obj_type == "sport"}selected="selected"{/if}>sport</option>
                </optgroup>
                <optgroup label="{__("fb_businesses")}">
                    <option value="bar" {if $object_data.facebook_obj_type == "bar"}selected="selected"{/if}>bar</option>
                    <option value="company" {if $object_data.facebook_obj_type == "company"}selected="selected"{/if}>company</option>
                    <option value="cafe" {if $object_data.facebook_obj_type == "cafe"}selected="selected"{/if}>cafe</option>
                    <option value="hotel" {if $object_data.facebook_obj_type == "hotel"}selected="selected"{/if}>hotel</option>
                    <option value="restaurant" {if $object_data.facebook_obj_type == "restaurant"}selected="selected"{/if}>restaurant</option>
                </optgroup>
                <optgroup label="{__("fb_groups")}">
                    <option value="cause" {if $object_data.facebook_obj_type == "cause"}selected="selected"{/if}>cause</option>
                    <option value="sports_league" {if $object_data.facebook_obj_type == "sports_league"}selected="selected"{/if}>sports_league</option>
                    <option value="sports_team" {if $object_data.facebook_obj_type == "sports_team"}selected="selected"{/if}>sports_team</option>
                </optgroup>
                <optgroup label="{__("fb_organizations")}">
                    <option value="band" {if $object_data.facebook_obj_type == "band"}selected="selected"{/if}>band</option>
                    <option value="government" {if $object_data.facebook_obj_type == "government"}selected="selected"{/if}>government</option>
                    <option value="non_profit" {if $object_data.facebook_obj_type == "non_profit"}selected="selected"{/if}>non_profit</option>
                    <option value="school" {if $object_data.facebook_obj_type == "school"}selected="selected"{/if}>school</option>
                    <option value="university" {if $object_data.facebook_obj_type == "university"}selected="selected"{/if}>university</option>
                </optgroup>
                <optgroup label="{__("fb_people")}">
                    <option value="actor" {if $object_data.facebook_obj_type == "actor"}selected="selected"{/if}>actor</option>
                    <option value="athlete" {if $object_data.facebook_obj_type == "athlete"}selected="selected"{/if}>athlete</option>
                    <option value="author" {if $object_data.facebook_obj_type == "author"}selected="selected"{/if}>author</option>
                    <option value="director" {if $object_data.facebook_obj_type == "director"}selected="selected"{/if}>director</option>
                    <option value="musician" {if $object_data.facebook_obj_type == "musician"}selected="selected"{/if}>musician</option>
                    <option value="politician" {if $object_data.facebook_obj_type == "politician"}selected="selected"{/if}>politician</option>
                    <option value="public_figure" {if $object_data.facebook_obj_type == "public_figure"}selected="selected"{/if}>public_figure</option>
                </optgroup>
                <optgroup label="{__("fb_places")}">
                    <option value="city" {if $object_data.facebook_obj_type == "city"}selected="selected"{/if}>city</option>
                    <option value="country" {if $object_data.facebook_obj_type == "country"}selected="selected"{/if}>country</option>
                    <option value="landmark" {if $object_data.facebook_obj_type == "landmark"}selected="selected"{/if}>landmark</option>
                    <option value="state_province" {if $object_data.facebook_obj_type == "state_province"}selected="selected"{/if}>state_province</option>
                </optgroup>
                <optgroup label="{__("fb_products_entertainment")}">
                    <option value="album" {if $object_data.facebook_obj_type == "album"}selected="selected"{/if}>album</option>
                    <option value="book" {if $object_data.facebook_obj_type == "book"}selected="selected"{/if}>book</option>
                    <option value="drink" {if $object_data.facebook_obj_type == "drink"}selected="selected"{/if}>drink</option>
                    <option value="food" {if $object_data.facebook_obj_type == "food"}selected="selected"{/if}>food</option>
                    <option value="game" {if $object_data.facebook_obj_type == "game"}selected="selected"{/if}>game</option>
                    <option value="product" {if $object_data.facebook_obj_type == "product"}selected="selected"{/if}>product</option>
                    <option value="song" {if $object_data.facebook_obj_type == "song"}selected="selected"{/if}>song</option>
                    <option value="movie" {if $object_data.facebook_obj_type == "movie"}selected="selected"{/if}>movie</option>
                    <option value="tv_show" {if $object_data.facebook_obj_type == "tv_show"}selected="selected"{/if}>tv_show</option>
                </optgroup>
                <optgroup label="{__("fb_websites")}">
                    <option value="blog" {if $object_data.facebook_obj_type == "blog"}selected="selected"{/if}>blog</option>
                    <option value="website" {if $object_data.facebook_obj_type == "website"}selected="selected"{/if}>website</option>
                    <option value="article" {if $object_data.facebook_obj_type == "article"}selected="selected"{/if}>article</option>
                </optgroup>
            </select>
        </div>
    </div>
</div>
