/*
 * jQuery history plugin
 *
 * The MIT License
 *
 * Copyright (c) 2006-2009 Taku Sano (Mikage Sawatari)
 * Copyright (c) 2010 Takayuki Miwa
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

(function(_, $) {
    var locationWrapper = {
        put: function(hash, win) {
            (win || window).location.hash = this.encoder(hash);
        },
        get: function(win) {
            var hash = ((win || window).location.hash).replace(/^#/, '');
            try {
                return $.browser.mozilla ? hash : decodeURIComponent(hash);
            }
            catch (error) {
                return hash;
            }
        },
        encoder: encodeURIComponent
    };

    var historyState = {
        storage: null,
        first: '',
        put: function(hash, params) {
            if (!this.storage) {
                this.storage = {};
                this.first = hash;
            }

            this.storage[hash] = params;
        },
        get: function(hash) {
            if (hash in this.storage) {
                return this.storage[hash];
            }

            return {};
        }
    };

    function initObjects(options) {
        options = $.extend({
                unescape: false
            }, options || {});

        locationWrapper.encoder = encoder(options.unescape);

        function encoder(unescape_) {
            if(unescape_ === true) {
                return function(hash){ return hash; };
            }
            if(typeof unescape_ == "string" &&
               (unescape_ = partialDecoder(unescape_.split(""))) ||
                typeof unescape_ == "function") {
                return function(hash) { return unescape_(encodeURIComponent(hash)); };
            }
            return encodeURIComponent;
        }

        function partialDecoder(chars) {
            var re = new RegExp($.map(chars, encodeURIComponent).join("|"), "ig");
            return function(enc) { return enc.replace(re, decodeURIComponent); };
        }
    }

    var implementations = {};

    implementations.base = {
        callback: undefined,
        type: undefined,

        check: function() {},
        load:  function(hash) {},
        init:  function(callback, options) {
            initObjects(options);
            self.callback = callback;
            self._options = options;
            self._init();
        },

        _init: function() {},
        _options: {}
    };

    implementations.hashchangeEvent = {
        _skip: false,
        _init: function() {
            $(window).bind('hashchange', function() {
                if (self._skip === true) {
                    self._skip = false;
                    return;
                }
                self.check();
            });
        },
        check: function() {
            var hash = locationWrapper.get() ? locationWrapper.get() : historyState.first;
            self.callback(hash, historyState.get(hash));
        },
        load: function(hash, params) {
            historyState.put(hash, params);
            self._skip = true;
            locationWrapper.put(hash);
        },
        reload: function(hash, params) {
            historyState.put(hash, params);
        }
    };

    implementations.HTML5 = {
        _init: function() {
            $(window).bind('popstate', self.check);
        },
        check: function(evt) {
            var state = evt.originalEvent.state;
            self.callback(state ? '#!/' + document.location : '', state);
        },
        load: function(hash, params) {
            window.history.pushState(params, null, _.current_location + '/' + hash.replace(/^\!\//, ''));
        },
        reload: function(hash, params) {
            window.history.replaceState(params, null, _.current_location + '/' + hash.replace(/^\!\//, ''));
        }
    };

    var self = $.extend({}, implementations.base);

    if (!_.embedded && "pushState" in window.history) {
        self.type = 'HTML5';
    } else if("onhashchange" in window) {
        self.type = 'hashchangeEvent';
    }

    if (self.type) {
        $.extend(self, implementations[self.type]);
        $.history = self;
    }
})(Tygh, Tygh.$);