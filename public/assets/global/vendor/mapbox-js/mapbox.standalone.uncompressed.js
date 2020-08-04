(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
function corslite(url, callback, cors) {
    var sent = false;

    if (typeof window.XMLHttpRequest === 'undefined') {
        return callback(Error('Browser not supported'));
    }

    if (typeof cors === 'undefined') {
        var m = url.match(/^\s*https?:\/\/[^\/]*/);
        cors = m && (m[0] !== location.protocol + '//' + location.domain +
                (location.port ? ':' + location.port : ''));
    }

    var x = new window.XMLHttpRequest();

    function isSuccessful(status) {
        return status >= 200 && status < 300 || status === 304;
    }

    if (cors && !('withCredentials' in x)) {
        // IE8-9
        x = new window.XDomainRequest();

        // Ensure callback is never called synchronously, i.e., before
        // x.send() returns (this has been observed in the wild).
        // See https://github.com/mapbox/mapbox.js/issues/472
        var original = callback;
        callback = function() {
            if (sent) {
                original.apply(this, arguments);
            } else {
                var that = this, args = arguments;
                setTimeout(function() {
                    original.apply(that, args);
                }, 0);
            }
        }
    }

    function loaded() {
        if (
            // XDomainRequest
            x.status === undefined ||
            // modern browsers
            isSuccessful(x.status)) callback.call(x, null, x);
        else callback.call(x, x, null);
    }

    // Both `onreadystatechange` and `onload` can fire. `onreadystatechange`
    // has [been supported for longer](http://stackoverflow.com/a/9181508/229001).
    if ('onload' in x) {
        x.onload = loaded;
    } else {
        x.onreadystatechange = function readystate() {
            if (x.readyState === 4) {
                loaded();
            }
        };
    }

    // Call the callback with the XMLHttpRequest object as an error and prevent
    // it from ever being called again by reassigning it to `noop`
    x.onerror = function error(evt) {
        // XDomainRequest provides no evt parameter
        callback.call(this, evt || true, null);
        callback = function() { };
    };

    // IE9 must have onprogress be set to a unique function.
    x.onprogress = function() { };

    x.ontimeout = function(evt) {
        callback.call(this, evt, null);
        callback = function() { };
    };

    x.onabort = function(evt) {
        callback.call(this, evt, null);
        callback = function() { };
    };

    // GET is the only supported HTTP Verb by XDomainRequest and is the
    // only one supported here.
    x.open('GET', url, true);

    // Send the request. Sending data is not supported.
    x.send(null);
    sent = true;

    return x;
}

if (typeof module !== 'undefined') module.exports = corslite;

},{}],2:[function(require,module,exports){
module.exports = Array.isArray || function (arr) {
  return Object.prototype.toString.call(arr) == '[object Array]';
};

},{}],3:[function(require,module,exports){
/*!
 * mustache.js - Logic-less {{mustache}} templates with JavaScript
 * http://github.com/janl/mustache.js
 */

/*global define: false Mustache: true*/

(function defineMustache (global, factory) {
  if (typeof exports === 'object' && exports && typeof exports.nodeName !== 'string') {
    factory(exports); // CommonJS
  } else if (typeof define === 'function' && define.amd) {
    define(['exports'], factory); // AMD
  } else {
    global.Mustache = {};
    factory(global.Mustache); // script, wsh, asp
  }
}(this, function mustacheFactory (mustache) {

  var objectToString = Object.prototype.toString;
  var isArray = Array.isArray || function isArrayPolyfill (object) {
    return objectToString.call(object) === '[object Array]';
  };

  function isFunction (object) {
    return typeof object === 'function';
  }

  /**
   * More correct typeof string handling array
   * which normally returns typeof 'object'
   */
  function typeStr (obj) {
    return isArray(obj) ? 'array' : typeof obj;
  }

  function escapeRegExp (string) {
    return string.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
  }

  /**
   * Null safe way of checking whether or not an object,
   * including its prototype, has a given property
   */
  function hasProperty (obj, propName) {
    return obj != null && typeof obj === 'object' && (propName in obj);
  }

  // Workaround for https://issues.apache.org/jira/browse/COUCHDB-577
  // See https://github.com/janl/mustache.js/issues/189
  var regExpTest = RegExp.prototype.test;
  function testRegExp (re, string) {
    return regExpTest.call(re, string);
  }

  var nonSpaceRe = /\S/;
  function isWhitespace (string) {
    return !testRegExp(nonSpaceRe, string);
  }

  var entityMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
    '/': '&#x2F;',
    '`': '&#x60;',
    '=': '&#x3D;'
  };

  function escapeHtml (string) {
    return String(string).replace(/[&<>"'`=\/]/g, function fromEntityMap (s) {
      return entityMap[s];
    });
  }

  var whiteRe = /\s*/;
  var spaceRe = /\s+/;
  var equalsRe = /\s*=/;
  var curlyRe = /\s*\}/;
  var tagRe = /#|\^|\/|>|\{|&|=|!/;

  /**
   * Breaks up the given `template` string into a tree of tokens. If the `tags`
   * argument is given here it must be an array with two string values: the
   * opening and closing tags used in the template (e.g. [ "<%", "%>" ]). Of
   * course, the default is to use mustaches (i.e. mustache.tags).
   *
   * A token is an array with at least 4 elements. The first element is the
   * mustache symbol that was used inside the tag, e.g. "#" or "&". If the tag
   * did not contain a symbol (i.e. {{myValue}}) this element is "name". For
   * all text that appears outside a symbol this element is "text".
   *
   * The second element of a token is its "value". For mustache tags this is
   * whatever else was inside the tag besides the opening symbol. For text tokens
   * this is the text itself.
   *
   * The third and fourth elements of the token are the start and end indices,
   * respectively, of the token in the original template.
   *
   * Tokens that are the root node of a subtree contain two more elements: 1) an
   * array of tokens in the subtree and 2) the index in the original template at
   * which the closing tag for that section begins.
   */
  function parseTemplate (template, tags) {
    if (!template)
      return [];

    var sections = [];     // Stack to hold section tokens
    var tokens = [];       // Buffer to hold the tokens
    var spaces = [];       // Indices of whitespace tokens on the current line
    var hasTag = false;    // Is there a {{tag}} on the current line?
    var nonSpace = false;  // Is there a non-space char on the current line?

    // Strips all whitespace tokens array for the current line
    // if there was a {{#tag}} on it and otherwise only space.
    function stripSpace () {
      if (hasTag && !nonSpace) {
        while (spaces.length)
          delete tokens[spaces.pop()];
      } else {
        spaces = [];
      }

      hasTag = false;
      nonSpace = false;
    }

    var openingTagRe, closingTagRe, closingCurlyRe;
    function compileTags (tagsToCompile) {
      if (typeof tagsToCompile === 'string')
        tagsToCompile = tagsToCompile.split(spaceRe, 2);

      if (!isArray(tagsToCompile) || tagsToCompile.length !== 2)
        throw new Error('Invalid tags: ' + tagsToCompile);

      openingTagRe = new RegExp(escapeRegExp(tagsToCompile[0]) + '\\s*');
      closingTagRe = new RegExp('\\s*' + escapeRegExp(tagsToCompile[1]));
      closingCurlyRe = new RegExp('\\s*' + escapeRegExp('}' + tagsToCompile[1]));
    }

    compileTags(tags || mustache.tags);

    var scanner = new Scanner(template);

    var start, type, value, chr, token, openSection;
    while (!scanner.eos()) {
      start = scanner.pos;

      // Match any text between tags.
      value = scanner.scanUntil(openingTagRe);

      if (value) {
        for (var i = 0, valueLength = value.length; i < valueLength; ++i) {
          chr = value.charAt(i);

          if (isWhitespace(chr)) {
            spaces.push(tokens.length);
          } else {
            nonSpace = true;
          }

          tokens.push([ 'text', chr, start, start + 1 ]);
          start += 1;

          // Check for whitespace on the current line.
          if (chr === '\n')
            stripSpace();
        }
      }

      // Match the opening tag.
      if (!scanner.scan(openingTagRe))
        break;

      hasTag = true;

      // Get the tag type.
      type = scanner.scan(tagRe) || 'name';
      scanner.scan(whiteRe);

      // Get the tag value.
      if (type === '=') {
        value = scanner.scanUntil(equalsRe);
        scanner.scan(equalsRe);
        scanner.scanUntil(closingTagRe);
      } else if (type === '{') {
        value = scanner.scanUntil(closingCurlyRe);
        scanner.scan(curlyRe);
        scanner.scanUntil(closingTagRe);
        type = '&';
      } else {
        value = scanner.scanUntil(closingTagRe);
      }

      // Match the closing tag.
      if (!scanner.scan(closingTagRe))
        throw new Error('Unclosed tag at ' + scanner.pos);

      token = [ type, value, start, scanner.pos ];
      tokens.push(token);

      if (type === '#' || type === '^') {
        sections.push(token);
      } else if (type === '/') {
        // Check section nesting.
        openSection = sections.pop();

        if (!openSection)
          throw new Error('Unopened section "' + value + '" at ' + start);

        if (openSection[1] !== value)
          throw new Error('Unclosed section "' + openSection[1] + '" at ' + start);
      } else if (type === 'name' || type === '{' || type === '&') {
        nonSpace = true;
      } else if (type === '=') {
        // Set the tags for the next time around.
        compileTags(value);
      }
    }

    // Make sure there are no open sections when we're done.
    openSection = sections.pop();

    if (openSection)
      throw new Error('Unclosed section "' + openSection[1] + '" at ' + scanner.pos);

    return nestTokens(squashTokens(tokens));
  }

  /**
   * Combines the values of consecutive text tokens in the given `tokens` array
   * to a single token.
   */
  function squashTokens (tokens) {
    var squashedTokens = [];

    var token, lastToken;
    for (var i = 0, numTokens = tokens.length; i < numTokens; ++i) {
      token = tokens[i];

      if (token) {
        if (token[0] === 'text' && lastToken && lastToken[0] === 'text') {
          lastToken[1] += token[1];
          lastToken[3] = token[3];
        } else {
          squashedTokens.push(token);
          lastToken = token;
        }
      }
    }

    return squashedTokens;
  }

  /**
   * Forms the given array of `tokens` into a nested tree structure where
   * tokens that represent a section have two additional items: 1) an array of
   * all tokens that appear in that section and 2) the index in the original
   * template that represents the end of that section.
   */
  function nestTokens (tokens) {
    var nestedTokens = [];
    var collector = nestedTokens;
    var sections = [];

    var token, section;
    for (var i = 0, numTokens = tokens.length; i < numTokens; ++i) {
      token = tokens[i];

      switch (token[0]) {
        case '#':
        case '^':
          collector.push(token);
          sections.push(token);
          collector = token[4] = [];
          break;
        case '/':
          section = sections.pop();
          section[5] = token[2];
          collector = sections.length > 0 ? sections[sections.length - 1][4] : nestedTokens;
          break;
        default:
          collector.push(token);
      }
    }

    return nestedTokens;
  }

  /**
   * A simple string scanner that is used by the template parser to find
   * tokens in template strings.
   */
  function Scanner (string) {
    this.string = string;
    this.tail = string;
    this.pos = 0;
  }

  /**
   * Returns `true` if the tail is empty (end of string).
   */
  Scanner.prototype.eos = function eos () {
    return this.tail === '';
  };

  /**
   * Tries to match the given regular expression at the current position.
   * Returns the matched text if it can match, the empty string otherwise.
   */
  Scanner.prototype.scan = function scan (re) {
    var match = this.tail.match(re);

    if (!match || match.index !== 0)
      return '';

    var string = match[0];

    this.tail = this.tail.substring(string.length);
    this.pos += string.length;

    return string;
  };

  /**
   * Skips all text until the given regular expression can be matched. Returns
   * the skipped string, which is the entire tail if no match can be made.
   */
  Scanner.prototype.scanUntil = function scanUntil (re) {
    var index = this.tail.search(re), match;

    switch (index) {
      case -1:
        match = this.tail;
        this.tail = '';
        break;
      case 0:
        match = '';
        break;
      default:
        match = this.tail.substring(0, index);
        this.tail = this.tail.substring(index);
    }

    this.pos += match.length;

    return match;
  };

  /**
   * Represents a rendering context by wrapping a view object and
   * maintaining a reference to the parent context.
   */
  function Context (view, parentContext) {
    this.view = view;
    this.cache = { '.': this.view };
    this.parent = parentContext;
  }

  /**
   * Creates a new context using the given view with this context
   * as the parent.
   */
  Context.prototype.push = function push (view) {
    return new Context(view, this);
  };

  /**
   * Returns the value of the given name in this context, traversing
   * up the context hierarchy if the value is absent in this context's view.
   */
  Context.prototype.lookup = function lookup (name) {
    var cache = this.cache;

    var value;
    if (cache.hasOwnProperty(name)) {
      value = cache[name];
    } else {
      var context = this, names, index, lookupHit = false;

      while (context) {
        if (name.indexOf('.') > 0) {
          value = context.view;
          names = name.split('.');
          index = 0;

          /**
           * Using the dot notion path in `name`, we descend through the
           * nested objects.
           *
           * To be certain that the lookup has been successful, we have to
           * check if the last object in the path actually has the property
           * we are looking for. We store the result in `lookupHit`.
           *
           * This is specially necessary for when the value has been set to
           * `undefined` and we want to avoid looking up parent contexts.
           **/
          while (value != null && index < names.length) {
            if (index === names.length - 1)
              lookupHit = hasProperty(value, names[index]);

            value = value[names[index++]];
          }
        } else {
          value = context.view[name];
          lookupHit = hasProperty(context.view, name);
        }

        if (lookupHit)
          break;

        context = context.parent;
      }

      cache[name] = value;
    }

    if (isFunction(value))
      value = value.call(this.view);

    return value;
  };

  /**
   * A Writer knows how to take a stream of tokens and render them to a
   * string, given a context. It also maintains a cache of templates to
   * avoid the need to parse the same template twice.
   */
  function Writer () {
    this.cache = {};
  }

  /**
   * Clears all cached templates in this writer.
   */
  Writer.prototype.clearCache = function clearCache () {
    this.cache = {};
  };

  /**
   * Parses and caches the given `template` and returns the array of tokens
   * that is generated from the parse.
   */
  Writer.prototype.parse = function parse (template, tags) {
    var cache = this.cache;
    var tokens = cache[template];

    if (tokens == null)
      tokens = cache[template] = parseTemplate(template, tags);

    return tokens;
  };

  /**
   * High-level method that is used to render the given `template` with
   * the given `view`.
   *
   * The optional `partials` argument may be an object that contains the
   * names and templates of partials that are used in the template. It may
   * also be a function that is used to load partial templates on the fly
   * that takes a single argument: the name of the partial.
   */
  Writer.prototype.render = function render (template, view, partials) {
    var tokens = this.parse(template);
    var context = (view instanceof Context) ? view : new Context(view);
    return this.renderTokens(tokens, context, partials, template);
  };

  /**
   * Low-level method that renders the given array of `tokens` using
   * the given `context` and `partials`.
   *
   * Note: The `originalTemplate` is only ever used to extract the portion
   * of the original template that was contained in a higher-order section.
   * If the template doesn't use higher-order sections, this argument may
   * be omitted.
   */
  Writer.prototype.renderTokens = function renderTokens (tokens, context, partials, originalTemplate) {
    var buffer = '';

    var token, symbol, value;
    for (var i = 0, numTokens = tokens.length; i < numTokens; ++i) {
      value = undefined;
      token = tokens[i];
      symbol = token[0];

      if (symbol === '#') value = this.renderSection(token, context, partials, originalTemplate);
      else if (symbol === '^') value = this.renderInverted(token, context, partials, originalTemplate);
      else if (symbol === '>') value = this.renderPartial(token, context, partials, originalTemplate);
      else if (symbol === '&') value = this.unescapedValue(token, context);
      else if (symbol === 'name') value = this.escapedValue(token, context);
      else if (symbol === 'text') value = this.rawValue(token);

      if (value !== undefined)
        buffer += value;
    }

    return buffer;
  };

  Writer.prototype.renderSection = function renderSection (token, context, partials, originalTemplate) {
    var self = this;
    var buffer = '';
    var value = context.lookup(token[1]);

    // This function is used to render an arbitrary template
    // in the current context by higher-order sections.
    function subRender (template) {
      return self.render(template, context, partials);
    }

    if (!value) return;

    if (isArray(value)) {
      for (var j = 0, valueLength = value.length; j < valueLength; ++j) {
        buffer += this.renderTokens(token[4], context.push(value[j]), partials, originalTemplate);
      }
    } else if (typeof value === 'object' || typeof value === 'string' || typeof value === 'number') {
      buffer += this.renderTokens(token[4], context.push(value), partials, originalTemplate);
    } else if (isFunction(value)) {
      if (typeof originalTemplate !== 'string')
        throw new Error('Cannot use higher-order sections without the original template');

      // Extract the portion of the original template that the section contains.
      value = value.call(context.view, originalTemplate.slice(token[3], token[5]), subRender);

      if (value != null)
        buffer += value;
    } else {
      buffer += this.renderTokens(token[4], context, partials, originalTemplate);
    }
    return buffer;
  };

  Writer.prototype.renderInverted = function renderInverted (token, context, partials, originalTemplate) {
    var value = context.lookup(token[1]);

    // Use JavaScript's definition of falsy. Include empty arrays.
    // See https://github.com/janl/mustache.js/issues/186
    if (!value || (isArray(value) && value.length === 0))
      return this.renderTokens(token[4], context, partials, originalTemplate);
  };

  Writer.prototype.renderPartial = function renderPartial (token, context, partials) {
    if (!partials) return;

    var value = isFunction(partials) ? partials(token[1]) : partials[token[1]];
    if (value != null)
      return this.renderTokens(this.parse(value), context, partials, value);
  };

  Writer.prototype.unescapedValue = function unescapedValue (token, context) {
    var value = context.lookup(token[1]);
    if (value != null)
      return value;
  };

  Writer.prototype.escapedValue = function escapedValue (token, context) {
    var value = context.lookup(token[1]);
    if (value != null)
      return mustache.escape(value);
  };

  Writer.prototype.rawValue = function rawValue (token) {
    return token[1];
  };

  mustache.name = 'mustache.js';
  mustache.version = '2.2.1';
  mustache.tags = [ '{{', '}}' ];

  // All high-level mustache.* functions use this writer.
  var defaultWriter = new Writer();

  /**
   * Clears all cached templates in the default writer.
   */
  mustache.clearCache = function clearCache () {
    return defaultWriter.clearCache();
  };

  /**
   * Parses and caches the given template in the default writer and returns the
   * array of tokens it contains. Doing this ahead of time avoids the need to
   * parse templates on the fly as they are rendered.
   */
  mustache.parse = function parse (template, tags) {
    return defaultWriter.parse(template, tags);
  };

  /**
   * Renders the `template` with the given `view` and `partials` using the
   * default writer.
   */
  mustache.render = function render (template, view, partials) {
    if (typeof template !== 'string') {
      throw new TypeError('Invalid template! Template should be a "string" ' +
                          'but "' + typeStr(template) + '" was given as the first ' +
                          'argument for mustache#render(template, view, partials)');
    }

    return defaultWriter.render(template, view, partials);
  };

  // This is here for backwards compatibility with 0.4.x.,
  /*eslint-disable */ // eslint wants camel cased function name
  mustache.to_html = function to_html (template, view, partials, send) {
    /*eslint-enable*/

    var result = mustache.render(template, view, partials);

    if (isFunction(send)) {
      send(result);
    } else {
      return result;
    }
  };

  // Export the escaping function so that the user may override it.
  // See https://github.com/janl/mustache.js/issues/244
  mustache.escape = escapeHtml;

  // Export these mainly for testing, but also for advanced usage.
  mustache.Scanner = Scanner;
  mustache.Context = Context;
  mustache.Writer = Writer;

}));

},{}],4:[function(require,module,exports){
var html_sanitize = require('./sanitizer-bundle.js');

module.exports = function(_) {
    if (!_) return '';
    return html_sanitize(_, cleanUrl, cleanId);
};

// https://bugzilla.mozilla.org/show_bug.cgi?id=255107
function cleanUrl(url) {
    'use strict';
    if (/^https?/.test(url.getScheme())) return url.toString();
    if (/^mailto?/.test(url.getScheme())) return url.toString();
    if ('data' == url.getScheme() && /^image/.test(url.getPath())) {
        return url.toString();
    }
}

function cleanId(id) { return id; }

},{"./sanitizer-bundle.js":5}],5:[function(require,module,exports){

// Copyright (C) 2010 Google Inc.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

/**
 * @fileoverview
 * Implements RFC 3986 for parsing/formatting URIs.
 *
 * @author mikesamuel@gmail.com
 * \@provides URI
 * \@overrides window
 */

var URI = (function () {

/**
 * creates a uri from the string form.  The parser is relaxed, so special
 * characters that aren't escaped but don't cause ambiguities will not cause
 * parse failures.
 *
 * @return {URI|null}
 */
function parse(uriStr) {
  var m = ('' + uriStr).match(URI_RE_);
  if (!m) { return null; }
  return new URI(
      nullIfAbsent(m[1]),
      nullIfAbsent(m[2]),
      nullIfAbsent(m[3]),
      nullIfAbsent(m[4]),
      nullIfAbsent(m[5]),
      nullIfAbsent(m[6]),
      nullIfAbsent(m[7]));
}


/**
 * creates a uri from the given parts.
 *
 * @param scheme {string} an unencoded scheme such as "http" or null
 * @param credentials {string} unencoded user credentials or null
 * @param domain {string} an unencoded domain name or null
 * @param port {number} a port number in [1, 32768].
 *    -1 indicates no port, as does null.
 * @param path {string} an unencoded path
 * @param query {Array.<string>|string|null} a list of unencoded cgi
 *   parameters where even values are keys and odds the corresponding values
 *   or an unencoded query.
 * @param fragment {string} an unencoded fragment without the "#" or null.
 * @return {URI}
 */
function create(scheme, credentials, domain, port, path, query, fragment) {
  var uri = new URI(
      encodeIfExists2(scheme, URI_DISALLOWED_IN_SCHEME_OR_CREDENTIALS_),
      encodeIfExists2(
          credentials, URI_DISALLOWED_IN_SCHEME_OR_CREDENTIALS_),
      encodeIfExists(domain),
      port > 0 ? port.toString() : null,
      encodeIfExists2(path, URI_DISALLOWED_IN_PATH_),
      null,
      encodeIfExists(fragment));
  if (query) {
    if ('string' === typeof query) {
      uri.setRawQuery(query.replace(/[^?&=0-9A-Za-z_\-~.%]/g, encodeOne));
    } else {
      uri.setAllParameters(query);
    }
  }
  return uri;
}
function encodeIfExists(unescapedPart) {
  if ('string' == typeof unescapedPart) {
    return encodeURIComponent(unescapedPart);
  }
  return null;
};
/**
 * if unescapedPart is non null, then escapes any characters in it that aren't
 * valid characters in a url and also escapes any special characters that
 * appear in extra.
 *
 * @param unescapedPart {string}
 * @param extra {RegExp} a character set of characters in [\01-\177].
 * @return {string|null} null iff unescapedPart == null.
 */
function encodeIfExists2(unescapedPart, extra) {
  if ('string' == typeof unescapedPart) {
    return encodeURI(unescapedPart).replace(extra, encodeOne);
  }
  return null;
};
/** converts a character in [\01-\177] to its url encoded equivalent. */
function encodeOne(ch) {
  var n = ch.charCodeAt(0);
  return '%' + '0123456789ABCDEF'.charAt((n >> 4) & 0xf) +
      '0123456789ABCDEF'.charAt(n & 0xf);
}

/**
 * {@updoc
 *  $ normPath('foo/./bar')
 *  # 'foo/bar'
 *  $ normPath('./foo')
 *  # 'foo'
 *  $ normPath('foo/.')
 *  # 'foo'
 *  $ normPath('foo//bar')
 *  # 'foo/bar'
 * }
 */
function normPath(path) {
  return path.replace(/(^|\/)\.(?:\/|$)/g, '$1').replace(/\/{2,}/g, '/');
}

var PARENT_DIRECTORY_HANDLER = new RegExp(
    ''
    // A path break
    + '(/|^)'
    // followed by a non .. path element
    // (cannot be . because normPath is used prior to this RegExp)
    + '(?:[^./][^/]*|\\.{2,}(?:[^./][^/]*)|\\.{3,}[^/]*)'
    // followed by .. followed by a path break.
    + '/\\.\\.(?:/|$)');

var PARENT_DIRECTORY_HANDLER_RE = new RegExp(PARENT_DIRECTORY_HANDLER);

var EXTRA_PARENT_PATHS_RE = /^(?:\.\.\/)*(?:\.\.$)?/;

/**
 * Normalizes its input path and collapses all . and .. sequences except for
 * .. sequences that would take it above the root of the current parent
 * directory.
 * {@updoc
 *  $ collapse_dots('foo/../bar')
 *  # 'bar'
 *  $ collapse_dots('foo/./bar')
 *  # 'foo/bar'
 *  $ collapse_dots('foo/../bar/./../../baz')
 *  # 'baz'
 *  $ collapse_dots('../foo')
 *  # '../foo'
 *  $ collapse_dots('../foo').replace(EXTRA_PARENT_PATHS_RE, '')
 *  # 'foo'
 * }
 */
function collapse_dots(path) {
  if (path === null) { return null; }
  var p = normPath(path);
  // Only /../ left to flatten
  var r = PARENT_DIRECTORY_HANDLER_RE;
  // We replace with $1 which matches a / before the .. because this
  // guarantees that:
  // (1) we have at most 1 / between the adjacent place,
  // (2) always have a slash if there is a preceding path section, and
  // (3) we never turn a relative path into an absolute path.
  for (var q; (q = p.replace(r, '$1')) != p; p = q) {};
  return p;
}

/**
 * resolves a relative url string to a base uri.
 * @return {URI}
 */
function resolve(baseUri, relativeUri) {
  // there are several kinds of relative urls:
  // 1. //foo - replaces everything from the domain on.  foo is a domain name
  // 2. foo - replaces the last part of the path, the whole query and fragment
  // 3. /foo - replaces the the path, the query and fragment
  // 4. ?foo - replace the query and fragment
  // 5. #foo - replace the fragment only

  var absoluteUri = baseUri.clone();
  // we satisfy these conditions by looking for the first part of relativeUri
  // that is not blank and applying defaults to the rest

  var overridden = relativeUri.hasScheme();

  if (overridden) {
    absoluteUri.setRawScheme(relativeUri.getRawScheme());
  } else {
    overridden = relativeUri.hasCredentials();
  }

  if (overridden) {
    absoluteUri.setRawCredentials(relativeUri.getRawCredentials());
  } else {
    overridden = relativeUri.hasDomain();
  }

  if (overridden) {
    absoluteUri.setRawDomain(relativeUri.getRawDomain());
  } else {
    overridden = relativeUri.hasPort();
  }

  var rawPath = relativeUri.getRawPath();
  var simplifiedPath = collapse_dots(rawPath);
  if (overridden) {
    absoluteUri.setPort(relativeUri.getPort());
    simplifiedPath = simplifiedPath
        && simplifiedPath.replace(EXTRA_PARENT_PATHS_RE, '');
  } else {
    overridden = !!rawPath;
    if (overridden) {
      // resolve path properly
      if (simplifiedPath.charCodeAt(0) !== 0x2f /* / */) {  // path is relative
        var absRawPath = collapse_dots(absoluteUri.getRawPath() || '')
            .replace(EXTRA_PARENT_PATHS_RE, '');
        var slash = absRawPath.lastIndexOf('/') + 1;
        simplifiedPath = collapse_dots(
            (slash ? absRawPath.substring(0, slash) : '')
            + collapse_dots(rawPath))
            .replace(EXTRA_PARENT_PATHS_RE, '');
      }
    } else {
      simplifiedPath = simplifiedPath
          && simplifiedPath.replace(EXTRA_PARENT_PATHS_RE, '');
      if (simplifiedPath !== rawPath) {
        absoluteUri.setRawPath(simplifiedPath);
      }
    }
  }

  if (overridden) {
    absoluteUri.setRawPath(simplifiedPath);
  } else {
    overridden = relativeUri.hasQuery();
  }

  if (overridden) {
    absoluteUri.setRawQuery(relativeUri.getRawQuery());
  } else {
    overridden = relativeUri.hasFragment();
  }

  if (overridden) {
    absoluteUri.setRawFragment(relativeUri.getRawFragment());
  }

  return absoluteUri;
}

/**
 * a mutable URI.
 *
 * This class contains setters and getters for the parts of the URI.
 * The <tt>getXYZ</tt>/<tt>setXYZ</tt> methods return the decoded part -- so
 * <code>uri.parse('/foo%20bar').getPath()</code> will return the decoded path,
 * <tt>/foo bar</tt>.
 *
 * <p>The raw versions of fields are available too.
 * <code>uri.parse('/foo%20bar').getRawPath()</code> will return the raw path,
 * <tt>/foo%20bar</tt>.  Use the raw setters with care, since
 * <code>URI::toString</code> is not guaranteed to return a valid url if a
 * raw setter was used.
 *
 * <p>All setters return <tt>this</tt> and so may be chained, a la
 * <code>uri.parse('/foo').setFragment('part').toString()</code>.
 *
 * <p>You should not use this constructor directly -- please prefer the factory
 * functions {@link uri.parse}, {@link uri.create}, {@link uri.resolve}
 * instead.</p>
 *
 * <p>The parameters are all raw (assumed to be properly escaped) parts, and
 * any (but not all) may be null.  Undefined is not allowed.</p>
 *
 * @constructor
 */
function URI(
    rawScheme,
    rawCredentials, rawDomain, port,
    rawPath, rawQuery, rawFragment) {
  this.scheme_ = rawScheme;
  this.credentials_ = rawCredentials;
  this.domain_ = rawDomain;
  this.port_ = port;
  this.path_ = rawPath;
  this.query_ = rawQuery;
  this.fragment_ = rawFragment;
  /**
   * @type {Array|null}
   */
  this.paramCache_ = null;
}

/** returns the string form of the url. */
URI.prototype.toString = function () {
  var out = [];
  if (null !== this.scheme_) { out.push(this.scheme_, ':'); }
  if (null !== this.domain_) {
    out.push('//');
    if (null !== this.credentials_) { out.push(this.credentials_, '@'); }
    out.push(this.domain_);
    if (null !== this.port_) { out.push(':', this.port_.toString()); }
  }
  if (null !== this.path_) { out.push(this.path_); }
  if (null !== this.query_) { out.push('?', this.query_); }
  if (null !== this.fragment_) { out.push('#', this.fragment_); }
  return out.join('');
};

URI.prototype.clone = function () {
  return new URI(this.scheme_, this.credentials_, this.domain_, this.port_,
                 this.path_, this.query_, this.fragment_);
};

URI.prototype.getScheme = function () {
  // HTML5 spec does not require the scheme to be lowercased but
  // all common browsers except Safari lowercase the scheme.
  return this.scheme_ && decodeURIComponent(this.scheme_).toLowerCase();
};
URI.prototype.getRawScheme = function () {
  return this.scheme_;
};
URI.prototype.setScheme = function (newScheme) {
  this.scheme_ = encodeIfExists2(
      newScheme, URI_DISALLOWED_IN_SCHEME_OR_CREDENTIALS_);
  return this;
};
URI.prototype.setRawScheme = function (newScheme) {
  this.scheme_ = newScheme ? newScheme : null;
  return this;
};
URI.prototype.hasScheme = function () {
  return null !== this.scheme_;
};


URI.prototype.getCredentials = function () {
  return this.credentials_ && decodeURIComponent(this.credentials_);
};
URI.prototype.getRawCredentials = function () {
  return this.credentials_;
};
URI.prototype.setCredentials = function (newCredentials) {
  this.credentials_ = encodeIfExists2(
      newCredentials, URI_DISALLOWED_IN_SCHEME_OR_CREDENTIALS_);

  return this;
};
URI.prototype.setRawCredentials = function (newCredentials) {
  this.credentials_ = newCredentials ? newCredentials : null;
  return this;
};
URI.prototype.hasCredentials = function () {
  return null !== this.credentials_;
};


URI.prototype.getDomain = function () {
  return this.domain_ && decodeURIComponent(this.domain_);
};
URI.prototype.getRawDomain = function () {
  return this.domain_;
};
URI.prototype.setDomain = function (newDomain) {
  return this.setRawDomain(newDomain && encodeURIComponent(newDomain));
};
URI.prototype.setRawDomain = function (newDomain) {
  this.domain_ = newDomain ? newDomain : null;
  // Maintain the invariant that paths must start with a slash when the URI
  // is not path-relative.
  return this.setRawPath(this.path_);
};
URI.prototype.hasDomain = function () {
  return null !== this.domain_;
};


URI.prototype.getPort = function () {
  return this.port_ && decodeURIComponent(this.port_);
};
URI.prototype.setPort = function (newPort) {
  if (newPort) {
    newPort = Number(newPort);
    if (newPort !== (newPort & 0xffff)) {
      throw new Error('Bad port number ' + newPort);
    }
    this.port_ = '' + newPort;
  } else {
    this.port_ = null;
  }
  return this;
};
URI.prototype.hasPort = function () {
  return null !== this.port_;
};


URI.prototype.getPath = function () {
  return this.path_ && decodeURIComponent(this.path_);
};
URI.prototype.getRawPath = function () {
  return this.path_;
};
URI.prototype.setPath = function (newPath) {
  return this.setRawPath(encodeIfExists2(newPath, URI_DISALLOWED_IN_PATH_));
};
URI.prototype.setRawPath = function (newPath) {
  if (newPath) {
    newPath = String(newPath);
    this.path_ = 
      // Paths must start with '/' unless this is a path-relative URL.
      (!this.domain_ || /^\//.test(newPath)) ? newPath : '/' + newPath;
  } else {
    this.path_ = null;
  }
  return this;
};
URI.prototype.hasPath = function () {
  return null !== this.path_;
};


URI.prototype.getQuery = function () {
  // From http://www.w3.org/Addressing/URL/4_URI_Recommentations.html
  // Within the query string, the plus sign is reserved as shorthand notation
  // for a space.
  return this.query_ && decodeURIComponent(this.query_).replace(/\+/g, ' ');
};
URI.prototype.getRawQuery = function () {
  return this.query_;
};
URI.prototype.setQuery = function (newQuery) {
  this.paramCache_ = null;
  this.query_ = encodeIfExists(newQuery);
  return this;
};
URI.prototype.setRawQuery = function (newQuery) {
  this.paramCache_ = null;
  this.query_ = newQuery ? newQuery : null;
  return this;
};
URI.prototype.hasQuery = function () {
  return null !== this.query_;
};

/**
 * sets the query given a list of strings of the form
 * [ key0, value0, key1, value1, ... ].
 *
 * <p><code>uri.setAllParameters(['a', 'b', 'c', 'd']).getQuery()</code>
 * will yield <code>'a=b&c=d'</code>.
 */
URI.prototype.setAllParameters = function (params) {
  if (typeof params === 'object') {
    if (!(params instanceof Array)
        && (params instanceof Object
            || Object.prototype.toString.call(params) !== '[object Array]')) {
      var newParams = [];
      var i = -1;
      for (var k in params) {
        var v = params[k];
        if ('string' === typeof v) {
          newParams[++i] = k;
          newParams[++i] = v;
        }
      }
      params = newParams;
    }
  }
  this.paramCache_ = null;
  var queryBuf = [];
  var separator = '';
  for (var j = 0; j < params.length;) {
    var k = params[j++];
    var v = params[j++];
    queryBuf.push(separator, encodeURIComponent(k.toString()));
    separator = '&';
    if (v) {
      queryBuf.push('=', encodeURIComponent(v.toString()));
    }
  }
  this.query_ = queryBuf.join('');
  return this;
};
URI.prototype.checkParameterCache_ = function () {
  if (!this.paramCache_) {
    var q = this.query_;
    if (!q) {
      this.paramCache_ = [];
    } else {
      var cgiParams = q.split(/[&\?]/);
      var out = [];
      var k = -1;
      for (var i = 0; i < cgiParams.length; ++i) {
        var m = cgiParams[i].match(/^([^=]*)(?:=(.*))?$/);
        // From http://www.w3.org/Addressing/URL/4_URI_Recommentations.html
        // Within the query string, the plus sign is reserved as shorthand
        // notation for a space.
        out[++k] = decodeURIComponent(m[1]).replace(/\+/g, ' ');
        out[++k] = decodeURIComponent(m[2] || '').replace(/\+/g, ' ');
      }
      this.paramCache_ = out;
    }
  }
};
/**
 * sets the values of the named cgi parameters.
 *
 * <p>So, <code>uri.parse('foo?a=b&c=d&e=f').setParameterValues('c', ['new'])
 * </code> yields <tt>foo?a=b&c=new&e=f</tt>.</p>
 *
 * @param key {string}
 * @param values {Array.<string>} the new values.  If values is a single string
 *   then it will be treated as the sole value.
 */
URI.prototype.setParameterValues = function (key, values) {
  // be nice and avoid subtle bugs where [] operator on string performs charAt
  // on some browsers and crashes on IE
  if (typeof values === 'string') {
    values = [ values ];
  }

  this.checkParameterCache_();
  var newValueIndex = 0;
  var pc = this.paramCache_;
  var params = [];
  for (var i = 0, k = 0; i < pc.length; i += 2) {
    if (key === pc[i]) {
      if (newValueIndex < values.length) {
        params.push(key, values[newValueIndex++]);
      }
    } else {
      params.push(pc[i], pc[i + 1]);
    }
  }
  while (newValueIndex < values.length) {
    params.push(key, values[newValueIndex++]);
  }
  this.setAllParameters(params);
  return this;
};
URI.prototype.removeParameter = function (key) {
  return this.setParameterValues(key, []);
};
/**
 * returns the parameters specified in the query part of the uri as a list of
 * keys and values like [ key0, value0, key1, value1, ... ].
 *
 * @return {Array.<string>}
 */
URI.prototype.getAllParameters = function () {
  this.checkParameterCache_();
  return this.paramCache_.slice(0, this.paramCache_.length);
};
/**
 * returns the value<b>s</b> for a given cgi parameter as a list of decoded
 * query parameter values.
 * @return {Array.<string>}
 */
URI.prototype.getParameterValues = function (paramNameUnescaped) {
  this.checkParameterCache_();
  var values = [];
  for (var i = 0; i < this.paramCache_.length; i += 2) {
    if (paramNameUnescaped === this.paramCache_[i]) {
      values.push(this.paramCache_[i + 1]);
    }
  }
  return values;
};
/**
 * returns a map of cgi parameter names to (non-empty) lists of values.
 * @return {Object.<string,Array.<string>>}
 */
URI.prototype.getParameterMap = function (paramNameUnescaped) {
  this.checkParameterCache_();
  var paramMap = {};
  for (var i = 0; i < this.paramCache_.length; i += 2) {
    var key = this.paramCache_[i++],
      value = this.paramCache_[i++];
    if (!(key in paramMap)) {
      paramMap[key] = [value];
    } else {
      paramMap[key].push(value);
    }
  }
  return paramMap;
};
/**
 * returns the first value for a given cgi parameter or null if the given
 * parameter name does not appear in the query string.
 * If the given parameter name does appear, but has no '<tt>=</tt>' following
 * it, then the empty string will be returned.
 * @return {string|null}
 */
URI.prototype.getParameterValue = function (paramNameUnescaped) {
  this.checkParameterCache_();
  for (var i = 0; i < this.paramCache_.length; i += 2) {
    if (paramNameUnescaped === this.paramCache_[i]) {
      return this.paramCache_[i + 1];
    }
  }
  return null;
};

URI.prototype.getFragment = function () {
  return this.fragment_ && decodeURIComponent(this.fragment_);
};
URI.prototype.getRawFragment = function () {
  return this.fragment_;
};
URI.prototype.setFragment = function (newFragment) {
  this.fragment_ = newFragment ? encodeURIComponent(newFragment) : null;
  return this;
};
URI.prototype.setRawFragment = function (newFragment) {
  this.fragment_ = newFragment ? newFragment : null;
  return this;
};
URI.prototype.hasFragment = function () {
  return null !== this.fragment_;
};

function nullIfAbsent(matchPart) {
  return ('string' == typeof matchPart) && (matchPart.length > 0)
         ? matchPart
         : null;
}




/**
 * a regular expression for breaking a URI into its component parts.
 *
 * <p>http://www.gbiv.com/protocols/uri/rfc/rfc3986.html#RFC2234 says
 * As the "first-match-wins" algorithm is identical to the "greedy"
 * disambiguation method used by POSIX regular expressions, it is natural and
 * commonplace to use a regular expression for parsing the potential five
 * components of a URI reference.
 *
 * <p>The following line is the regular expression for breaking-down a
 * well-formed URI reference into its components.
 *
 * <pre>
 * ^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?
 *  12            3  4          5       6  7        8 9
 * </pre>
 *
 * <p>The numbers in the second line above are only to assist readability; they
 * indicate the reference points for each subexpression (i.e., each paired
 * parenthesis). We refer to the value matched for subexpression <n> as $<n>.
 * For example, matching the above expression to
 * <pre>
 *     http://www.ics.uci.edu/pub/ietf/uri/#Related
 * </pre>
 * results in the following subexpression matches:
 * <pre>
 *    $1 = http:
 *    $2 = http
 *    $3 = //www.ics.uci.edu
 *    $4 = www.ics.uci.edu
 *    $5 = /pub/ietf/uri/
 *    $6 = <undefined>
 *    $7 = <undefined>
 *    $8 = #Related
 *    $9 = Related
 * </pre>
 * where <undefined> indicates that the component is not present, as is the
 * case for the query component in the above example. Therefore, we can
 * determine the value of the five components as
 * <pre>
 *    scheme    = $2
 *    authority = $4
 *    path      = $5
 *    query     = $7
 *    fragment  = $9
 * </pre>
 *
 * <p>msamuel: I have modified the regular expression slightly to expose the
 * credentials, domain, and port separately from the authority.
 * The modified version yields
 * <pre>
 *    $1 = http              scheme
 *    $2 = <undefined>       credentials -\
 *    $3 = www.ics.uci.edu   domain       | authority
 *    $4 = <undefined>       port        -/
 *    $5 = /pub/ietf/uri/    path
 *    $6 = <undefined>       query without ?
 *    $7 = Related           fragment without #
 * </pre>
 */
var URI_RE_ = new RegExp(
      "^" +
      "(?:" +
        "([^:/?#]+)" +         // scheme
      ":)?" +
      "(?://" +
        "(?:([^/?#]*)@)?" +    // credentials
        "([^/?#:@]*)" +        // domain
        "(?::([0-9]+))?" +     // port
      ")?" +
      "([^?#]+)?" +            // path
      "(?:\\?([^#]*))?" +      // query
      "(?:#(.*))?" +           // fragment
      "$"
      );

var URI_DISALLOWED_IN_SCHEME_OR_CREDENTIALS_ = /[#\/\?@]/g;
var URI_DISALLOWED_IN_PATH_ = /[\#\?]/g;

URI.parse = parse;
URI.create = create;
URI.resolve = resolve;
URI.collapse_dots = collapse_dots;  // Visible for testing.

// lightweight string-based api for loadModuleMaker
URI.utils = {
  mimeTypeOf: function (uri) {
    var uriObj = parse(uri);
    if (/\.html$/.test(uriObj.getPath())) {
      return 'text/html';
    } else {
      return 'application/javascript';
    }
  },
  resolve: function (base, uri) {
    if (base) {
      return resolve(parse(base), parse(uri)).toString();
    } else {
      return '' + uri;
    }
  }
};


return URI;
})();

// Copyright Google Inc.
// Licensed under the Apache Licence Version 2.0
// Autogenerated at Mon Feb 25 13:05:42 EST 2013
// @overrides window
// @provides html4
var html4 = {};
html4.atype = {
  'NONE': 0,
  'URI': 1,
  'URI_FRAGMENT': 11,
  'SCRIPT': 2,
  'STYLE': 3,
  'HTML': 12,
  'ID': 4,
  'IDREF': 5,
  'IDREFS': 6,
  'GLOBAL_NAME': 7,
  'LOCAL_NAME': 8,
  'CLASSES': 9,
  'FRAME_TARGET': 10,
  'MEDIA_QUERY': 13
};
html4[ 'atype' ] = html4.atype;
html4.ATTRIBS = {
  '*::class': 9,
  '*::dir': 0,
  '*::draggable': 0,
  '*::hidden': 0,
  '*::id': 4,
  '*::inert': 0,
  '*::itemprop': 0,
  '*::itemref': 6,
  '*::itemscope': 0,
  '*::lang': 0,
  '*::onblur': 2,
  '*::onchange': 2,
  '*::onclick': 2,
  '*::ondblclick': 2,
  '*::onfocus': 2,
  '*::onkeydown': 2,
  '*::onkeypress': 2,
  '*::onkeyup': 2,
  '*::onload': 2,
  '*::onmousedown': 2,
  '*::onmousemove': 2,
  '*::onmouseout': 2,
  '*::onmouseover': 2,
  '*::onmouseup': 2,
  '*::onreset': 2,
  '*::onscroll': 2,
  '*::onselect': 2,
  '*::onsubmit': 2,
  '*::onunload': 2,
  '*::spellcheck': 0,
  '*::style': 3,
  '*::title': 0,
  '*::translate': 0,
  'a::accesskey': 0,
  'a::coords': 0,
  'a::href': 1,
  'a::hreflang': 0,
  'a::name': 7,
  'a::onblur': 2,
  'a::onfocus': 2,
  'a::shape': 0,
  'a::tabindex': 0,
  'a::target': 10,
  'a::type': 0,
  'area::accesskey': 0,
  'area::alt': 0,
  'area::coords': 0,
  'area::href': 1,
  'area::nohref': 0,
  'area::onblur': 2,
  'area::onfocus': 2,
  'area::shape': 0,
  'area::tabindex': 0,
  'area::target': 10,
  'audio::controls': 0,
  'audio::loop': 0,
  'audio::mediagroup': 5,
  'audio::muted': 0,
  'audio::preload': 0,
  'bdo::dir': 0,
  'blockquote::cite': 1,
  'br::clear': 0,
  'button::accesskey': 0,
  'button::disabled': 0,
  'button::name': 8,
  'button::onblur': 2,
  'button::onfocus': 2,
  'button::tabindex': 0,
  'button::type': 0,
  'button::value': 0,
  'canvas::height': 0,
  'canvas::width': 0,
  'caption::align': 0,
  'col::align': 0,
  'col::char': 0,
  'col::charoff': 0,
  'col::span': 0,
  'col::valign': 0,
  'col::width': 0,
  'colgroup::align': 0,
  'colgroup::char': 0,
  'colgroup::charoff': 0,
  'colgroup::span': 0,
  'colgroup::valign': 0,
  'colgroup::width': 0,
  'command::checked': 0,
  'command::command': 5,
  'command::disabled': 0,
  'command::icon': 1,
  'command::label': 0,
  'command::radiogroup': 0,
  'command::type': 0,
  'data::value': 0,
  'del::cite': 1,
  'del::datetime': 0,
  'details::open': 0,
  'dir::compact': 0,
  'div::align': 0,
  'dl::compact': 0,
  'fieldset::disabled': 0,
  'font::color': 0,
  'font::face': 0,
  'font::size': 0,
  'form::accept': 0,
  'form::action': 1,
  'form::autocomplete': 0,
  'form::enctype': 0,
  'form::method': 0,
  'form::name': 7,
  'form::novalidate': 0,
  'form::onreset': 2,
  'form::onsubmit': 2,
  'form::target': 10,
  'h1::align': 0,
  'h2::align': 0,
  'h3::align': 0,
  'h4::align': 0,
  'h5::align': 0,
  'h6::align': 0,
  'hr::align': 0,
  'hr::noshade': 0,
  'hr::size': 0,
  'hr::width': 0,
  'iframe::align': 0,
  'iframe::frameborder': 0,
  'iframe::height': 0,
  'iframe::marginheight': 0,
  'iframe::marginwidth': 0,
  'iframe::width': 0,
  'img::align': 0,
  'img::alt': 0,
  'img::border': 0,
  'img::height': 0,
  'img::hspace': 0,
  'img::ismap': 0,
  'img::name': 7,
  'img::src': 1,
  'img::usemap': 11,
  'img::vspace': 0,
  'img::width': 0,
  'input::accept': 0,
  'input::accesskey': 0,
  'input::align': 0,
  'input::alt': 0,
  'input::autocomplete': 0,
  'input::checked': 0,
  'input::disabled': 0,
  'input::inputmode': 0,
  'input::ismap': 0,
  'input::list': 5,
  'input::max': 0,
  'input::maxlength': 0,
  'input::min': 0,
  'input::multiple': 0,
  'input::name': 8,
  'input::onblur': 2,
  'input::onchange': 2,
  'input::onfocus': 2,
  'input::onselect': 2,
  'input::placeholder': 0,
  'input::readonly': 0,
  'input::required': 0,
  'input::size': 0,
  'input::src': 1,
  'input::step': 0,
  'input::tabindex': 0,
  'input::type': 0,
  'input::usemap': 11,
  'input::value': 0,
  'ins::cite': 1,
  'ins::datetime': 0,
  'label::accesskey': 0,
  'label::for': 5,
  'label::onblur': 2,
  'label::onfocus': 2,
  'legend::accesskey': 0,
  'legend::align': 0,
  'li::type': 0,
  'li::value': 0,
  'map::name': 7,
  'menu::compact': 0,
  'menu::label': 0,
  'menu::type': 0,
  'meter::high': 0,
  'meter::low': 0,
  'meter::max': 0,
  'meter::min': 0,
  'meter::value': 0,
  'ol::compact': 0,
  'ol::reversed': 0,
  'ol::start': 0,
  'ol::type': 0,
  'optgroup::disabled': 0,
  'optgroup::label': 0,
  'option::disabled': 0,
  'option::label': 0,
  'option::selected': 0,
  'option::value': 0,
  'output::for': 6,
  'output::name': 8,
  'p::align': 0,
  'pre::width': 0,
  'progress::max': 0,
  'progress::min': 0,
  'progress::value': 0,
  'q::cite': 1,
  'select::autocomplete': 0,
  'select::disabled': 0,
  'select::multiple': 0,
  'select::name': 8,
  'select::onblur': 2,
  'select::onchange': 2,
  'select::onfocus': 2,
  'select::required': 0,
  'select::size': 0,
  'select::tabindex': 0,
  'source::type': 0,
  'table::align': 0,
  'table::bgcolor': 0,
  'table::border': 0,
  'table::cellpadding': 0,
  'table::cellspacing': 0,
  'table::frame': 0,
  'table::rules': 0,
  'table::summary': 0,
  'table::width': 0,
  'tbody::align': 0,
  'tbody::char': 0,
  'tbody::charoff': 0,
  'tbody::valign': 0,
  'td::abbr': 0,
  'td::align': 0,
  'td::axis': 0,
  'td::bgcolor': 0,
  'td::char': 0,
  'td::charoff': 0,
  'td::colspan': 0,
  'td::headers': 6,
  'td::height': 0,
  'td::nowrap': 0,
  'td::rowspan': 0,
  'td::scope': 0,
  'td::valign': 0,
  'td::width': 0,
  'textarea::accesskey': 0,
  'textarea::autocomplete': 0,
  'textarea::cols': 0,
  'textarea::disabled': 0,
  'textarea::inputmode': 0,
  'textarea::name': 8,
  'textarea::onblur': 2,
  'textarea::onchange': 2,
  'textarea::onfocus': 2,
  'textarea::onselect': 2,
  'textarea::placeholder': 0,
  'textarea::readonly': 0,
  'textarea::required': 0,
  'textarea::rows': 0,
  'textarea::tabindex': 0,
  'textarea::wrap': 0,
  'tfoot::align': 0,
  'tfoot::char': 0,
  'tfoot::charoff': 0,
  'tfoot::valign': 0,
  'th::abbr': 0,
  'th::align': 0,
  'th::axis': 0,
  'th::bgcolor': 0,
  'th::char': 0,
  'th::charoff': 0,
  'th::colspan': 0,
  'th::headers': 6,
  'th::height': 0,
  'th::nowrap': 0,
  'th::rowspan': 0,
  'th::scope': 0,
  'th::valign': 0,
  'th::width': 0,
  'thead::align': 0,
  'thead::char': 0,
  'thead::charoff': 0,
  'thead::valign': 0,
  'tr::align': 0,
  'tr::bgcolor': 0,
  'tr::char': 0,
  'tr::charoff': 0,
  'tr::valign': 0,
  'track::default': 0,
  'track::kind': 0,
  'track::label': 0,
  'track::srclang': 0,
  'ul::compact': 0,
  'ul::type': 0,
  'video::controls': 0,
  'video::height': 0,
  'video::loop': 0,
  'video::mediagroup': 5,
  'video::muted': 0,
  'video::poster': 1,
  'video::preload': 0,
  'video::width': 0
};
html4[ 'ATTRIBS' ] = html4.ATTRIBS;
html4.eflags = {
  'OPTIONAL_ENDTAG': 1,
  'EMPTY': 2,
  'CDATA': 4,
  'RCDATA': 8,
  'UNSAFE': 16,
  'FOLDABLE': 32,
  'SCRIPT': 64,
  'STYLE': 128,
  'VIRTUALIZED': 256
};
html4[ 'eflags' ] = html4.eflags;
// these are bitmasks of the eflags above.
html4.ELEMENTS = {
  'a': 0,
  'abbr': 0,
  'acronym': 0,
  'address': 0,
  'applet': 272,
  'area': 2,
  'article': 0,
  'aside': 0,
  'audio': 0,
  'b': 0,
  'base': 274,
  'basefont': 274,
  'bdi': 0,
  'bdo': 0,
  'big': 0,
  'blockquote': 0,
  'body': 305,
  'br': 2,
  'button': 0,
  'canvas': 0,
  'caption': 0,
  'center': 0,
  'cite': 0,
  'code': 0,
  'col': 2,
  'colgroup': 1,
  'command': 2,
  'data': 0,
  'datalist': 0,
  'dd': 1,
  'del': 0,
  'details': 0,
  'dfn': 0,
  'dialog': 272,
  'dir': 0,
  'div': 0,
  'dl': 0,
  'dt': 1,
  'em': 0,
  'fieldset': 0,
  'figcaption': 0,
  'figure': 0,
  'font': 0,
  'footer': 0,
  'form': 0,
  'frame': 274,
  'frameset': 272,
  'h1': 0,
  'h2': 0,
  'h3': 0,
  'h4': 0,
  'h5': 0,
  'h6': 0,
  'head': 305,
  'header': 0,
  'hgroup': 0,
  'hr': 2,
  'html': 305,
  'i': 0,
  'iframe': 16,
  'img': 2,
  'input': 2,
  'ins': 0,
  'isindex': 274,
  'kbd': 0,
  'keygen': 274,
  'label': 0,
  'legend': 0,
  'li': 1,
  'link': 274,
  'map': 0,
  'mark': 0,
  'menu': 0,
  'meta': 274,
  'meter': 0,
  'nav': 0,
  'nobr': 0,
  'noembed': 276,
  'noframes': 276,
  'noscript': 276,
  'object': 272,
  'ol': 0,
  'optgroup': 0,
  'option': 1,
  'output': 0,
  'p': 1,
  'param': 274,
  'pre': 0,
  'progress': 0,
  'q': 0,
  's': 0,
  'samp': 0,
  'script': 84,
  'section': 0,
  'select': 0,
  'small': 0,
  'source': 2,
  'span': 0,
  'strike': 0,
  'strong': 0,
  'style': 148,
  'sub': 0,
  'summary': 0,
  'sup': 0,
  'table': 0,
  'tbody': 1,
  'td': 1,
  'textarea': 8,
  'tfoot': 1,
  'th': 1,
  'thead': 1,
  'time': 0,
  'title': 280,
  'tr': 1,
  'track': 2,
  'tt': 0,
  'u': 0,
  'ul': 0,
  'var': 0,
  'video': 0,
  'wbr': 2
};
html4[ 'ELEMENTS' ] = html4.ELEMENTS;
html4.ELEMENT_DOM_INTERFACES = {
  'a': 'HTMLAnchorElement',
  'abbr': 'HTMLElement',
  'acronym': 'HTMLElement',
  'address': 'HTMLElement',
  'applet': 'HTMLAppletElement',
  'area': 'HTMLAreaElement',
  'article': 'HTMLElement',
  'aside': 'HTMLElement',
  'audio': 'HTMLAudioElement',
  'b': 'HTMLElement',
  'base': 'HTMLBaseElement',
  'basefont': 'HTMLBaseFontElement',
  'bdi': 'HTMLElement',
  'bdo': 'HTMLElement',
  'big': 'HTMLElement',
  'blockquote': 'HTMLQuoteElement',
  'body': 'HTMLBodyElement',
  'br': 'HTMLBRElement',
  'button': 'HTMLButtonElement',
  'canvas': 'HTMLCanvasElement',
  'caption': 'HTMLTableCaptionElement',
  'center': 'HTMLElement',
  'cite': 'HTMLElement',
  'code': 'HTMLElement',
  'col': 'HTMLTableColElement',
  'colgroup': 'HTMLTableColElement',
  'command': 'HTMLCommandElement',
  'data': 'HTMLElement',
  'datalist': 'HTMLDataListElement',
  'dd': 'HTMLElement',
  'del': 'HTMLModElement',
  'details': 'HTMLDetailsElement',
  'dfn': 'HTMLElement',
  'dialog': 'HTMLDialogElement',
  'dir': 'HTMLDirectoryElement',
  'div': 'HTMLDivElement',
  'dl': 'HTMLDListElement',
  'dt': 'HTMLElement',
  'em': 'HTMLElement',
  'fieldset': 'HTMLFieldSetElement',
  'figcaption': 'HTMLElement',
  'figure': 'HTMLElement',
  'font': 'HTMLFontElement',
  'footer': 'HTMLElement',
  'form': 'HTMLFormElement',
  'frame': 'HTMLFrameElement',
  'frameset': 'HTMLFrameSetElement',
  'h1': 'HTMLHeadingElement',
  'h2': 'HTMLHeadingElement',
  'h3': 'HTMLHeadingElement',
  'h4': 'HTMLHeadingElement',
  'h5': 'HTMLHeadingElement',
  'h6': 'HTMLHeadingElement',
  'head': 'HTMLHeadElement',
  'header': 'HTMLElement',
  'hgroup': 'HTMLElement',
  'hr': 'HTMLHRElement',
  'html': 'HTMLHtmlElement',
  'i': 'HTMLElement',
  'iframe': 'HTMLIFrameElement',
  'img': 'HTMLImageElement',
  'input': 'HTMLInputElement',
  'ins': 'HTMLModElement',
  'isindex': 'HTMLUnknownElement',
  'kbd': 'HTMLElement',
  'keygen': 'HTMLKeygenElement',
  'label': 'HTMLLabelElement',
  'legend': 'HTMLLegendElement',
  'li': 'HTMLLIElement',
  'link': 'HTMLLinkElement',
  'map': 'HTMLMapElement',
  'mark': 'HTMLElement',
  'menu': 'HTMLMenuElement',
  'meta': 'HTMLMetaElement',
  'meter': 'HTMLMeterElement',
  'nav': 'HTMLElement',
  'nobr': 'HTMLElement',
  'noembed': 'HTMLElement',
  'noframes': 'HTMLElement',
  'noscript': 'HTMLElement',
  'object': 'HTMLObjectElement',
  'ol': 'HTMLOListElement',
  'optgroup': 'HTMLOptGroupElement',
  'option': 'HTMLOptionElement',
  'output': 'HTMLOutputElement',
  'p': 'HTMLParagraphElement',
  'param': 'HTMLParamElement',
  'pre': 'HTMLPreElement',
  'progress': 'HTMLProgressElement',
  'q': 'HTMLQuoteElement',
  's': 'HTMLElement',
  'samp': 'HTMLElement',
  'script': 'HTMLScriptElement',
  'section': 'HTMLElement',
  'select': 'HTMLSelectElement',
  'small': 'HTMLElement',
  'source': 'HTMLSourceElement',
  'span': 'HTMLSpanElement',
  'strike': 'HTMLElement',
  'strong': 'HTMLElement',
  'style': 'HTMLStyleElement',
  'sub': 'HTMLElement',
  'summary': 'HTMLElement',
  'sup': 'HTMLElement',
  'table': 'HTMLTableElement',
  'tbody': 'HTMLTableSectionElement',
  'td': 'HTMLTableDataCellElement',
  'textarea': 'HTMLTextAreaElement',
  'tfoot': 'HTMLTableSectionElement',
  'th': 'HTMLTableHeaderCellElement',
  'thead': 'HTMLTableSectionElement',
  'time': 'HTMLTimeElement',
  'title': 'HTMLTitleElement',
  'tr': 'HTMLTableRowElement',
  'track': 'HTMLTrackElement',
  'tt': 'HTMLElement',
  'u': 'HTMLElement',
  'ul': 'HTMLUListElement',
  'var': 'HTMLElement',
  'video': 'HTMLVideoElement',
  'wbr': 'HTMLElement'
};
html4[ 'ELEMENT_DOM_INTERFACES' ] = html4.ELEMENT_DOM_INTERFACES;
html4.ueffects = {
  'NOT_LOADED': 0,
  'SAME_DOCUMENT': 1,
  'NEW_DOCUMENT': 2
};
html4[ 'ueffects' ] = html4.ueffects;
html4.URIEFFECTS = {
  'a::href': 2,
  'area::href': 2,
  'blockquote::cite': 0,
  'command::icon': 1,
  'del::cite': 0,
  'form::action': 2,
  'img::src': 1,
  'input::src': 1,
  'ins::cite': 0,
  'q::cite': 0,
  'video::poster': 1
};
html4[ 'URIEFFECTS' ] = html4.URIEFFECTS;
html4.ltypes = {
  'UNSANDBOXED': 2,
  'SANDBOXED': 1,
  'DATA': 0
};
html4[ 'ltypes' ] = html4.ltypes;
html4.LOADERTYPES = {
  'a::href': 2,
  'area::href': 2,
  'blockquote::cite': 2,
  'command::icon': 1,
  'del::cite': 2,
  'form::action': 2,
  'img::src': 1,
  'input::src': 1,
  'ins::cite': 2,
  'q::cite': 2,
  'video::poster': 1
};
html4[ 'LOADERTYPES' ] = html4.LOADERTYPES;

// Copyright (C) 2006 Google Inc.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

/**
 * @fileoverview
 * An HTML sanitizer that can satisfy a variety of security policies.
 *
 * <p>
 * The HTML sanitizer is built around a SAX parser and HTML element and
 * attributes schemas.
 *
 * If the cssparser is loaded, inline styles are sanitized using the
 * css property and value schemas.  Else they are remove during
 * sanitization.
 *
 * If it exists, uses parseCssDeclarations, sanitizeCssProperty,  cssSchema
 *
 * @author mikesamuel@gmail.com
 * @author jasvir@gmail.com
 * \@requires html4, URI
 * \@overrides window
 * \@provides html, html_sanitize
 */

// The Turkish i seems to be a non-issue, but abort in case it is.
if ('I'.toLowerCase() !== 'i') { throw 'I/i problem'; }

/**
 * \@namespace
 */
var html = (function(html4) {

  // For closure compiler
  var parseCssDeclarations, sanitizeCssProperty, cssSchema;
  if ('undefined' !== typeof window) {
    parseCssDeclarations = window['parseCssDeclarations'];
    sanitizeCssProperty = window['sanitizeCssProperty'];
    cssSchema = window['cssSchema'];
  }

  // The keys of this object must be 'quoted' or JSCompiler will mangle them!
  // This is a partial list -- lookupEntity() uses the host browser's parser
  // (when available) to implement full entity lookup.
  // Note that entities are in general case-sensitive; the uppercase ones are
  // explicitly defined by HTML5 (presumably as compatibility).
  var ENTITIES = {
    'lt': '<',
    'LT': '<',
    'gt': '>',
    'GT': '>',
    'amp': '&',
    'AMP': '&',
    'quot': '"',
    'apos': '\'',
    'nbsp': '\u00A0'
  };

  // Patterns for types of entity/character reference names.
  var decimalEscapeRe = /^#(\d+)$/;
  var hexEscapeRe = /^#x([0-9A-Fa-f]+)$/;
  // contains every entity per http://www.w3.org/TR/2011/WD-html5-20110113/named-character-references.html
  var safeEntityNameRe = /^[A-Za-z][A-za-z0-9]+$/;
  // Used as a hook to invoke the browser's entity parsing. <textarea> is used
  // because its content is parsed for entities but not tags.
  // TODO(kpreid): This retrieval is a kludge and leads to silent loss of
  // functionality if the document isn't available.
  var entityLookupElement =
      ('undefined' !== typeof window && window['document'])
          ? window['document'].createElement('textarea') : null;
  /**
   * Decodes an HTML entity.
   *
   * {\@updoc
   * $ lookupEntity('lt')
   * # '<'
   * $ lookupEntity('GT')
   * # '>'
   * $ lookupEntity('amp')
   * # '&'
   * $ lookupEntity('nbsp')
   * # '\xA0'
   * $ lookupEntity('apos')
   * # "'"
   * $ lookupEntity('quot')
   * # '"'
   * $ lookupEntity('#xa')
   * # '\n'
   * $ lookupEntity('#10')
   * # '\n'
   * $ lookupEntity('#x0a')
   * # '\n'
   * $ lookupEntity('#010')
   * # '\n'
   * $ lookupEntity('#x00A')
   * # '\n'
   * $ lookupEntity('Pi')      // Known failure
   * # '\u03A0'
   * $ lookupEntity('pi')      // Known failure
   * # '\u03C0'
   * }
   *
   * @param {string} name the content between the '&' and the ';'.
   * @return {string} a single unicode code-point as a string.
   */
  function lookupEntity(name) {
    // TODO: entity lookup as specified by HTML5 actually depends on the
    // presence of the ";".
    if (ENTITIES.hasOwnProperty(name)) { return ENTITIES[name]; }
    var m = name.match(decimalEscapeRe);
    if (m) {
      return String.fromCharCode(parseInt(m[1], 10));
    } else if (!!(m = name.match(hexEscapeRe))) {
      return String.fromCharCode(parseInt(m[1], 16));
    } else if (entityLookupElement && safeEntityNameRe.test(name)) {
      entityLookupElement.innerHTML = '&' + name + ';';
      var text = entityLookupElement.textContent;
      ENTITIES[name] = text;
      return text;
    } else {
      return '&' + name + ';';
    }
  }

  function decodeOneEntity(_, name) {
    return lookupEntity(name);
  }

  var nulRe = /\0/g;
  function stripNULs(s) {
    return s.replace(nulRe, '');
  }

  var ENTITY_RE_1 = /&(#[0-9]+|#[xX][0-9A-Fa-f]+|\w+);/g;
  var ENTITY_RE_2 = /^(#[0-9]+|#[xX][0-9A-Fa-f]+|\w+);/;
  /**
   * The plain text of a chunk of HTML CDATA which possibly containing.
   *
   * {\@updoc
   * $ unescapeEntities('')
   * # ''
   * $ unescapeEntities('hello World!')
   * # 'hello World!'
   * $ unescapeEntities('1 &lt; 2 &amp;&AMP; 4 &gt; 3&#10;')
   * # '1 < 2 && 4 > 3\n'
   * $ unescapeEntities('&lt;&lt <- unfinished entity&gt;')
   * # '<&lt <- unfinished entity>'
   * $ unescapeEntities('/foo?bar=baz&copy=true')  // & often unescaped in URLS
   * # '/foo?bar=baz&copy=true'
   * $ unescapeEntities('pi=&pi;&#x3c0;, Pi=&Pi;\u03A0') // FIXME: known failure
   * # 'pi=\u03C0\u03c0, Pi=\u03A0\u03A0'
   * }
   *
   * @param {string} s a chunk of HTML CDATA.  It must not start or end inside
   *     an HTML entity.
   */
  function unescapeEntities(s) {
    return s.replace(ENTITY_RE_1, decodeOneEntity);
  }

  var ampRe = /&/g;
  var looseAmpRe = /&([^a-z#]|#(?:[^0-9x]|x(?:[^0-9a-f]|$)|$)|$)/gi;
  var ltRe = /[<]/g;
  var gtRe = />/g;
  var quotRe = /\"/g;

  /**
   * Escapes HTML special characters in attribute values.
   *
   * {\@updoc
   * $ escapeAttrib('')
   * # ''
   * $ escapeAttrib('"<<&==&>>"')  // Do not just escape the first occurrence.
   * # '&#34;&lt;&lt;&amp;&#61;&#61;&amp;&gt;&gt;&#34;'
   * $ escapeAttrib('Hello <World>!')
   * # 'Hello &lt;World&gt;!'
   * }
   */
  function escapeAttrib(s) {
    return ('' + s).replace(ampRe, '&amp;').replace(ltRe, '&lt;')
        .replace(gtRe, '&gt;').replace(quotRe, '&#34;');
  }

  /**
   * Escape entities in RCDATA that can be escaped without changing the meaning.
   * {\@updoc
   * $ normalizeRCData('1 < 2 &&amp; 3 > 4 &amp;& 5 &lt; 7&8')
   * # '1 &lt; 2 &amp;&amp; 3 &gt; 4 &amp;&amp; 5 &lt; 7&amp;8'
   * }
   */
  function normalizeRCData(rcdata) {
    return rcdata
        .replace(looseAmpRe, '&amp;$1')
        .replace(ltRe, '&lt;')
        .replace(gtRe, '&gt;');
  }

  // TODO(felix8a): validate sanitizer regexs against the HTML5 grammar at
  // http://www.whatwg.org/specs/web-apps/current-work/multipage/syntax.html
  // http://www.whatwg.org/specs/web-apps/current-work/multipage/parsing.html
  // http://www.whatwg.org/specs/web-apps/current-work/multipage/tokenization.html
  // http://www.whatwg.org/specs/web-apps/current-work/multipage/tree-construction.html

  // We initially split input so that potentially meaningful characters
  // like '<' and '>' are separate tokens, using a fast dumb process that
  // ignores quoting.  Then we walk that token stream, and when we see a
  // '<' that's the start of a tag, we use ATTR_RE to extract tag
  // attributes from the next token.  That token will never have a '>'
  // character.  However, it might have an unbalanced quote character, and
  // when we see that, we combine additional tokens to balance the quote.

  var ATTR_RE = new RegExp(
    '^\\s*' +
    '([-.:\\w]+)' +             // 1 = Attribute name
    '(?:' + (
      '\\s*(=)\\s*' +           // 2 = Is there a value?
      '(' + (                   // 3 = Attribute value
        // TODO(felix8a): maybe use backref to match quotes
        '(\")[^\"]*(\"|$)' +    // 4, 5 = Double-quoted string
        '|' +
        '(\')[^\']*(\'|$)' +    // 6, 7 = Single-quoted string
        '|' +
        // Positive lookahead to prevent interpretation of
        // <foo a= b=c> as <foo a='b=c'>
        // TODO(felix8a): might be able to drop this case
        '(?=[a-z][-\\w]*\\s*=)' +
        '|' +
        // Unquoted value that isn't an attribute name
        // (since we didn't match the positive lookahead above)
        '[^\"\'\\s]*' ) +
      ')' ) +
    ')?',
    'i');

  // false on IE<=8, true on most other browsers
  var splitWillCapture = ('a,b'.split(/(,)/).length === 3);

  // bitmask for tags with special parsing, like <script> and <textarea>
  var EFLAGS_TEXT = html4.eflags['CDATA'] | html4.eflags['RCDATA'];

  /**
   * Given a SAX-like event handler, produce a function that feeds those
   * events and a parameter to the event handler.
   *
   * The event handler has the form:{@code
   * {
   *   // Name is an upper-case HTML tag name.  Attribs is an array of
   *   // alternating upper-case attribute names, and attribute values.  The
   *   // attribs array is reused by the parser.  Param is the value passed to
   *   // the saxParser.
   *   startTag: function (name, attribs, param) { ... },
   *   endTag:   function (name, param) { ... },
   *   pcdata:   function (text, param) { ... },
   *   rcdata:   function (text, param) { ... },
   *   cdata:    function (text, param) { ... },
   *   startDoc: function (param) { ... },
   *   endDoc:   function (param) { ... }
   * }}
   *
   * @param {Object} handler a record containing event handlers.
   * @return {function(string, Object)} A function that takes a chunk of HTML
   *     and a parameter.  The parameter is passed on to the handler methods.
   */
  function makeSaxParser(handler) {
    // Accept quoted or unquoted keys (Closure compat)
    var hcopy = {
      cdata: handler.cdata || handler['cdata'],
      comment: handler.comment || handler['comment'],
      endDoc: handler.endDoc || handler['endDoc'],
      endTag: handler.endTag || handler['endTag'],
      pcdata: handler.pcdata || handler['pcdata'],
      rcdata: handler.rcdata || handler['rcdata'],
      startDoc: handler.startDoc || handler['startDoc'],
      startTag: handler.startTag || handler['startTag']
    };
    return function(htmlText, param) {
      return parse(htmlText, hcopy, param);
    };
  }

  // Parsing strategy is to split input into parts that might be lexically
  // meaningful (every ">" becomes a separate part), and then recombine
  // parts if we discover they're in a different context.

  // TODO(felix8a): Significant performance regressions from -legacy,
  // tested on
  //    Chrome 18.0
  //    Firefox 11.0
  //    IE 6, 7, 8, 9
  //    Opera 11.61
  //    Safari 5.1.3
  // Many of these are unusual patterns that are linearly slower and still
  // pretty fast (eg 1ms to 5ms), so not necessarily worth fixing.

  // TODO(felix8a): "<script> && && && ... <\/script>" is slower on all
  // browsers.  The hotspot is htmlSplit.

  // TODO(felix8a): "<p title='>>>>...'><\/p>" is slower on all browsers.
  // This is partly htmlSplit, but the hotspot is parseTagAndAttrs.

  // TODO(felix8a): "<a><\/a><a><\/a>..." is slower on IE9.
  // "<a>1<\/a><a>1<\/a>..." is faster, "<a><\/a>2<a><\/a>2..." is faster.

  // TODO(felix8a): "<p<p<p..." is slower on IE[6-8]

  var continuationMarker = {};
  function parse(htmlText, handler, param) {
    var m, p, tagName;
    var parts = htmlSplit(htmlText);
    var state = {
      noMoreGT: false,
      noMoreEndComments: false
    };
    parseCPS(handler, parts, 0, state, param);
  }

  function continuationMaker(h, parts, initial, state, param) {
    return function () {
      parseCPS(h, parts, initial, state, param);
    };
  }

  function parseCPS(h, parts, initial, state, param) {
    try {
      if (h.startDoc && initial == 0) { h.startDoc(param); }
      var m, p, tagName;
      for (var pos = initial, end = parts.length; pos < end;) {
        var current = parts[pos++];
        var next = parts[pos];
        switch (current) {
        case '&':
          if (ENTITY_RE_2.test(next)) {
            if (h.pcdata) {
              h.pcdata('&' + next, param, continuationMarker,
                continuationMaker(h, parts, pos, state, param));
            }
            pos++;
          } else {
            if (h.pcdata) { h.pcdata("&amp;", param, continuationMarker,
                continuationMaker(h, parts, pos, state, param));
            }
          }
          break;
        case '<\/':
          if (m = /^([-\w:]+)[^\'\"]*/.exec(next)) {
            if (m[0].length === next.length && parts[pos + 1] === '>') {
              // fast case, no attribute parsing needed
              pos += 2;
              tagName = m[1].toLowerCase();
              if (h.endTag) {
                h.endTag(tagName, param, continuationMarker,
                  continuationMaker(h, parts, pos, state, param));
              }
            } else {
              // slow case, need to parse attributes
              // TODO(felix8a): do we really care about misparsing this?
              pos = parseEndTag(
                parts, pos, h, param, continuationMarker, state);
            }
          } else {
            if (h.pcdata) {
              h.pcdata('&lt;/', param, continuationMarker,
                continuationMaker(h, parts, pos, state, param));
            }
          }
          break;
        case '<':
          if (m = /^([-\w:]+)\s*\/?/.exec(next)) {
            if (m[0].length === next.length && parts[pos + 1] === '>') {
              // fast case, no attribute parsing needed
              pos += 2;
              tagName = m[1].toLowerCase();
              if (h.startTag) {
                h.startTag(tagName, [], param, continuationMarker,
                  continuationMaker(h, parts, pos, state, param));
              }
              // tags like <script> and <textarea> have special parsing
              var eflags = html4.ELEMENTS[tagName];
              if (eflags & EFLAGS_TEXT) {
                var tag = { name: tagName, next: pos, eflags: eflags };
                pos = parseText(
                  parts, tag, h, param, continuationMarker, state);
              }
            } else {
              // slow case, need to parse attributes
              pos = parseStartTag(
                parts, pos, h, param, continuationMarker, state);
            }
          } else {
            if (h.pcdata) {
              h.pcdata('&lt;', param, continuationMarker,
                continuationMaker(h, parts, pos, state, param));
            }
          }
          break;
        case '<\!--':
          // The pathological case is n copies of '<\!--' without '-->', and
          // repeated failure to find '-->' is quadratic.  We avoid that by
          // remembering when search for '-->' fails.
          if (!state.noMoreEndComments) {
            // A comment <\!--x--> is split into three tokens:
            //   '<\!--', 'x--', '>'
            // We want to find the next '>' token that has a preceding '--'.
            // pos is at the 'x--'.
            for (p = pos + 1; p < end; p++) {
              if (parts[p] === '>' && /--$/.test(parts[p - 1])) { break; }
            }
            if (p < end) {
              if (h.comment) {
                var comment = parts.slice(pos, p).join('');
                h.comment(
                  comment.substr(0, comment.length - 2), param,
                  continuationMarker,
                  continuationMaker(h, parts, p + 1, state, param));
              }
              pos = p + 1;
            } else {
              state.noMoreEndComments = true;
            }
          }
          if (state.noMoreEndComments) {
            if (h.pcdata) {
              h.pcdata('&lt;!--', param, continuationMarker,
                continuationMaker(h, parts, pos, state, param));
            }
          }
          break;
        case '<\!':
          if (!/^\w/.test(next)) {
            if (h.pcdata) {
              h.pcdata('&lt;!', param, continuationMarker,
                continuationMaker(h, parts, pos, state, param));
            }
          } else {
            // similar to noMoreEndComment logic
            if (!state.noMoreGT) {
              for (p = pos + 1; p < end; p++) {
                if (parts[p] === '>') { break; }
              }
              if (p < end) {
                pos = p + 1;
              } else {
                state.noMoreGT = true;
              }
            }
            if (state.noMoreGT) {
              if (h.pcdata) {
                h.pcdata('&lt;!', param, continuationMarker,
                  continuationMaker(h, parts, pos, state, param));
              }
            }
          }
          break;
        case '<?':
          // similar to noMoreEndComment logic
          if (!state.noMoreGT) {
            for (p = pos + 1; p < end; p++) {
              if (parts[p] === '>') { break; }
            }
            if (p < end) {
              pos = p + 1;
            } else {
              state.noMoreGT = true;
            }
          }
          if (state.noMoreGT) {
            if (h.pcdata) {
              h.pcdata('&lt;?', param, continuationMarker,
                continuationMaker(h, parts, pos, state, param));
            }
          }
          break;
        case '>':
          if (h.pcdata) {
            h.pcdata("&gt;", param, continuationMarker,
              continuationMaker(h, parts, pos, state, param));
          }
          break;
        case '':
          break;
        default:
          if (h.pcdata) {
            h.pcdata(current, param, continuationMarker,
              continuationMaker(h, parts, pos, state, param));
          }
          break;
        }
      }
      if (h.endDoc) { h.endDoc(param); }
    } catch (e) {
      if (e !== continuationMarker) { throw e; }
    }
  }

  // Split str into parts for the html parser.
  function htmlSplit(str) {
    // can't hoist this out of the function because of the re.exec loop.
    var re = /(<\/|<\!--|<[!?]|[&<>])/g;
    str += '';
    if (splitWillCapture) {
      return str.split(re);
    } else {
      var parts = [];
      var lastPos = 0;
      var m;
      while ((m = re.exec(str)) !== null) {
        parts.push(str.substring(lastPos, m.index));
        parts.push(m[0]);
        lastPos = m.index + m[0].length;
      }
      parts.push(str.substring(lastPos));
      return parts;
    }
  }

  function parseEndTag(parts, pos, h, param, continuationMarker, state) {
    var tag = parseTagAndAttrs(parts, pos);
    // drop unclosed tags
    if (!tag) { return parts.length; }
    if (h.endTag) {
      h.endTag(tag.name, param, continuationMarker,
        continuationMaker(h, parts, pos, state, param));
    }
    return tag.next;
  }

  function parseStartTag(parts, pos, h, param, continuationMarker, state) {
    var tag = parseTagAndAttrs(parts, pos);
    // drop unclosed tags
    if (!tag) { return parts.length; }
    if (h.startTag) {
      h.startTag(tag.name, tag.attrs, param, continuationMarker,
        continuationMaker(h, parts, tag.next, state, param));
    }
    // tags like <script> and <textarea> have special parsing
    if (tag.eflags & EFLAGS_TEXT) {
      return parseText(parts, tag, h, param, continuationMarker, state);
    } else {
      return tag.next;
    }
  }

  var endTagRe = {};

  // Tags like <script> and <textarea> are flagged as CDATA or RCDATA,
  // which means everything is text until we see the correct closing tag.
  function parseText(parts, tag, h, param, continuationMarker, state) {
    var end = parts.length;
    if (!endTagRe.hasOwnProperty(tag.name)) {
      endTagRe[tag.name] = new RegExp('^' + tag.name + '(?:[\\s\\/]|$)', 'i');
    }
    var re = endTagRe[tag.name];
    var first = tag.next;
    var p = tag.next + 1;
    for (; p < end; p++) {
      if (parts[p - 1] === '<\/' && re.test(parts[p])) { break; }
    }
    if (p < end) { p -= 1; }
    var buf = parts.slice(first, p).join('');
    if (tag.eflags & html4.eflags['CDATA']) {
      if (h.cdata) {
        h.cdata(buf, param, continuationMarker,
          continuationMaker(h, parts, p, state, param));
      }
    } else if (tag.eflags & html4.eflags['RCDATA']) {
      if (h.rcdata) {
        h.rcdata(normalizeRCData(buf), param, continuationMarker,
          continuationMaker(h, parts, p, state, param));
      }
    } else {
      throw new Error('bug');
    }
    return p;
  }

  // at this point, parts[pos-1] is either "<" or "<\/".
  function parseTagAndAttrs(parts, pos) {
    var m = /^([-\w:]+)/.exec(parts[pos]);
    var tag = {};
    tag.name = m[1].toLowerCase();
    tag.eflags = html4.ELEMENTS[tag.name];
    var buf = parts[pos].substr(m[0].length);
    // Find the next '>'.  We optimistically assume this '>' is not in a
    // quoted context, and further down we fix things up if it turns out to
    // be quoted.
    var p = pos + 1;
    var end = parts.length;
    for (; p < end; p++) {
      if (parts[p] === '>') { break; }
      buf += parts[p];
    }
    if (end <= p) { return void 0; }
    var attrs = [];
    while (buf !== '') {
      m = ATTR_RE.exec(buf);
      if (!m) {
        // No attribute found: skip garbage
        buf = buf.replace(/^[\s\S][^a-z\s]*/, '');

      } else if ((m[4] && !m[5]) || (m[6] && !m[7])) {
        // Unterminated quote: slurp to the next unquoted '>'
        var quote = m[4] || m[6];
        var sawQuote = false;
        var abuf = [buf, parts[p++]];
        for (; p < end; p++) {
          if (sawQuote) {
            if (parts[p] === '>') { break; }
          } else if (0 <= parts[p].indexOf(quote)) {
            sawQuote = true;
          }
          abuf.push(parts[p]);
        }
        // Slurp failed: lose the garbage
        if (end <= p) { break; }
        // Otherwise retry attribute parsing
        buf = abuf.join('');
        continue;

      } else {
        // We have an attribute
        var aName = m[1].toLowerCase();
        var aValue = m[2] ? decodeValue(m[3]) : '';
        attrs.push(aName, aValue);
        buf = buf.substr(m[0].length);
      }
    }
    tag.attrs = attrs;
    tag.next = p + 1;
    return tag;
  }

  function decodeValue(v) {
    var q = v.charCodeAt(0);
    if (q === 0x22 || q === 0x27) { // " or '
      v = v.substr(1, v.length - 2);
    }
    return unescapeEntities(stripNULs(v));
  }

  /**
   * Returns a function that strips unsafe tags and attributes from html.
   * @param {function(string, Array.<string>): ?Array.<string>} tagPolicy
   *     A function that takes (tagName, attribs[]), where tagName is a key in
   *     html4.ELEMENTS and attribs is an array of alternating attribute names
   *     and values.  It should return a record (as follows), or null to delete
   *     the element.  It's okay for tagPolicy to modify the attribs array,
   *     but the same array is reused, so it should not be held between calls.
   *     Record keys:
   *        attribs: (required) Sanitized attributes array.
   *        tagName: Replacement tag name.
   * @return {function(string, Array)} A function that sanitizes a string of
   *     HTML and appends result strings to the second argument, an array.
   */
  function makeHtmlSanitizer(tagPolicy) {
    var stack;
    var ignoring;
    var emit = function (text, out) {
      if (!ignoring) { out.push(text); }
    };
    return makeSaxParser({
      'startDoc': function(_) {
        stack = [];
        ignoring = false;
      },
      'startTag': function(tagNameOrig, attribs, out) {
        if (ignoring) { return; }
        if (!html4.ELEMENTS.hasOwnProperty(tagNameOrig)) { return; }
        var eflagsOrig = html4.ELEMENTS[tagNameOrig];
        if (eflagsOrig & html4.eflags['FOLDABLE']) {
          return;
        }

        var decision = tagPolicy(tagNameOrig, attribs);
        if (!decision) {
          ignoring = !(eflagsOrig & html4.eflags['EMPTY']);
          return;
        } else if (typeof decision !== 'object') {
          throw new Error('tagPolicy did not return object (old API?)');
        }
        if ('attribs' in decision) {
          attribs = decision['attribs'];
        } else {
          throw new Error('tagPolicy gave no attribs');
        }
        var eflagsRep;
        var tagNameRep;
        if ('tagName' in decision) {
          tagNameRep = decision['tagName'];
          eflagsRep = html4.ELEMENTS[tagNameRep];
        } else {
          tagNameRep = tagNameOrig;
          eflagsRep = eflagsOrig;
        }
        // TODO(mikesamuel): relying on tagPolicy not to insert unsafe
        // attribute names.

        // If this is an optional-end-tag element and either this element or its
        // previous like sibling was rewritten, then insert a close tag to
        // preserve structure.
        if (eflagsOrig & html4.eflags['OPTIONAL_ENDTAG']) {
          var onStack = stack[stack.length - 1];
          if (onStack && onStack.orig === tagNameOrig &&
              (onStack.rep !== tagNameRep || tagNameOrig !== tagNameRep)) {
                out.push('<\/', onStack.rep, '>');
          }
        }

        if (!(eflagsOrig & html4.eflags['EMPTY'])) {
          stack.push({orig: tagNameOrig, rep: tagNameRep});
        }

        out.push('<', tagNameRep);
        for (var i = 0, n = attribs.length; i < n; i += 2) {
          var attribName = attribs[i],
              value = attribs[i + 1];
          if (value !== null && value !== void 0) {
            out.push(' ', attribName, '="', escapeAttrib(value), '"');
          }
        }
        out.push('>');

        if ((eflagsOrig & html4.eflags['EMPTY'])
            && !(eflagsRep & html4.eflags['EMPTY'])) {
          // replacement is non-empty, synthesize end tag
          out.push('<\/', tagNameRep, '>');
        }
      },
      'endTag': function(tagName, out) {
        if (ignoring) {
          ignoring = false;
          return;
        }
        if (!html4.ELEMENTS.hasOwnProperty(tagName)) { return; }
        var eflags = html4.ELEMENTS[tagName];
        if (!(eflags & (html4.eflags['EMPTY'] | html4.eflags['FOLDABLE']))) {
          var index;
          if (eflags & html4.eflags['OPTIONAL_ENDTAG']) {
            for (index = stack.length; --index >= 0;) {
              var stackElOrigTag = stack[index].orig;
              if (stackElOrigTag === tagName) { break; }
              if (!(html4.ELEMENTS[stackElOrigTag] &
                    html4.eflags['OPTIONAL_ENDTAG'])) {
                // Don't pop non optional end tags looking for a match.
                return;
              }
            }
          } else {
            for (index = stack.length; --index >= 0;) {
              if (stack[index].orig === tagName) { break; }
            }
          }
          if (index < 0) { return; }  // Not opened.
          for (var i = stack.length; --i > index;) {
            var stackElRepTag = stack[i].rep;
            if (!(html4.ELEMENTS[stackElRepTag] &
                  html4.eflags['OPTIONAL_ENDTAG'])) {
              out.push('<\/', stackElRepTag, '>');
            }
          }
          if (index < stack.length) {
            tagName = stack[index].rep;
          }
          stack.length = index;
          out.push('<\/', tagName, '>');
        }
      },
      'pcdata': emit,
      'rcdata': emit,
      'cdata': emit,
      'endDoc': function(out) {
        for (; stack.length; stack.length--) {
          out.push('<\/', stack[stack.length - 1].rep, '>');
        }
      }
    });
  }

  var ALLOWED_URI_SCHEMES = /^(?:https?|mailto|data)$/i;

  function safeUri(uri, effect, ltype, hints, naiveUriRewriter) {
    if (!naiveUriRewriter) { return null; }
    try {
      var parsed = URI.parse('' + uri);
      if (parsed) {
        if (!parsed.hasScheme() ||
            ALLOWED_URI_SCHEMES.test(parsed.getScheme())) {
          var safe = naiveUriRewriter(parsed, effect, ltype, hints);
          return safe ? safe.toString() : null;
        }
      }
    } catch (e) {
      return null;
    }
    return null;
  }

  function log(logger, tagName, attribName, oldValue, newValue) {
    if (!attribName) {
      logger(tagName + " removed", {
        change: "removed",
        tagName: tagName
      });
    }
    if (oldValue !== newValue) {
      var changed = "changed";
      if (oldValue && !newValue) {
        changed = "removed";
      } else if (!oldValue && newValue)  {
        changed = "added";
      }
      logger(tagName + "." + attribName + " " + changed, {
        change: changed,
        tagName: tagName,
        attribName: attribName,
        oldValue: oldValue,
        newValue: newValue
      });
    }
  }

  function lookupAttribute(map, tagName, attribName) {
    var attribKey;
    attribKey = tagName + '::' + attribName;
    if (map.hasOwnProperty(attribKey)) {
      return map[attribKey];
    }
    attribKey = '*::' + attribName;
    if (map.hasOwnProperty(attribKey)) {
      return map[attribKey];
    }
    return void 0;
  }
  function getAttributeType(tagName, attribName) {
    return lookupAttribute(html4.ATTRIBS, tagName, attribName);
  }
  function getLoaderType(tagName, attribName) {
    return lookupAttribute(html4.LOADERTYPES, tagName, attribName);
  }
  function getUriEffect(tagName, attribName) {
    return lookupAttribute(html4.URIEFFECTS, tagName, attribName);
  }

  /**
   * Sanitizes attributes on an HTML tag.
   * @param {string} tagName An HTML tag name in lowercase.
   * @param {Array.<?string>} attribs An array of alternating names and values.
   * @param {?function(?string): ?string} opt_naiveUriRewriter A transform to
   *     apply to URI attributes; it can return a new string value, or null to
   *     delete the attribute.  If unspecified, URI attributes are deleted.
   * @param {function(?string): ?string} opt_nmTokenPolicy A transform to apply
   *     to attributes containing HTML names, element IDs, and space-separated
   *     lists of classes; it can return a new string value, or null to delete
   *     the attribute.  If unspecified, these attributes are kept unchanged.
   * @return {Array.<?string>} The sanitized attributes as a list of alternating
   *     names and values, where a null value means to omit the attribute.
   */
  function sanitizeAttribs(tagName, attribs,
    opt_naiveUriRewriter, opt_nmTokenPolicy, opt_logger) {
    // TODO(felix8a): it's obnoxious that domado duplicates much of this
    // TODO(felix8a): maybe consistently enforce constraints like target=
    for (var i = 0; i < attribs.length; i += 2) {
      var attribName = attribs[i];
      var value = attribs[i + 1];
      var oldValue = value;
      var atype = null, attribKey;
      if ((attribKey = tagName + '::' + attribName,
           html4.ATTRIBS.hasOwnProperty(attribKey)) ||
          (attribKey = '*::' + attribName,
           html4.ATTRIBS.hasOwnProperty(attribKey))) {
        atype = html4.ATTRIBS[attribKey];
      }
      if (atype !== null) {
        switch (atype) {
          case html4.atype['NONE']: break;
          case html4.atype['SCRIPT']:
            value = null;
            if (opt_logger) {
              log(opt_logger, tagName, attribName, oldValue, value);
            }
            break;
          case html4.atype['STYLE']:
            if ('undefined' === typeof parseCssDeclarations) {
              value = null;
              if (opt_logger) {
                log(opt_logger, tagName, attribName, oldValue, value);
	      }
              break;
            }
            var sanitizedDeclarations = [];
            parseCssDeclarations(
                value,
                {
                  declaration: function (property, tokens) {
                    var normProp = property.toLowerCase();
                    var schema = cssSchema[normProp];
                    if (!schema) {
                      return;
                    }
                    sanitizeCssProperty(
                        normProp, schema, tokens,
                        opt_naiveUriRewriter
                        ? function (url) {
                            return safeUri(
                                url, html4.ueffects.SAME_DOCUMENT,
                                html4.ltypes.SANDBOXED,
                                {
                                  "TYPE": "CSS",
                                  "CSS_PROP": normProp
                                }, opt_naiveUriRewriter);
                          }
                        : null);
                    sanitizedDeclarations.push(property + ': ' + tokens.join(' '));
                  }
                });
            value = sanitizedDeclarations.length > 0 ?
              sanitizedDeclarations.join(' ; ') : null;
            if (opt_logger) {
              log(opt_logger, tagName, attribName, oldValue, value);
            }
            break;
          case html4.atype['ID']:
          case html4.atype['IDREF']:
          case html4.atype['IDREFS']:
          case html4.atype['GLOBAL_NAME']:
          case html4.atype['LOCAL_NAME']:
          case html4.atype['CLASSES']:
            value = opt_nmTokenPolicy ? opt_nmTokenPolicy(value) : value;
            if (opt_logger) {
              log(opt_logger, tagName, attribName, oldValue, value);
            }
            break;
          case html4.atype['URI']:
            value = safeUri(value,
              getUriEffect(tagName, attribName),
              getLoaderType(tagName, attribName),
              {
                "TYPE": "MARKUP",
                "XML_ATTR": attribName,
                "XML_TAG": tagName
              }, opt_naiveUriRewriter);
              if (opt_logger) {
              log(opt_logger, tagName, attribName, oldValue, value);
            }
            break;
          case html4.atype['URI_FRAGMENT']:
            if (value && '#' === value.charAt(0)) {
              value = value.substring(1);  // remove the leading '#'
              value = opt_nmTokenPolicy ? opt_nmTokenPolicy(value) : value;
              if (value !== null && value !== void 0) {
                value = '#' + value;  // restore the leading '#'
              }
            } else {
              value = null;
            }
            if (opt_logger) {
              log(opt_logger, tagName, attribName, oldValue, value);
            }
            break;
          default:
            value = null;
            if (opt_logger) {
              log(opt_logger, tagName, attribName, oldValue, value);
            }
            break;
        }
      } else {
        value = null;
        if (opt_logger) {
          log(opt_logger, tagName, attribName, oldValue, value);
        }
      }
      attribs[i + 1] = value;
    }
    return attribs;
  }

  /**
   * Creates a tag policy that omits all tags marked UNSAFE in html4-defs.js
   * and applies the default attribute sanitizer with the supplied policy for
   * URI attributes and NMTOKEN attributes.
   * @param {?function(?string): ?string} opt_naiveUriRewriter A transform to
   *     apply to URI attributes.  If not given, URI attributes are deleted.
   * @param {function(?string): ?string} opt_nmTokenPolicy A transform to apply
   *     to attributes containing HTML names, element IDs, and space-separated
   *     lists of classes.  If not given, such attributes are left unchanged.
   * @return {function(string, Array.<?string>)} A tagPolicy suitable for
   *     passing to html.sanitize.
   */
  function makeTagPolicy(
    opt_naiveUriRewriter, opt_nmTokenPolicy, opt_logger) {
    return function(tagName, attribs) {
      if (!(html4.ELEMENTS[tagName] & html4.eflags['UNSAFE'])) {
        return {
          'attribs': sanitizeAttribs(tagName, attribs,
            opt_naiveUriRewriter, opt_nmTokenPolicy, opt_logger)
        };
      } else {
        if (opt_logger) {
          log(opt_logger, tagName, undefined, undefined, undefined);
        }
      }
    };
  }

  /**
   * Sanitizes HTML tags and attributes according to a given policy.
   * @param {string} inputHtml The HTML to sanitize.
   * @param {function(string, Array.<?string>)} tagPolicy A function that
   *     decides which tags to accept and sanitizes their attributes (see
   *     makeHtmlSanitizer above for details).
   * @return {string} The sanitized HTML.
   */
  function sanitizeWithPolicy(inputHtml, tagPolicy) {
    var outputArray = [];
    makeHtmlSanitizer(tagPolicy)(inputHtml, outputArray);
    return outputArray.join('');
  }

  /**
   * Strips unsafe tags and attributes from HTML.
   * @param {string} inputHtml The HTML to sanitize.
   * @param {?function(?string): ?string} opt_naiveUriRewriter A transform to
   *     apply to URI attributes.  If not given, URI attributes are deleted.
   * @param {function(?string): ?string} opt_nmTokenPolicy A transform to apply
   *     to attributes containing HTML names, element IDs, and space-separated
   *     lists of classes.  If not given, such attributes are left unchanged.
   */
  function sanitize(inputHtml,
    opt_naiveUriRewriter, opt_nmTokenPolicy, opt_logger) {
    var tagPolicy = makeTagPolicy(
      opt_naiveUriRewriter, opt_nmTokenPolicy, opt_logger);
    return sanitizeWithPolicy(inputHtml, tagPolicy);
  }

  // Export both quoted and unquoted names for Closure linkage.
  var html = {};
  html.escapeAttrib = html['escapeAttrib'] = escapeAttrib;
  html.makeHtmlSanitizer = html['makeHtmlSanitizer'] = makeHtmlSanitizer;
  html.makeSaxParser = html['makeSaxParser'] = makeSaxParser;
  html.makeTagPolicy = html['makeTagPolicy'] = makeTagPolicy;
  html.normalizeRCData = html['normalizeRCData'] = normalizeRCData;
  html.sanitize = html['sanitize'] = sanitize;
  html.sanitizeAttribs = html['sanitizeAttribs'] = sanitizeAttribs;
  html.sanitizeWithPolicy = html['sanitizeWithPolicy'] = sanitizeWithPolicy;
  html.unescapeEntities = html['unescapeEntities'] = unescapeEntities;
  return html;
})(html4);

var html_sanitize = html['sanitize'];

// Loosen restrictions of Caja's
// html-sanitizer to allow for styling
html4.ATTRIBS['*::style'] = 0;
html4.ELEMENTS['style'] = 0;
html4.ATTRIBS['a::target'] = 0;
html4.ELEMENTS['video'] = 0;
html4.ATTRIBS['video::src'] = 0;
html4.ATTRIBS['video::poster'] = 0;
html4.ATTRIBS['video::controls'] = 0;
html4.ELEMENTS['audio'] = 0;
html4.ATTRIBS['audio::src'] = 0;
html4.ATTRIBS['video::autoplay'] = 0;
html4.ATTRIBS['video::controls'] = 0;

if (typeof module !== 'undefined') {
    module.exports = html_sanitize;
}

},{}],6:[function(require,module,exports){
module.exports={
  "author": "Mapbox",
  "name": "mapbox.js",
  "description": "mapbox javascript api",
  "version": "3.1.1",
  "homepage": "http://mapbox.com/",
  "repository": {
    "type": "git",
    "url": "git://github.com/mapbox/mapbox.js.git"
  },
  "main": "src/index.js",
  "dependencies": {
    "corslite": "0.0.6",
    "isarray": "0.0.1",
    "leaflet": "1.0.2",
    "mustache": "2.2.1",
    "sanitize-caja": "0.1.4"
  },
  "scripts": {
    "test": "eslint --no-eslintrc -c .eslintrc src && phantomjs node_modules/mocha-phantomjs-core/mocha-phantomjs-core.js test/index.html"
  },
  "license": "BSD-3-Clause",
  "devDependencies": {
    "browserify": "^13.0.0",
    "clean-css": "~2.0.7",
    "cz-conventional-changelog": "1.2.0",
    "eslint": "^0.23.0",
    "expect.js": "0.3.1",
    "happen": "0.1.3",
    "leaflet-fullscreen": "0.0.4",
    "leaflet-hash": "0.2.1",
    "marked": "~0.3.0",
    "minifyify": "^6.1.0",
    "minimist": "0.0.5",
    "mocha": "2.4.5",
    "mocha-phantomjs-core": "2.0.1",
    "phantomjs-prebuilt": "2.1.12",
    "sinon": "1.10.2"
  },
  "optionalDependencies": {},
  "engines": {
    "node": "*"
  },
  "config": {
    "commitizen": {
      "path": "./node_modules/cz-conventional-changelog"
    }
  }
}

},{}],7:[function(require,module,exports){
'use strict';

module.exports = {
    HTTP_URL: 'http://a.tiles.mapbox.com/v4',
    HTTPS_URL: 'https://a.tiles.mapbox.com/v4',
    FORCE_HTTPS: false,
    REQUIRE_ACCESS_TOKEN: true
};

},{}],8:[function(require,module,exports){
'use strict';

var util = require('./util'),
    format_url = require('./format_url'),
    request = require('./request'),
    marker = require('./marker'),
    simplestyle = require('./simplestyle');

// # featureLayer
//
// A layer of features, loaded from Mapbox or else. Adds the ability
// to reset features, filter them, and load them from a GeoJSON URL.
var FeatureLayer = L.FeatureGroup.extend({
    options: {
        filter: function() { return true; },
        sanitizer: require('sanitize-caja'),
        style: simplestyle.style,
        popupOptions: { closeButton: false }
    },

    initialize: function(_, options) {
        L.setOptions(this, options);

        this._layers = {};

        if (typeof _ === 'string') {
            util.idUrl(_, this);
        // javascript object of TileJSON data
        } else if (_ && typeof _ === 'object') {
            this.setGeoJSON(_);
        }
    },

    setGeoJSON: function(_) {
        this._geojson = _;
        this.clearLayers();
        this._initialize(_);
        return this;
    },

    getGeoJSON: function() {
        return this._geojson;
    },

    loadURL: function(url) {
        if (this._request && 'abort' in this._request) this._request.abort();
        this._request = request(url, L.bind(function(err, json) {
            this._request = null;
            if (err && err.type !== 'abort') {
                util.log('could not load features at ' + url);
                this.fire('error', {error: err});
            } else if (json) {
                this.setGeoJSON(json);
                this.fire('ready');
            }
        }, this));
        return this;
    },

    loadID: function(id) {
        return this.loadURL(format_url('/v4/' + id + '/features.json', this.options.accessToken));
    },

    setFilter: function(_) {
        this.options.filter = _;
        if (this._geojson) {
            this.clearLayers();
            this._initialize(this._geojson);
        }
        return this;
    },

    getFilter: function() {
        return this.options.filter;
    },

    _initialize: function(json) {
        var features = L.Util.isArray(json) ? json : json.features,
            i, len;

        if (features) {
            for (i = 0, len = features.length; i < len; i++) {
                // Only add this if geometry or geometries are set and not null
                if (features[i].geometries || features[i].geometry || features[i].features) {
                    this._initialize(features[i]);
                }
            }
        } else if (this.options.filter(json)) {

            var opts = {accessToken: this.options.accessToken},
                pointToLayer = this.options.pointToLayer || function(feature, latlon) {
                  return marker.style(feature, latlon, opts);
                },
                layer = L.GeoJSON.geometryToLayer(json, {
                    pointToLayer: pointToLayer
                }),
                popupHtml = marker.createPopup(json, this.options.sanitizer),
                style = this.options.style,
                defaultStyle = style === simplestyle.style;

            if (style && 'setStyle' in layer &&
                // if the style method is the simplestyle default, then
                // never style L.Circle or L.CircleMarker because
                // simplestyle has no rules over them, only over geometry
                // primitives directly from GeoJSON
                (!(defaultStyle && (layer instanceof L.Circle ||
                  layer instanceof L.CircleMarker)))) {
                if (typeof style === 'function') {
                    style = style(json);
                }
                layer.setStyle(style);
            }

            layer.feature = json;

            if (popupHtml) {
                layer.bindPopup(popupHtml, this.options.popupOptions);
            }

            this.addLayer(layer);
        }
    }
});

module.exports.FeatureLayer = FeatureLayer;

module.exports.featureLayer = function(_, options) {
    return new FeatureLayer(_, options);
};

},{"./format_url":10,"./marker":21,"./request":22,"./simplestyle":24,"./util":27,"sanitize-caja":4}],9:[function(require,module,exports){
'use strict';

var Feedback = L.Class.extend({
    includes: L.Mixin.Events,
    data: {},
    record: function(data) {
        L.extend(this.data, data);
        this.fire('change');
    }
});

module.exports = new Feedback();

},{}],10:[function(require,module,exports){
'use strict';

var config = require('./config'),
    version = require('../package.json').version;

module.exports = function(path, accessToken) {
    accessToken = accessToken || L.mapbox.accessToken;

    if (!accessToken && config.REQUIRE_ACCESS_TOKEN) {
        throw new Error('An API access token is required to use Mapbox.js. ' +
            'See https://www.mapbox.com/mapbox.js/api/v' + version + '/api-access-tokens/');
    }

    var url = (document.location.protocol === 'https:' || config.FORCE_HTTPS) ? config.HTTPS_URL : config.HTTP_URL;
    url = url.replace(/\/v4$/, '');
    url += path;

    if (config.REQUIRE_ACCESS_TOKEN) {
        if (accessToken[0] === 's') {
            throw new Error('Use a public access token (pk.*) with Mapbox.js, not a secret access token (sk.*). ' +
                'See https://www.mapbox.com/mapbox.js/api/v' + version + '/api-access-tokens/');
        }

        url += url.indexOf('?') !== -1 ? '&access_token=' : '?access_token=';
        url += accessToken;
    }

    return url;
};

module.exports.tileJSON = function(urlOrMapID, accessToken) {

    if (urlOrMapID.indexOf('mapbox://styles') === 0) {
        throw new Error('Styles created with Mapbox Studio need to be used with ' +
            'L.mapbox.styleLayer, not L.mapbox.tileLayer');
    }

    if (urlOrMapID.indexOf('/') !== -1)
        return urlOrMapID;

    var url = module.exports('/v4/' + urlOrMapID + '.json', accessToken);

    // TileJSON requests need a secure flag appended to their URLs so
    // that the server knows to send SSL-ified resource references.
    if (url.indexOf('https') === 0)
        url += '&secure';

    return url;
};


module.exports.style = function(styleURL, accessToken) {
    if (styleURL.indexOf('mapbox://styles/') === -1) throw new Error('Incorrectly formatted Mapbox style at ' + styleURL);

    var ownerIDStyle = styleURL.split('mapbox://styles/')[1];
    var url = module.exports('/styles/v1/' + ownerIDStyle, accessToken)
        .replace('http://', 'https://');

    return url;
};

},{"../package.json":6,"./config":7}],11:[function(require,module,exports){
'use strict';

var isArray = require('isarray'),
    util = require('./util'),
    format_url = require('./format_url'),
    feedback = require('./feedback'),
    request = require('./request');

// Low-level geocoding interface - wraps specific API calls and their
// return values.
module.exports = function(url, options) {
    if (!options) options = {};
    var geocoder = {};

    util.strict(url, 'string');

    if (url.indexOf('/') === -1) {
        url = format_url('/geocoding/v5/' + url + '/{query}.json', options.accessToken, 5);
    }

    function roundTo(latLng, precision) {
        var mult = Math.pow(10, precision);
        latLng.lat = Math.round(latLng.lat * mult) / mult;
        latLng.lng = Math.round(latLng.lng * mult) / mult;
        return latLng;
    }

    geocoder.getURL = function() {
        return url;
    };

    geocoder.queryURL = function(_) {
        var isObject = !(isArray(_) || typeof _ === 'string'),
            query = isObject ? _.query : _;

        if (isArray(query)) {
            var parts = [];
            for (var i = 0; i < query.length; i++) {
                parts[i] = encodeURIComponent(query[i]);
            }
            query = parts.join(';');
        } else {
            query = encodeURIComponent(query);
        }

        feedback.record({ geocoding: query });

        var url = L.Util.template(geocoder.getURL(), {query: query});

        if (isObject) {
            if (_.types) {
                if (isArray(_.types)) {
                    url += '&types=' + _.types.join();
                } else {
                    url += '&types=' + _.types;
                }
            }

            if (_.country) {
                if (isArray(_.country)) {
                    url += '&country=' + _.country.join();
                } else {
                    url += '&country=' + _.country;
                }
            }

            if (_.bbox) {
                if (isArray(_.bbox)) {
                    url += '&bbox=' + _.bbox.join();
                } else {
                    url += '&bbox=' + _.bbox;
                }
            }

            if (_.proximity) {
                var proximity = roundTo(L.latLng(_.proximity), 3);
                url += '&proximity=' + proximity.lng + ',' + proximity.lat;
            }

            if (typeof _.autocomplete === 'boolean') {
                url += '&autocomplete=' + _.autocomplete;
            }
        }

        return url;
    };

    geocoder.query = function(_, callback) {
        util.strict(callback, 'function');

        request(geocoder.queryURL(_), function(err, json) {
            if (json && (json.length || json.features)) {
                var res = {
                    results: json
                };
                if (json.features && json.features.length) {
                    res.latlng = [
                        json.features[0].center[1],
                        json.features[0].center[0]];

                    if (json.features[0].bbox) {
                        res.bounds = json.features[0].bbox;
                        res.lbounds = util.lbounds(res.bounds);
                    }
                }
                callback(null, res);
            } else callback(err || true);
        });

        return geocoder;
    };

    // a reverse geocode:
    //
    //  geocoder.reverseQuery([80, 20])
    geocoder.reverseQuery = function(_, callback) {
        var q = '';

        // sort through different ways people represent lat and lon pairs
        function normalize(x) {
            var latLng;
            if (x.lat !== undefined && x.lng !== undefined) {
                latLng = L.latLng(x.lat, x.lng);
            } else if (x.lat !== undefined && x.lon !== undefined) {
                latLng = L.latLng(x.lat, x.lon);
            } else {
                latLng = L.latLng(x[1], x[0]);
            }
            latLng = roundTo(latLng, 5);
            return latLng.lng + ',' + latLng.lat;
        }

        if (_.length && _[0].length) {
            for (var i = 0, pts = []; i < _.length; i++) {
                pts.push(normalize(_[i]));
            }
            q = pts.join(';');
        } else {
            q = normalize(_);
        }

        request(geocoder.queryURL(q), function(err, json) {
            callback(err, json);
        });

        return geocoder;
    };

    return geocoder;
};

},{"./feedback":9,"./format_url":10,"./request":22,"./util":27,"isarray":2}],12:[function(require,module,exports){
'use strict';

var geocoder = require('./geocoder'),
    util = require('./util');

var GeocoderControl = L.Control.extend({
    includes: L.Mixin.Events,

    options: {
        proximity: true,
        position: 'topleft',
        pointZoom: 16,
        keepOpen: false,
        autocomplete: false,
        queryOptions: {}
    },

    initialize: function(_, options) {
        L.Util.setOptions(this, options);
        this.setURL(_);
        this._updateSubmit = L.bind(this._updateSubmit, this);
        this._updateAutocomplete = L.bind(this._updateAutocomplete, this);
        this._chooseResult = L.bind(this._chooseResult, this);
    },

    setURL: function(_) {
        this.geocoder = geocoder(_, {
            accessToken: this.options.accessToken
        });
        return this;
    },

    getURL: function() {
        return this.geocoder.getURL();
    },

    setID: function(_) {
        return this.setURL(_);
    },

    setTileJSON: function(_) {
        return this.setURL(_.geocoder);
    },

    _toggle: function(e) {
        if (e) L.DomEvent.stop(e);
        if (L.DomUtil.hasClass(this._container, 'active')) {
            L.DomUtil.removeClass(this._container, 'active');
            this._results.innerHTML = '';
            this._input.blur();
        } else {
            L.DomUtil.addClass(this._container, 'active');
            this._input.focus();
            this._input.select();
        }
    },

    _closeIfOpen: function() {
        if (L.DomUtil.hasClass(this._container, 'active') &&
            !this.options.keepOpen) {
            L.DomUtil.removeClass(this._container, 'active');
            this._results.innerHTML = '';
            this._input.blur();
        }
    },

    onAdd: function(map) {

        var container = L.DomUtil.create('div', 'leaflet-control-mapbox-geocoder leaflet-bar leaflet-control'),
            link = L.DomUtil.create('a', 'leaflet-control-mapbox-geocoder-toggle mapbox-icon mapbox-icon-geocoder', container),
            results = L.DomUtil.create('div', 'leaflet-control-mapbox-geocoder-results', container),
            wrap = L.DomUtil.create('div', 'leaflet-control-mapbox-geocoder-wrap', container),
            form = L.DomUtil.create('form', 'leaflet-control-mapbox-geocoder-form', wrap),
            input = L.DomUtil.create('input', '', form);

        link.href = '#';
        link.innerHTML = '&nbsp;';

        input.type = 'text';
        input.setAttribute('placeholder', 'Search');

        L.DomEvent.addListener(form, 'submit', this._geocode, this);
        L.DomEvent.addListener(input, 'keyup', this._autocomplete, this);
        L.DomEvent.disableClickPropagation(container);

        this._map = map;
        this._results = results;
        this._input = input;
        this._form = form;

        if (this.options.keepOpen) {
            L.DomUtil.addClass(container, 'active');
        } else {
            this._map.on('click', this._closeIfOpen, this);
            L.DomEvent.addListener(link, 'click', this._toggle, this);
        }

        return container;
    },

    _updateSubmit: function(err, resp) {
        L.DomUtil.removeClass(this._container, 'searching');
        this._results.innerHTML = '';
        if (err || !resp) {
            this.fire('error', {error: err});
        } else {
            var features = [];
            if (resp.results && resp.results.features) {
                features = resp.results.features;
            }
            if (features.length === 1) {
                this.fire('autoselect', { feature: features[0] });
                this.fire('found', {results: resp.results});
                this._chooseResult(features[0]);
                this._closeIfOpen();
            } else if (features.length > 1) {
                this.fire('found', {results: resp.results});
                this._displayResults(features);
            } else {
                this.fire('notfound');
                this._displayResults(features);
            }
        }
    },

    _updateAutocomplete: function(err, resp) {
        this._results.innerHTML = '';
        if (err || !resp) {
            this.fire('error', {error: err});
        } else {
            var features = [];
            if (resp.results && resp.results.features) {
                features = resp.results.features;
            }
            if (features.length) {
                this.fire('found', {results: resp.results});
            } else {
                this.fire('notfound');
            }
            this._displayResults(features);
        }
    },

    _displayResults: function(features) {
        for (var i = 0, l = Math.min(features.length, 5); i < l; i++) {
            var feature = features[i];
            var name = feature.place_name;
            if (!name.length) continue;

            var r = L.DomUtil.create('a', '', this._results);
            var text = ('innerText' in r) ? 'innerText' : 'textContent';
            r[text] = name;
            r.setAttribute('title', name);
            r.href = '#';

            (L.bind(function(feature) {
                L.DomEvent.addListener(r, 'click', function(e) {
                    this._chooseResult(feature);
                    L.DomEvent.stop(e);
                    this.fire('select', { feature: feature });
                }, this);
            }, this))(feature);
        }
        if (features.length > 5) {
            var outof = L.DomUtil.create('span', '', this._results);
            outof.innerHTML = 'Top 5 of ' + features.length + '  results';
        }
    },

    _chooseResult: function(result) {
        if (result.bbox) {
            this._map.fitBounds(util.lbounds(result.bbox));
        } else if (result.center) {
            this._map.setView([result.center[1], result.center[0]], (this._map.getZoom() === undefined) ?
                this.options.pointZoom :
                Math.max(this._map.getZoom(), this.options.pointZoom));
        }
    },

    _geocode: function(e) {
        L.DomEvent.preventDefault(e);
        if (this._input.value === '') return this._updateSubmit();
        L.DomUtil.addClass(this._container, 'searching');
        this.geocoder.query(L.Util.extend({
            query: this._input.value,
            proximity: this.options.proximity ? this._map.getCenter() : false
        }, this.options.queryOptions), this._updateSubmit);
    },

    _autocomplete: function() {
        if (!this.options.autocomplete) return;
        if (this._input.value === '') return this._updateAutocomplete();
        this.geocoder.query(L.Util.extend({
            query: this._input.value,
            proximity: this.options.proximity ? this._map.getCenter() : false
        }, this.options.queryOptions), this._updateAutocomplete);
    }
});

module.exports.GeocoderControl = GeocoderControl;

module.exports.geocoderControl = function(_, options) {
    return new GeocoderControl(_, options);
};

},{"./geocoder":11,"./util":27}],13:[function(require,module,exports){
'use strict';

function utfDecode(c) {
    if (c >= 93) c--;
    if (c >= 35) c--;
    return c - 32;
}

module.exports = function(data) {
    return function(x, y) {
        if (!data) return;
        var idx = utfDecode(data.grid[y].charCodeAt(x)),
            key = data.keys[idx];
        return data.data[key];
    };
};

},{}],14:[function(require,module,exports){
'use strict';

var util = require('./util'),
    Mustache = require('mustache');

var GridControl = L.Control.extend({

    options: {
        pinnable: true,
        follow: false,
        sanitizer: require('sanitize-caja'),
        touchTeaser: true,
        location: true
    },

    _currentContent: '',

    // pinned means that this control is on a feature and the user has likely
    // clicked. pinned will not become false unless the user clicks off
    // of the feature onto another or clicks x
    _pinned: false,

    initialize: function(_, options) {
        L.Util.setOptions(this, options);
        util.strict_instance(_, L.Class, 'L.mapbox.gridLayer');
        this._layer = _;
    },

    setTemplate: function(template) {
        util.strict(template, 'string');
        this.options.template = template;
        return this;
    },

    _template: function(format, data) {
        if (!data) return;
        var template = this.options.template || this._layer.getTileJSON().template;
        if (template) {
            var d = {};
            d['__' + format + '__'] = true;
            return this.options.sanitizer(
                Mustache.to_html(template, L.extend(d, data)));
        }
    },

    // change the content of the tooltip HTML if it has changed, otherwise
    // noop
    _show: function(content, o) {
        if (content === this._currentContent) return;

        this._currentContent = content;

        if (this.options.follow) {
            this._popup.setContent(content)
                .setLatLng(o.latLng);
            if (this._map._popup !== this._popup) this._popup.openOn(this._map);
        } else {
            this._container.style.display = 'block';
            this._contentWrapper.innerHTML = content;
        }
    },

    hide: function() {
        this._pinned = false;
        this._currentContent = '';

        this._map.closePopup();
        this._container.style.display = 'none';
        this._contentWrapper.innerHTML = '';

        L.DomUtil.removeClass(this._container, 'closable');

        return this;
    },

    _mouseover: function(o) {
        if (o.data) {
            L.DomUtil.addClass(this._map._container, 'map-clickable');
        } else {
            L.DomUtil.removeClass(this._map._container, 'map-clickable');
        }

        if (this._pinned) return;

        var content = this._template('teaser', o.data);
        if (content) {
            this._show(content, o);
        } else {
            this.hide();
        }
    },

    _mousemove: function(o) {
        if (this._pinned) return;
        if (!this.options.follow) return;

        this._popup.setLatLng(o.latLng);
    },

    _navigateTo: function(url) {
        window.top.location.href = url;
    },

    _click: function(o) {

        var location_formatted = this._template('location', o.data);
        if (this.options.location && location_formatted &&
            location_formatted.search(/^https?:/) === 0) {
            return this._navigateTo(this._template('location', o.data));
        }

        if (!this.options.pinnable) return;

        var content = this._template('full', o.data);

        if (!content && this.options.touchTeaser && L.Browser.touch) {
            content = this._template('teaser', o.data);
        }

        if (content) {
            L.DomUtil.addClass(this._container, 'closable');
            this._pinned = true;
            this._show(content, o);
        } else if (this._pinned) {
            L.DomUtil.removeClass(this._container, 'closable');
            this._pinned = false;
            this.hide();
        }
    },

    _onPopupClose: function() {
        this._currentContent = null;
        this._pinned = false;
    },

    _createClosebutton: function(container, fn) {
        var link = L.DomUtil.create('a', 'close', container);

        link.innerHTML = 'close';
        link.href = '#';
        link.title = 'close';

        L.DomEvent
            .on(link, 'click', L.DomEvent.stopPropagation)
            .on(link, 'mousedown', L.DomEvent.stopPropagation)
            .on(link, 'dblclick', L.DomEvent.stopPropagation)
            .on(link, 'click', L.DomEvent.preventDefault)
            .on(link, 'click', fn, this);

        return link;
    },

    onAdd: function(map) {
        this._map = map;

        var className = 'leaflet-control-grid map-tooltip',
            container = L.DomUtil.create('div', className),
            contentWrapper = L.DomUtil.create('div', 'map-tooltip-content');

        // hide the container element initially
        container.style.display = 'none';
        this._createClosebutton(container, this.hide);
        container.appendChild(contentWrapper);

        this._contentWrapper = contentWrapper;
        this._popup = new L.Popup({ autoPan: false, closeOnClick: false });

        map.on('popupclose', this._onPopupClose, this);

        L.DomEvent
            .disableClickPropagation(container)
            // allow people to scroll tooltips with mousewheel
            .addListener(container, 'mousewheel', L.DomEvent.stopPropagation);

        this._layer
            .on('mouseover', this._mouseover, this)
            .on('mousemove', this._mousemove, this)
            .on('click', this._click, this);

        return container;
    },

    onRemove: function (map) {

        map.off('popupclose', this._onPopupClose, this);

        this._layer
            .off('mouseover', this._mouseover, this)
            .off('mousemove', this._mousemove, this)
            .off('click', this._click, this);
    }
});

module.exports.GridControl = GridControl;

module.exports.gridControl = function(_, options) {
    return new GridControl(_, options);
};

},{"./util":27,"mustache":3,"sanitize-caja":4}],15:[function(require,module,exports){
'use strict';

var util = require('./util'),
    request = require('./request'),
    grid = require('./grid');

// forked from danzel/L.UTFGrid
var GridLayer = L.Layer.extend({
    includes: [require('./load_tilejson')],

    options: {
        template: function() { return ''; }
    },

    _mouseOn: null,
    _tilejson: {},
    _cache: {},

    initialize: function(_, options) {
        L.Util.setOptions(this, options);
        this._loadTileJSON(_);
    },

    _setTileJSON: function(json) {
        util.strict(json, 'object');

        L.extend(this.options, {
            grids: json.grids,
            minZoom: json.minzoom,
            maxZoom: json.maxzoom,
            bounds: json.bounds && util.lbounds(json.bounds)
        });

        this._tilejson = json;
        this._cache = {};
        this._update();

        return this;
    },

    getTileJSON: function() {
        return this._tilejson;
    },

    active: function() {
        return !!(this._map && this.options.grids && this.options.grids.length);
    },

    onAdd: function(map) {
        this._map = map;
        this._update();

        this._map
            .on('click', this._click, this)
            .on('mousemove', this._move, this)
            .on('moveend', this._update, this);
    },

    onRemove: function() {
        this._map
            .off('click', this._click, this)
            .off('mousemove', this._move, this)
            .off('moveend', this._update, this);
    },

    getData: function(latlng, callback) {
        if (!this.active()) return;

        var map = this._map,
            point = map.project(latlng.wrap()),
            tileSize = 256,
            resolution = 4,
            x = Math.floor(point.x / tileSize),
            y = Math.floor(point.y / tileSize),
            max = map.options.crs.scale(map.getZoom()) / tileSize;

        x = (x + max) % max;
        y = (y + max) % max;

        this._getTile(map.getZoom(), x, y, function(grid) {
            var gridX = Math.floor((point.x - (x * tileSize)) / resolution),
                gridY = Math.floor((point.y - (y * tileSize)) / resolution);

            callback(grid(gridX, gridY));
        });

        return this;
    },

    _click: function(e) {
        this.getData(e.latlng, L.bind(function(data) {
            this.fire('click', {
                latLng: e.latlng,
                data: data
            });
        }, this));
    },

    _move: function(e) {
        this.getData(e.latlng, L.bind(function(data) {
            if (data !== this._mouseOn) {
                if (this._mouseOn) {
                    this.fire('mouseout', {
                        latLng: e.latlng,
                        data: this._mouseOn
                    });
                }

                this.fire('mouseover', {
                    latLng: e.latlng,
                    data: data
                });

                this._mouseOn = data;
            } else {
                this.fire('mousemove', {
                    latLng: e.latlng,
                    data: data
                });
            }
        }, this));
    },

    _getTileURL: function(tilePoint) {
        var urls = this.options.grids,
            index = (tilePoint.x + tilePoint.y) % urls.length,
            url = urls[index];

        return L.Util.template(url, tilePoint);
    },

    // Load up all required json grid files
    _update: function() {
        if (!this.active()) return;

        var bounds = this._map.getPixelBounds(),
            z = this._map.getZoom(),
            tileSize = 256;

        if (z > this.options.maxZoom || z < this.options.minZoom) return;

        var tileBounds = L.bounds(
                bounds.min.divideBy(tileSize)._floor(),
                bounds.max.divideBy(tileSize)._floor()),
            max = this._map.options.crs.scale(z) / tileSize;

        for (var x = tileBounds.min.x; x <= tileBounds.max.x; x++) {
            for (var y = tileBounds.min.y; y <= tileBounds.max.y; y++) {
                // x wrapped
                this._getTile(z, ((x % max) + max) % max, ((y % max) + max) % max);
            }
        }
    },

    _getTile: function(z, x, y, callback) {
        var key = z + '_' + x + '_' + y,
            tilePoint = L.point(x, y);

        tilePoint.z = z;

        if (!this._tileShouldBeLoaded(tilePoint)) {
            return;
        }

        if (key in this._cache) {
            if (!callback) return;

            if (typeof this._cache[key] === 'function') {
                callback(this._cache[key]); // Already loaded
            } else {
                this._cache[key].push(callback); // Pending
            }

            return;
        }

        this._cache[key] = [];

        if (callback) {
            this._cache[key].push(callback);
        }

        request(this._getTileURL(tilePoint), L.bind(function(err, json) {
            var callbacks = this._cache[key];
            this._cache[key] = grid(json);
            for (var i = 0; i < callbacks.length; ++i) {
                callbacks[i](this._cache[key]);
            }
        }, this));
    },

    _tileShouldBeLoaded: function(tilePoint) {
        if (tilePoint.z > this.options.maxZoom || tilePoint.z < this.options.minZoom) {
            return false;
        }

        if (this.options.bounds) {
            var tileSize = 256,
                nwPoint = tilePoint.multiplyBy(tileSize),
                sePoint = nwPoint.add(new L.Point(tileSize, tileSize)),
                nw = this._map.unproject(nwPoint),
                se = this._map.unproject(sePoint),
                bounds = new L.LatLngBounds([nw, se]);

            if (!this.options.bounds.intersects(bounds)) {
                return false;
            }
        }

        return true;
    }
});

module.exports.GridLayer = GridLayer;

module.exports.gridLayer = function(_, options) {
    return new GridLayer(_, options);
};

},{"./grid":13,"./load_tilejson":17,"./request":22,"./util":27}],16:[function(require,module,exports){
'use strict';

var LegendControl = L.Control.extend({

    options: {
        position: 'bottomright',
        sanitizer: require('sanitize-caja')
    },

    initialize: function(options) {
        L.setOptions(this, options);
        this._legends = {};
    },

    onAdd: function() {
        this._container = L.DomUtil.create('div', 'map-legends wax-legends');
        L.DomEvent.disableClickPropagation(this._container);

        this._update();

        return this._container;
    },

    addLegend: function(text) {
        if (!text) { return this; }

        if (!this._legends[text]) {
            this._legends[text] = 0;
        }

        this._legends[text]++;
        return this._update();
    },

    removeLegend: function(text) {
        if (!text) { return this; }
        if (this._legends[text]) this._legends[text]--;
        return this._update();
    },

    _update: function() {
        if (!this._map) { return this; }

        this._container.innerHTML = '';
        var hide = 'none';

        for (var i in this._legends) {
            if (this._legends.hasOwnProperty(i) && this._legends[i]) {
                var div = L.DomUtil.create('div', 'map-legend wax-legend', this._container);
                div.innerHTML = this.options.sanitizer(i);
                hide = 'block';
            }
        }

        // hide the control entirely unless there is at least one legend;
        // otherwise there will be a small grey blemish on the map.
        this._container.style.display = hide;

        return this;
    }
});

module.exports.LegendControl = LegendControl;

module.exports.legendControl = function(options) {
    return new LegendControl(options);
};

},{"sanitize-caja":4}],17:[function(require,module,exports){
'use strict';

var request = require('./request'),
    format_url = require('./format_url'),
    util = require('./util');

module.exports = {
    _loadTileJSON: function(_) {
        if (typeof _ === 'string') {
            _ = format_url.tileJSON(_, this.options && this.options.accessToken);
            request(_, L.bind(function(err, json) {
                if (err) {
                    util.log('could not load TileJSON at ' + _);
                    this.fire('error', {error: err});
                } else if (json) {
                    this._setTileJSON(json);
                    this.fire('ready');
                }
            }, this));
        } else if (_ && typeof _ === 'object') {
            this._setTileJSON(_);
        }
    }
};

},{"./format_url":10,"./request":22,"./util":27}],18:[function(require,module,exports){
'use strict';

var tileLayer = require('./tile_layer').tileLayer,
    featureLayer = require('./feature_layer').featureLayer,
    gridLayer = require('./grid_layer').gridLayer,
    gridControl = require('./grid_control').gridControl,
    shareControl = require('./share_control').shareControl,
    legendControl = require('./legend_control').legendControl,
    mapboxLogoControl = require('./mapbox_logo').mapboxLogoControl,
    feedback = require('./feedback');

function withAccessToken(options, accessToken) {
    if (!accessToken || options.accessToken)
        return options;
    return L.extend({accessToken: accessToken}, options);
}

var LMap = L.Map.extend({
    includes: [require('./load_tilejson')],

    options: {
        tileLayer: {},
        featureLayer: {},
        gridLayer: {},
        legendControl: {},
        gridControl: {},
        shareControl: false,
        sanitizer: require('sanitize-caja')
    },

    _tilejson: {},

    initialize: function(element, _, options) {

        L.Map.prototype.initialize.call(this, element,
            L.extend({}, L.Map.prototype.options, options));

        // Disable the default 'Leaflet' text
        if (this.attributionControl) {
            this.attributionControl.setPrefix('');

            var compact = this.options.attributionControl.compact;
            // Set a compact display if map container width is < 640 or
            // compact is set to `true` in attributionControl options.
            if (compact || (compact !== false && this._container.offsetWidth <= 640)) {
                L.DomUtil.addClass(this.attributionControl._container, 'leaflet-compact-attribution');
            }

            if (compact === undefined) {
                this.on('resize', function() {
                    if (this._container.offsetWidth > 640) {
                        L.DomUtil.removeClass(this.attributionControl._container, 'leaflet-compact-attribution');
                    } else {
                        L.DomUtil.addClass(this.attributionControl._container, 'leaflet-compact-attribution');
                    }
                });
            }
        }

        if (this.options.tileLayer) {
            this.tileLayer = tileLayer(undefined,
                withAccessToken(this.options.tileLayer, this.options.accessToken));
            this.addLayer(this.tileLayer);
        }

        if (this.options.featureLayer) {
            this.featureLayer = featureLayer(undefined,
                withAccessToken(this.options.featureLayer, this.options.accessToken));
            this.addLayer(this.featureLayer);
        }

        if (this.options.gridLayer) {
            this.gridLayer = gridLayer(undefined,
                withAccessToken(this.options.gridLayer, this.options.accessToken));
            this.addLayer(this.gridLayer);
        }

        if (this.options.gridLayer && this.options.gridControl) {
            this.gridControl = gridControl(this.gridLayer, this.options.gridControl);
            this.addControl(this.gridControl);
        }

        if (this.options.legendControl) {
            this.legendControl = legendControl(this.options.legendControl);
            this.addControl(this.legendControl);
        }

        if (this.options.shareControl) {
            this.shareControl = shareControl(undefined,
                withAccessToken(this.options.shareControl, this.options.accessToken));
            this.addControl(this.shareControl);
        }

        this._mapboxLogoControl = mapboxLogoControl(this.options.mapboxLogoControl);
        this.addControl(this._mapboxLogoControl);

        this._loadTileJSON(_);

        this.on('layeradd', this._onLayerAdd, this)
            .on('layerremove', this._onLayerRemove, this)
            .on('moveend', this._updateMapFeedbackLink, this);

        this.whenReady(function () {
            feedback.on('change', this._updateMapFeedbackLink, this);
        });

        this.on('unload', function () {
            feedback.off('change', this._updateMapFeedbackLink, this);
        });
    },

    // use a javascript object of tilejson data to configure this layer
    _setTileJSON: function(_) {
        this._tilejson = _;
        this._initialize(_);
        return this;
    },

    getTileJSON: function() {
        return this._tilejson;
    },

    _initialize: function(json) {
        if (this.tileLayer) {
            this.tileLayer._setTileJSON(json);
            this._updateLayer(this.tileLayer);
        }

        if (this.featureLayer && !this.featureLayer.getGeoJSON() && json.data && json.data[0]) {
            this.featureLayer.loadURL(json.data[0]);
        }

        if (this.gridLayer) {
            this.gridLayer._setTileJSON(json);
            this._updateLayer(this.gridLayer);
        }

        if (this.legendControl && json.legend) {
            this.legendControl.addLegend(json.legend);
        }

        if (this.shareControl) {
            this.shareControl._setTileJSON(json);
        }

        this._mapboxLogoControl._setTileJSON(json);

        if (!this._loaded && json.center) {
            var zoom = this.getZoom() !== undefined ? this.getZoom() : json.center[2],
                center = L.latLng(json.center[1], json.center[0]);

            this.setView(center, zoom);
        }
    },

    _updateMapFeedbackLink: function() {
        if (!this._controlContainer.getElementsByClassName) return;
        var link = this._controlContainer.getElementsByClassName('mapbox-improve-map');
        if (link.length && this._loaded) {
            var center = this.getCenter().wrap();
            var tilejson = this._tilejson || {};
            var id = tilejson.id || '';

            var hash = '#' + id + '/' +
                center.lng.toFixed(3) + '/' +
                center.lat.toFixed(3) + '/' +
                this.getZoom();

            for (var key in feedback.data) {
                hash += '/' + key + '=' + feedback.data[key];
            }

            for (var i = 0; i < link.length; i++) {
                link[i].hash = hash;
            }
        }
    },

    _onLayerAdd: function(e) {
        if ('on' in e.layer) {
            e.layer.on('ready', this._onLayerReady, this);
        }
        window.setTimeout(L.bind(this._updateMapFeedbackLink, this), 0); // Update after attribution control resets the HTML.
    },

    _onLayerRemove: function(e) {
        if ('on' in e.layer) {
            e.layer.off('ready', this._onLayerReady, this);
        }
        window.setTimeout(L.bind(this._updateMapFeedbackLink, this), 0); // Update after attribution control resets the HTML.
    },

    _onLayerReady: function(e) {
        this._updateLayer(e.target);
    },

    _updateLayer: function(layer) {
        if (!layer.options) return;

        if (this.attributionControl && this._loaded && layer.getAttribution) {
            this.attributionControl.addAttribution(layer.getAttribution());
        }

        if (!(L.stamp(layer) in this._zoomBoundLayers) &&
                (layer.options.maxZoom || layer.options.minZoom)) {
            this._zoomBoundLayers[L.stamp(layer)] = layer;
        }

        this._updateMapFeedbackLink();
        this._updateZoomLevels();
    }
});

module.exports.Map = LMap;

module.exports.map = function(element, _, options) {
    return new LMap(element, _, options);
};

},{"./feature_layer":8,"./feedback":9,"./grid_control":14,"./grid_layer":15,"./legend_control":16,"./load_tilejson":17,"./mapbox_logo":20,"./share_control":23,"./tile_layer":26,"sanitize-caja":4}],19:[function(require,module,exports){
'use strict';

var geocoderControl = require('./geocoder_control'),
    gridControl = require('./grid_control'),
    featureLayer = require('./feature_layer'),
    legendControl = require('./legend_control'),
    shareControl = require('./share_control'),
    tileLayer = require('./tile_layer'),
    map = require('./map'),
    gridLayer = require('./grid_layer'),
    styleLayer = require('./style_layer');

L.mapbox = module.exports = {
    VERSION: require('../package.json').version,
    geocoder: require('./geocoder'),
    marker: require('./marker'),
    simplestyle: require('./simplestyle'),
    tileLayer: tileLayer.tileLayer,
    TileLayer: tileLayer.TileLayer,
    styleLayer: styleLayer.styleLayer,
    StyleLayer: styleLayer.StyleLayer,
    shareControl: shareControl.shareControl,
    ShareControl: shareControl.ShareControl,
    legendControl: legendControl.legendControl,
    LegendControl: legendControl.LegendControl,
    geocoderControl: geocoderControl.geocoderControl,
    GeocoderControl: geocoderControl.GeocoderControl,
    gridControl: gridControl.gridControl,
    GridControl: gridControl.GridControl,
    gridLayer: gridLayer.gridLayer,
    GridLayer: gridLayer.GridLayer,
    featureLayer: featureLayer.featureLayer,
    FeatureLayer: featureLayer.FeatureLayer,
    map: map.map,
    Map: map.Map,
    config: require('./config'),
    sanitize: require('sanitize-caja'),
    template: require('mustache').to_html,
    feedback: require('./feedback')
};


// Hardcode image path, because Leaflet's autodetection
// fails, because mapbox.js is not named leaflet.js
window.L.Icon.Default.imagePath =
    // Detect bad-news protocols like file:// and hardcode
    // to https if they're detected.
    ((document.location.protocol === 'https:' ||
    document.location.protocol === 'http:') ? '' : 'https:') +
    '//api.tiles.mapbox.com/mapbox.js/' + 'v' +
    require('../package.json').version + '/images/';

},{"../package.json":6,"./config":7,"./feature_layer":8,"./feedback":9,"./geocoder":11,"./geocoder_control":12,"./grid_control":14,"./grid_layer":15,"./legend_control":16,"./map":18,"./marker":21,"./share_control":23,"./simplestyle":24,"./style_layer":25,"./tile_layer":26,"mustache":3,"sanitize-caja":4}],20:[function(require,module,exports){
'use strict';

var MapboxLogoControl = L.Control.extend({

    options: {
        position: 'bottomleft'
    },

    initialize: function(options) {
        L.setOptions(this, options);
    },

    onAdd: function() {
        this._container = L.DomUtil.create('div', 'mapbox-logo');
        return this._container;
    },

    _setTileJSON: function(json) {
        // Check if account referenced by the accessToken
        // is asscociated with the Mapbox Logo
        // as determined by mapbox-maps.
        if (json.mapbox_logo) {
            L.DomUtil.addClass(this._container, 'mapbox-logo-true');
        }
    }
});

module.exports.MapboxLogoControl = MapboxLogoControl;

module.exports.mapboxLogoControl = function(options) {
    return new MapboxLogoControl(options);
};

},{}],21:[function(require,module,exports){
'use strict';

var format_url = require('./format_url'),
    util = require('./util'),
    sanitize = require('sanitize-caja');

// mapbox-related markers functionality
// provide an icon from mapbox's simple-style spec and hosted markers
// service
function icon(fp, options) {
    fp = fp || {};

    var sizes = {
            small: [20, 50],
            medium: [30, 70],
            large: [35, 90]
        },
        size = fp['marker-size'] || 'medium',
        symbol = ('marker-symbol' in fp && fp['marker-symbol'] !== '') ? '-' + fp['marker-symbol'] : '',
        color = (fp['marker-color'] || '7e7e7e').replace('#', '');

    return L.icon({
        iconUrl: format_url('/v4/marker/' +
            'pin-' + size.charAt(0) + symbol + '+' + color +
            // detect and use retina markers, which are x2 resolution
            (L.Browser.retina ? '@2x' : '') + '.png', options && options.accessToken),
        iconSize: sizes[size],
        iconAnchor: [sizes[size][0] / 2, sizes[size][1] / 2],
        popupAnchor: [0, -sizes[size][1] / 2]
    });
}

// a factory that provides markers for Leaflet from Mapbox's
// [simple-style specification](https://github.com/mapbox/simplestyle-spec)
// and [Markers API](http://mapbox.com/developers/api/#markers).
function style(f, latlon, options) {
    return L.marker(latlon, {
        icon: icon(f.properties, options),
        title: util.strip_tags(
            sanitize((f.properties && f.properties.title) || ''))
    });
}

// Sanitize and format properties of a GeoJSON Feature object in order
// to form the HTML string used as the argument for `L.createPopup`
function createPopup(f, sanitizer) {
    if (!f || !f.properties) return '';
    var popup = '';

    if (f.properties.title) {
        popup += '<div class="marker-title">' + f.properties.title + '</div>';
    }

    if (f.properties.description) {
        popup += '<div class="marker-description">' + f.properties.description + '</div>';
    }

    return (sanitizer || sanitize)(popup);
}

module.exports = {
    icon: icon,
    style: style,
    createPopup: createPopup
};

},{"./format_url":10,"./util":27,"sanitize-caja":4}],22:[function(require,module,exports){
'use strict';

var corslite = require('corslite'),
    strict = require('./util').strict,
    config = require('./config');

var protocol = /^(https?:)?(?=\/\/(.|api)\.tiles\.mapbox\.com\/)/;

module.exports = function(url, callback) {
    strict(url, 'string');
    strict(callback, 'function');

    url = url.replace(protocol, function(match, protocol) {
        if (!('withCredentials' in new window.XMLHttpRequest())) {
            // XDomainRequest in use; doesn't support cross-protocol requests
            return document.location.protocol;
        } else if (protocol === 'https:' || document.location.protocol === 'https:' || config.FORCE_HTTPS) {
            return 'https:';
        } else {
            return 'http:';
        }
    });

    function onload(err, resp) {
        if (!err && resp) {
            resp = JSON.parse(resp.responseText);
        }
        callback(err, resp);
    }

    return corslite(url, onload);
};

},{"./config":7,"./util":27,"corslite":1}],23:[function(require,module,exports){
'use strict';

var format_url = require('./format_url');

var ShareControl = L.Control.extend({
    includes: [require('./load_tilejson')],

    options: {
        position: 'topleft',
        url: ''
    },

    initialize: function(_, options) {
        L.setOptions(this, options);
        this._loadTileJSON(_);
    },

    _setTileJSON: function(json) {
        this._tilejson = json;
    },

    onAdd: function(map) {
        this._map = map;

        var container = L.DomUtil.create('div', 'leaflet-control-mapbox-share leaflet-bar');
        var link = L.DomUtil.create('a', 'mapbox-share mapbox-icon mapbox-icon-share', container);
        link.href = '#';

        this._modal = L.DomUtil.create('div', 'mapbox-modal', this._map._container);
        this._mask = L.DomUtil.create('div', 'mapbox-modal-mask', this._modal);
        this._content = L.DomUtil.create('div', 'mapbox-modal-content', this._modal);

        L.DomEvent.addListener(link, 'click', this._shareClick, this);
        L.DomEvent.disableClickPropagation(container);

        this._map.on('mousedown', this._clickOut, this);

        return container;
    },

    _clickOut: function(e) {
        if (this._sharing) {
            L.DomEvent.preventDefault(e);
            L.DomUtil.removeClass(this._modal, 'active');
            this._content.innerHTML = '';
            this._sharing = null;
            return;
        }
    },

    _shareClick: function(e) {
        L.DomEvent.stop(e);
        if (this._sharing) return this._clickOut(e);

        var tilejson = this._tilejson || this._map._tilejson || {},
            url = encodeURIComponent(this.options.url || tilejson.webpage || window.location),
            name = encodeURIComponent(tilejson.name),
            image = format_url('/v4/' + tilejson.id + '/' + this._map.getCenter().lng + ',' + this._map.getCenter().lat + ',' + this._map.getZoom() + '/600x600.png', this.options.accessToken),
            embed = format_url('/v4/' + tilejson.id + '.html', this.options.accessToken),
            twitterURL = '//twitter.com/intent/tweet?status=' + name + ' ' + url,
            facebookURL = '//www.facebook.com/sharer.php?u=' + url + '&t=' + name,
            pinterestURL = '//www.pinterest.com/pin/create/button/?url=' + url + '&media=' + image + '&description=' + name,
            embedValue = '<iframe width="100%" height="500px" frameBorder="0" src="' + embed + '"></iframe>',
            embedLabel = 'Copy and paste this <strong>HTML code</strong> into documents to embed this map on web pages.';

        function createShareButton(buttonClass, href, socialMediaName) {
            var elem = document.createElement('a');
            elem.setAttribute('class', buttonClass);
            elem.setAttribute('href', href);
            elem.setAttribute('target', '_blank');
            socialMediaName = document.createTextNode(socialMediaName);
            elem.appendChild(socialMediaName);

            return elem;
        }

        L.DomUtil.addClass(this._modal, 'active');

        this._sharing = L.DomUtil.create('div', 'mapbox-modal-body', this._content);

        var twitterButton = createShareButton('mapbox-button mapbox-button-icon mapbox-icon-twitter', twitterURL, 'Twitter');
        var facebookButton = createShareButton('mapbox-button mapbox-button-icon mapbox-icon-facebook', facebookURL, 'Facebook');
        var pinterestButton = createShareButton('mapbox-button mapbox-button-icon mapbox-icon-pinterest', pinterestURL, 'Pinterest');

        var shareHeader = document.createElement('h3');
        var shareText = document.createTextNode('Share this map');
        shareHeader.appendChild(shareText);

        var shareButtons = document.createElement('div');
        shareButtons.setAttribute('class', 'mapbox-share-buttons');
        shareButtons.appendChild(facebookButton);
        shareButtons.appendChild(twitterButton);
        shareButtons.appendChild(pinterestButton);

        this._sharing.appendChild(shareHeader);
        this._sharing.appendChild(shareButtons);

        var input = L.DomUtil.create('input', 'mapbox-embed', this._sharing);
        input.type = 'text';
        input.value = embedValue;

        var label = L.DomUtil.create('label', 'mapbox-embed-description', this._sharing);
        label.innerHTML = embedLabel;

        var close = L.DomUtil.create('a', 'leaflet-popup-close-button', this._sharing);
        close.href = '#';

        L.DomEvent.disableClickPropagation(this._sharing);
        L.DomEvent.addListener(close, 'click', this._clickOut, this);
        L.DomEvent.addListener(input, 'click', function(e) {
            e.target.focus();
            e.target.select();
        });
    }
});

module.exports.ShareControl = ShareControl;

module.exports.shareControl = function(_, options) {
    return new ShareControl(_, options);
};

},{"./format_url":10,"./load_tilejson":17}],24:[function(require,module,exports){
'use strict';

// an implementation of the simplestyle spec for polygon and linestring features
// https://github.com/mapbox/simplestyle-spec
var defaults = {
    stroke: '#555555',
    'stroke-width': 2,
    'stroke-opacity': 1,
    fill: '#555555',
    'fill-opacity': 0.5
};

var mapping = [
    ['stroke', 'color'],
    ['stroke-width', 'weight'],
    ['stroke-opacity', 'opacity'],
    ['fill', 'fillColor'],
    ['fill-opacity', 'fillOpacity']
];

function fallback(a, b) {
    var c = {};
    for (var k in b) {
        if (a[k] === undefined) c[k] = b[k];
        else c[k] = a[k];
    }
    return c;
}

function remap(a) {
    var d = {};
    for (var i = 0; i < mapping.length; i++) {
        d[mapping[i][1]] = a[mapping[i][0]];
    }
    return d;
}

function style(feature) {
    return remap(fallback(feature.properties || {}, defaults));
}

module.exports = {
    style: style,
    defaults: defaults
};

},{}],25:[function(require,module,exports){
'use strict';

var util = require('./util');
var format_url = require('./format_url');
var request = require('./request');

var StyleLayer = L.TileLayer.extend({

    options: {
        sanitizer: require('sanitize-caja')
    },

    initialize: function(_, options) {
        L.TileLayer.prototype.initialize.call(this, undefined, L.extend({}, options, {
            tileSize: 512,
            zoomOffset: -1,
            minNativeZoom: 0,
            tms: false
        }));
        this._url = this._formatTileURL(_);
        this._getAttribution(_);
    },

    _getAttribution: function(_) {
        var styleURL = format_url.style(_, this.options && this.options.accessToken);
        request(styleURL, L.bind(function(err, style) {
            if (err) {
                util.log('could not load Mapbox style at ' + styleURL);
                this.fire('error', {error: err});
            }
            var sources = [];
            for (var id in style.sources) {
                var source = style.sources[id].url.split('mapbox://')[1];
                sources.push(source);
            }
            request(format_url.tileJSON(sources.join(), this.options.accessToken), L.bind(function(err, json) {
                if (err) {
                    util.log('could not load TileJSON at ' + _);
                    this.fire('error', {error: err});
                } else if (json) {
                    util.strict(json, 'object');

                    this.options.attribution = this.options.sanitizer(json.attribution);

                    this._tilejson = json;
                    this.fire('ready');
                }
            }, this));
        }, this));
    },

    // disable the setUrl function, which is not available on mapbox tilelayers
    setUrl: null,

    _formatTileURL: function(style) {
        if (typeof style === 'string') {
            if (style.indexOf('mapbox://styles/') === -1) {
                util.log('Incorrectly formatted Mapbox style at ' + style);
                this.fire('error');
            }
            var ownerIDStyle = style.split('mapbox://styles/')[1];
            return format_url('/styles/v1/' + ownerIDStyle + '/tiles/{z}/{x}/{y}{r}', this.options.accessToken);
        } else if (typeof style === 'object') {
            return format_url('/styles/v1/' + style.owner + '/' + style.id + '/tiles/{z}/{x}/{y}{r}', this.options.accessToken);
        }
    }
});

module.exports.StyleLayer = StyleLayer;

module.exports.styleLayer = function(_, options) {
    return new StyleLayer(_, options);
};

},{"./format_url":10,"./request":22,"./util":27,"sanitize-caja":4}],26:[function(require,module,exports){
'use strict';

var util = require('./util');
var formatPattern = /\.((?:png|jpg)\d*)(?=$|\?)/;

var TileLayer = L.TileLayer.extend({
    includes: [require('./load_tilejson')],

    options: {
        sanitizer: require('sanitize-caja')
    },

    // http://mapbox.com/developers/api/#image_quality
    formats: [
        'png', 'jpg',
        // PNG
        'png32', 'png64', 'png128', 'png256',
        // JPG
        'jpg70', 'jpg80', 'jpg90'],

    scalePrefix: '@2x.',

    initialize: function(_, options) {
        L.TileLayer.prototype.initialize.call(this, undefined, options);

        this._tilejson = {};

        if (options && options.format) {
            util.strict_oneof(options.format, this.formats);
        }

        this._loadTileJSON(_);
    },

    setFormat: function(_) {
        util.strict(_, 'string');
        this.options.format = _;
        this.redraw();
        return this;
    },

    // disable the setUrl function, which is not available on mapbox tilelayers
    setUrl: null,

    _setTileJSON: function(json) {
        util.strict(json, 'object');

        if (!this.options.format) {
          var match = json.tiles[0].match(formatPattern);
          if (match) {
              this.options.format = match[1];
          }
        }

        L.extend(this.options, {
            tiles: json.tiles,
            attribution: this.options.sanitizer(json.attribution),
            minZoom: json.minzoom || 0,
            maxZoom: json.maxzoom || 18,
            tms: json.scheme === 'tms',
            bounds: json.bounds && util.lbounds(json.bounds)
        });

        this._tilejson = json;
        this.redraw();
        return this;
    },

    getTileJSON: function() {
        return this._tilejson;
    },

    // this is an exception to mapbox.js naming rules because it's called
    // by `L.map`
    getTileUrl: function(tilePoint) {
        var tiles = this.options.tiles,
            index = Math.floor(Math.abs(tilePoint.x + tilePoint.y) % tiles.length),
            url = tiles[index];

        var templated = L.Util.template(url, tilePoint);
        if (!templated || !this.options.format) {
            return templated;
        } else {
            return templated.replace(formatPattern,
                (L.Browser.retina ? this.scalePrefix : '.') + this.options.format);
        }
    },

    // TileJSON.TileLayers are added to the map immediately, so that they get
    // the desired z-index, but do not update until the TileJSON has been loaded.
    _update: function() {
        if (this.options.tiles) {
            L.TileLayer.prototype._update.call(this);
        }
    }
});

module.exports.TileLayer = TileLayer;

module.exports.tileLayer = function(_, options) {
    return new TileLayer(_, options);
};

},{"./load_tilejson":17,"./util":27,"sanitize-caja":4}],27:[function(require,module,exports){
'use strict';

function contains(item, list) {
    if (!list || !list.length) return false;
    for (var i = 0; i < list.length; i++) {
        if (list[i] === item) return true;
    }
    return false;
}

module.exports = {
    idUrl: function(_, t) {
        if (_.indexOf('/') === -1) t.loadID(_);
        else t.loadURL(_);
    },
    log: function(_) {
        if (typeof console === 'object' &&
            typeof console.error === 'function') {
            console.error(_);
        }
    },
    strict: function(_, type) {
        if (typeof _ !== type) {
            throw new Error('Invalid argument: ' + type + ' expected');
        }
    },
    strict_instance: function(_, klass, name) {
        if (!(_ instanceof klass)) {
            throw new Error('Invalid argument: ' + name + ' expected');
        }
    },
    strict_oneof: function(_, values) {
        if (!contains(_, values)) {
            throw new Error('Invalid argument: ' + _ + ' given, valid values are ' +
                values.join(', '));
        }
    },
    strip_tags: function(_) {
        return _.replace(/<[^<]+>/g, '');
    },
    lbounds: function(_) {
        // leaflet-compatible bounds, since leaflet does not do geojson
        return new L.LatLngBounds([[_[1], _[0]], [_[3], _[2]]]);
    }
};

},{}]},{},[19])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJub2RlX21vZHVsZXMvY29yc2xpdGUvY29yc2xpdGUuanMiLCJub2RlX21vZHVsZXMvaXNhcnJheS9pbmRleC5qcyIsIm5vZGVfbW9kdWxlcy9tdXN0YWNoZS9tdXN0YWNoZS5qcyIsIm5vZGVfbW9kdWxlcy9zYW5pdGl6ZS1jYWphL2luZGV4LmpzIiwibm9kZV9tb2R1bGVzL3Nhbml0aXplLWNhamEvc2FuaXRpemVyLWJ1bmRsZS5qcyIsInBhY2thZ2UuanNvbiIsInNyYy9jb25maWcuanMiLCJzcmMvZmVhdHVyZV9sYXllci5qcyIsInNyYy9mZWVkYmFjay5qcyIsInNyYy9mb3JtYXRfdXJsLmpzIiwic3JjL2dlb2NvZGVyLmpzIiwic3JjL2dlb2NvZGVyX2NvbnRyb2wuanMiLCJzcmMvZ3JpZC5qcyIsInNyYy9ncmlkX2NvbnRyb2wuanMiLCJzcmMvZ3JpZF9sYXllci5qcyIsInNyYy9sZWdlbmRfY29udHJvbC5qcyIsInNyYy9sb2FkX3RpbGVqc29uLmpzIiwic3JjL21hcC5qcyIsInNyYy9tYXBib3guanMiLCJzcmMvbWFwYm94X2xvZ28uanMiLCJzcmMvbWFya2VyLmpzIiwic3JjL3JlcXVlc3QuanMiLCJzcmMvc2hhcmVfY29udHJvbC5qcyIsInNyYy9zaW1wbGVzdHlsZS5qcyIsInNyYy9zdHlsZV9sYXllci5qcyIsInNyYy90aWxlX2xheWVyLmpzIiwic3JjL3V0aWwuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUNBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUM3RkE7QUFDQTtBQUNBO0FBQ0E7O0FDSEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3JuQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMvNEVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDakRBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNSQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNqSUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDWkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDNURBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3RKQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUM1TUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNoQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDdE1BO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMxTkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNuRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDeEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMxTkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbkRBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNoQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ2pFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDaENBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDekhBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzdDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3pFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN0R0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwiZnVuY3Rpb24gY29yc2xpdGUodXJsLCBjYWxsYmFjaywgY29ycykge1xuICAgIHZhciBzZW50ID0gZmFsc2U7XG5cbiAgICBpZiAodHlwZW9mIHdpbmRvdy5YTUxIdHRwUmVxdWVzdCA9PT0gJ3VuZGVmaW5lZCcpIHtcbiAgICAgICAgcmV0dXJuIGNhbGxiYWNrKEVycm9yKCdCcm93c2VyIG5vdCBzdXBwb3J0ZWQnKSk7XG4gICAgfVxuXG4gICAgaWYgKHR5cGVvZiBjb3JzID09PSAndW5kZWZpbmVkJykge1xuICAgICAgICB2YXIgbSA9IHVybC5tYXRjaCgvXlxccypodHRwcz86XFwvXFwvW15cXC9dKi8pO1xuICAgICAgICBjb3JzID0gbSAmJiAobVswXSAhPT0gbG9jYXRpb24ucHJvdG9jb2wgKyAnLy8nICsgbG9jYXRpb24uZG9tYWluICtcbiAgICAgICAgICAgICAgICAobG9jYXRpb24ucG9ydCA/ICc6JyArIGxvY2F0aW9uLnBvcnQgOiAnJykpO1xuICAgIH1cblxuICAgIHZhciB4ID0gbmV3IHdpbmRvdy5YTUxIdHRwUmVxdWVzdCgpO1xuXG4gICAgZnVuY3Rpb24gaXNTdWNjZXNzZnVsKHN0YXR1cykge1xuICAgICAgICByZXR1cm4gc3RhdHVzID49IDIwMCAmJiBzdGF0dXMgPCAzMDAgfHwgc3RhdHVzID09PSAzMDQ7XG4gICAgfVxuXG4gICAgaWYgKGNvcnMgJiYgISgnd2l0aENyZWRlbnRpYWxzJyBpbiB4KSkge1xuICAgICAgICAvLyBJRTgtOVxuICAgICAgICB4ID0gbmV3IHdpbmRvdy5YRG9tYWluUmVxdWVzdCgpO1xuXG4gICAgICAgIC8vIEVuc3VyZSBjYWxsYmFjayBpcyBuZXZlciBjYWxsZWQgc3luY2hyb25vdXNseSwgaS5lLiwgYmVmb3JlXG4gICAgICAgIC8vIHguc2VuZCgpIHJldHVybnMgKHRoaXMgaGFzIGJlZW4gb2JzZXJ2ZWQgaW4gdGhlIHdpbGQpLlxuICAgICAgICAvLyBTZWUgaHR0cHM6Ly9naXRodWIuY29tL21hcGJveC9tYXBib3guanMvaXNzdWVzLzQ3MlxuICAgICAgICB2YXIgb3JpZ2luYWwgPSBjYWxsYmFjaztcbiAgICAgICAgY2FsbGJhY2sgPSBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGlmIChzZW50KSB7XG4gICAgICAgICAgICAgICAgb3JpZ2luYWwuYXBwbHkodGhpcywgYXJndW1lbnRzKTtcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgdmFyIHRoYXQgPSB0aGlzLCBhcmdzID0gYXJndW1lbnRzO1xuICAgICAgICAgICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICAgICAgICAgIG9yaWdpbmFsLmFwcGx5KHRoYXQsIGFyZ3MpO1xuICAgICAgICAgICAgICAgIH0sIDApO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfVxuXG4gICAgZnVuY3Rpb24gbG9hZGVkKCkge1xuICAgICAgICBpZiAoXG4gICAgICAgICAgICAvLyBYRG9tYWluUmVxdWVzdFxuICAgICAgICAgICAgeC5zdGF0dXMgPT09IHVuZGVmaW5lZCB8fFxuICAgICAgICAgICAgLy8gbW9kZXJuIGJyb3dzZXJzXG4gICAgICAgICAgICBpc1N1Y2Nlc3NmdWwoeC5zdGF0dXMpKSBjYWxsYmFjay5jYWxsKHgsIG51bGwsIHgpO1xuICAgICAgICBlbHNlIGNhbGxiYWNrLmNhbGwoeCwgeCwgbnVsbCk7XG4gICAgfVxuXG4gICAgLy8gQm90aCBgb25yZWFkeXN0YXRlY2hhbmdlYCBhbmQgYG9ubG9hZGAgY2FuIGZpcmUuIGBvbnJlYWR5c3RhdGVjaGFuZ2VgXG4gICAgLy8gaGFzIFtiZWVuIHN1cHBvcnRlZCBmb3IgbG9uZ2VyXShodHRwOi8vc3RhY2tvdmVyZmxvdy5jb20vYS85MTgxNTA4LzIyOTAwMSkuXG4gICAgaWYgKCdvbmxvYWQnIGluIHgpIHtcbiAgICAgICAgeC5vbmxvYWQgPSBsb2FkZWQ7XG4gICAgfSBlbHNlIHtcbiAgICAgICAgeC5vbnJlYWR5c3RhdGVjaGFuZ2UgPSBmdW5jdGlvbiByZWFkeXN0YXRlKCkge1xuICAgICAgICAgICAgaWYgKHgucmVhZHlTdGF0ZSA9PT0gNCkge1xuICAgICAgICAgICAgICAgIGxvYWRlZCgpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9O1xuICAgIH1cblxuICAgIC8vIENhbGwgdGhlIGNhbGxiYWNrIHdpdGggdGhlIFhNTEh0dHBSZXF1ZXN0IG9iamVjdCBhcyBhbiBlcnJvciBhbmQgcHJldmVudFxuICAgIC8vIGl0IGZyb20gZXZlciBiZWluZyBjYWxsZWQgYWdhaW4gYnkgcmVhc3NpZ25pbmcgaXQgdG8gYG5vb3BgXG4gICAgeC5vbmVycm9yID0gZnVuY3Rpb24gZXJyb3IoZXZ0KSB7XG4gICAgICAgIC8vIFhEb21haW5SZXF1ZXN0IHByb3ZpZGVzIG5vIGV2dCBwYXJhbWV0ZXJcbiAgICAgICAgY2FsbGJhY2suY2FsbCh0aGlzLCBldnQgfHwgdHJ1ZSwgbnVsbCk7XG4gICAgICAgIGNhbGxiYWNrID0gZnVuY3Rpb24oKSB7IH07XG4gICAgfTtcblxuICAgIC8vIElFOSBtdXN0IGhhdmUgb25wcm9ncmVzcyBiZSBzZXQgdG8gYSB1bmlxdWUgZnVuY3Rpb24uXG4gICAgeC5vbnByb2dyZXNzID0gZnVuY3Rpb24oKSB7IH07XG5cbiAgICB4Lm9udGltZW91dCA9IGZ1bmN0aW9uKGV2dCkge1xuICAgICAgICBjYWxsYmFjay5jYWxsKHRoaXMsIGV2dCwgbnVsbCk7XG4gICAgICAgIGNhbGxiYWNrID0gZnVuY3Rpb24oKSB7IH07XG4gICAgfTtcblxuICAgIHgub25hYm9ydCA9IGZ1bmN0aW9uKGV2dCkge1xuICAgICAgICBjYWxsYmFjay5jYWxsKHRoaXMsIGV2dCwgbnVsbCk7XG4gICAgICAgIGNhbGxiYWNrID0gZnVuY3Rpb24oKSB7IH07XG4gICAgfTtcblxuICAgIC8vIEdFVCBpcyB0aGUgb25seSBzdXBwb3J0ZWQgSFRUUCBWZXJiIGJ5IFhEb21haW5SZXF1ZXN0IGFuZCBpcyB0aGVcbiAgICAvLyBvbmx5IG9uZSBzdXBwb3J0ZWQgaGVyZS5cbiAgICB4Lm9wZW4oJ0dFVCcsIHVybCwgdHJ1ZSk7XG5cbiAgICAvLyBTZW5kIHRoZSByZXF1ZXN0LiBTZW5kaW5nIGRhdGEgaXMgbm90IHN1cHBvcnRlZC5cbiAgICB4LnNlbmQobnVsbCk7XG4gICAgc2VudCA9IHRydWU7XG5cbiAgICByZXR1cm4geDtcbn1cblxuaWYgKHR5cGVvZiBtb2R1bGUgIT09ICd1bmRlZmluZWQnKSBtb2R1bGUuZXhwb3J0cyA9IGNvcnNsaXRlO1xuIiwibW9kdWxlLmV4cG9ydHMgPSBBcnJheS5pc0FycmF5IHx8IGZ1bmN0aW9uIChhcnIpIHtcbiAgcmV0dXJuIE9iamVjdC5wcm90b3R5cGUudG9TdHJpbmcuY2FsbChhcnIpID09ICdbb2JqZWN0IEFycmF5XSc7XG59O1xuIiwiLyohXG4gKiBtdXN0YWNoZS5qcyAtIExvZ2ljLWxlc3Mge3ttdXN0YWNoZX19IHRlbXBsYXRlcyB3aXRoIEphdmFTY3JpcHRcbiAqIGh0dHA6Ly9naXRodWIuY29tL2phbmwvbXVzdGFjaGUuanNcbiAqL1xuXG4vKmdsb2JhbCBkZWZpbmU6IGZhbHNlIE11c3RhY2hlOiB0cnVlKi9cblxuKGZ1bmN0aW9uIGRlZmluZU11c3RhY2hlIChnbG9iYWwsIGZhY3RvcnkpIHtcbiAgaWYgKHR5cGVvZiBleHBvcnRzID09PSAnb2JqZWN0JyAmJiBleHBvcnRzICYmIHR5cGVvZiBleHBvcnRzLm5vZGVOYW1lICE9PSAnc3RyaW5nJykge1xuICAgIGZhY3RvcnkoZXhwb3J0cyk7IC8vIENvbW1vbkpTXG4gIH0gZWxzZSBpZiAodHlwZW9mIGRlZmluZSA9PT0gJ2Z1bmN0aW9uJyAmJiBkZWZpbmUuYW1kKSB7XG4gICAgZGVmaW5lKFsnZXhwb3J0cyddLCBmYWN0b3J5KTsgLy8gQU1EXG4gIH0gZWxzZSB7XG4gICAgZ2xvYmFsLk11c3RhY2hlID0ge307XG4gICAgZmFjdG9yeShnbG9iYWwuTXVzdGFjaGUpOyAvLyBzY3JpcHQsIHdzaCwgYXNwXG4gIH1cbn0odGhpcywgZnVuY3Rpb24gbXVzdGFjaGVGYWN0b3J5IChtdXN0YWNoZSkge1xuXG4gIHZhciBvYmplY3RUb1N0cmluZyA9IE9iamVjdC5wcm90b3R5cGUudG9TdHJpbmc7XG4gIHZhciBpc0FycmF5ID0gQXJyYXkuaXNBcnJheSB8fCBmdW5jdGlvbiBpc0FycmF5UG9seWZpbGwgKG9iamVjdCkge1xuICAgIHJldHVybiBvYmplY3RUb1N0cmluZy5jYWxsKG9iamVjdCkgPT09ICdbb2JqZWN0IEFycmF5XSc7XG4gIH07XG5cbiAgZnVuY3Rpb24gaXNGdW5jdGlvbiAob2JqZWN0KSB7XG4gICAgcmV0dXJuIHR5cGVvZiBvYmplY3QgPT09ICdmdW5jdGlvbic7XG4gIH1cblxuICAvKipcbiAgICogTW9yZSBjb3JyZWN0IHR5cGVvZiBzdHJpbmcgaGFuZGxpbmcgYXJyYXlcbiAgICogd2hpY2ggbm9ybWFsbHkgcmV0dXJucyB0eXBlb2YgJ29iamVjdCdcbiAgICovXG4gIGZ1bmN0aW9uIHR5cGVTdHIgKG9iaikge1xuICAgIHJldHVybiBpc0FycmF5KG9iaikgPyAnYXJyYXknIDogdHlwZW9mIG9iajtcbiAgfVxuXG4gIGZ1bmN0aW9uIGVzY2FwZVJlZ0V4cCAoc3RyaW5nKSB7XG4gICAgcmV0dXJuIHN0cmluZy5yZXBsYWNlKC9bXFwtXFxbXFxde30oKSorPy4sXFxcXFxcXiR8I1xcc10vZywgJ1xcXFwkJicpO1xuICB9XG5cbiAgLyoqXG4gICAqIE51bGwgc2FmZSB3YXkgb2YgY2hlY2tpbmcgd2hldGhlciBvciBub3QgYW4gb2JqZWN0LFxuICAgKiBpbmNsdWRpbmcgaXRzIHByb3RvdHlwZSwgaGFzIGEgZ2l2ZW4gcHJvcGVydHlcbiAgICovXG4gIGZ1bmN0aW9uIGhhc1Byb3BlcnR5IChvYmosIHByb3BOYW1lKSB7XG4gICAgcmV0dXJuIG9iaiAhPSBudWxsICYmIHR5cGVvZiBvYmogPT09ICdvYmplY3QnICYmIChwcm9wTmFtZSBpbiBvYmopO1xuICB9XG5cbiAgLy8gV29ya2Fyb3VuZCBmb3IgaHR0cHM6Ly9pc3N1ZXMuYXBhY2hlLm9yZy9qaXJhL2Jyb3dzZS9DT1VDSERCLTU3N1xuICAvLyBTZWUgaHR0cHM6Ly9naXRodWIuY29tL2phbmwvbXVzdGFjaGUuanMvaXNzdWVzLzE4OVxuICB2YXIgcmVnRXhwVGVzdCA9IFJlZ0V4cC5wcm90b3R5cGUudGVzdDtcbiAgZnVuY3Rpb24gdGVzdFJlZ0V4cCAocmUsIHN0cmluZykge1xuICAgIHJldHVybiByZWdFeHBUZXN0LmNhbGwocmUsIHN0cmluZyk7XG4gIH1cblxuICB2YXIgbm9uU3BhY2VSZSA9IC9cXFMvO1xuICBmdW5jdGlvbiBpc1doaXRlc3BhY2UgKHN0cmluZykge1xuICAgIHJldHVybiAhdGVzdFJlZ0V4cChub25TcGFjZVJlLCBzdHJpbmcpO1xuICB9XG5cbiAgdmFyIGVudGl0eU1hcCA9IHtcbiAgICAnJic6ICcmYW1wOycsXG4gICAgJzwnOiAnJmx0OycsXG4gICAgJz4nOiAnJmd0OycsXG4gICAgJ1wiJzogJyZxdW90OycsXG4gICAgXCInXCI6ICcmIzM5OycsXG4gICAgJy8nOiAnJiN4MkY7JyxcbiAgICAnYCc6ICcmI3g2MDsnLFxuICAgICc9JzogJyYjeDNEOydcbiAgfTtcblxuICBmdW5jdGlvbiBlc2NhcGVIdG1sIChzdHJpbmcpIHtcbiAgICByZXR1cm4gU3RyaW5nKHN0cmluZykucmVwbGFjZSgvWyY8PlwiJ2A9XFwvXS9nLCBmdW5jdGlvbiBmcm9tRW50aXR5TWFwIChzKSB7XG4gICAgICByZXR1cm4gZW50aXR5TWFwW3NdO1xuICAgIH0pO1xuICB9XG5cbiAgdmFyIHdoaXRlUmUgPSAvXFxzKi87XG4gIHZhciBzcGFjZVJlID0gL1xccysvO1xuICB2YXIgZXF1YWxzUmUgPSAvXFxzKj0vO1xuICB2YXIgY3VybHlSZSA9IC9cXHMqXFx9LztcbiAgdmFyIHRhZ1JlID0gLyN8XFxefFxcL3w+fFxce3wmfD18IS87XG5cbiAgLyoqXG4gICAqIEJyZWFrcyB1cCB0aGUgZ2l2ZW4gYHRlbXBsYXRlYCBzdHJpbmcgaW50byBhIHRyZWUgb2YgdG9rZW5zLiBJZiB0aGUgYHRhZ3NgXG4gICAqIGFyZ3VtZW50IGlzIGdpdmVuIGhlcmUgaXQgbXVzdCBiZSBhbiBhcnJheSB3aXRoIHR3byBzdHJpbmcgdmFsdWVzOiB0aGVcbiAgICogb3BlbmluZyBhbmQgY2xvc2luZyB0YWdzIHVzZWQgaW4gdGhlIHRlbXBsYXRlIChlLmcuIFsgXCI8JVwiLCBcIiU+XCIgXSkuIE9mXG4gICAqIGNvdXJzZSwgdGhlIGRlZmF1bHQgaXMgdG8gdXNlIG11c3RhY2hlcyAoaS5lLiBtdXN0YWNoZS50YWdzKS5cbiAgICpcbiAgICogQSB0b2tlbiBpcyBhbiBhcnJheSB3aXRoIGF0IGxlYXN0IDQgZWxlbWVudHMuIFRoZSBmaXJzdCBlbGVtZW50IGlzIHRoZVxuICAgKiBtdXN0YWNoZSBzeW1ib2wgdGhhdCB3YXMgdXNlZCBpbnNpZGUgdGhlIHRhZywgZS5nLiBcIiNcIiBvciBcIiZcIi4gSWYgdGhlIHRhZ1xuICAgKiBkaWQgbm90IGNvbnRhaW4gYSBzeW1ib2wgKGkuZS4ge3tteVZhbHVlfX0pIHRoaXMgZWxlbWVudCBpcyBcIm5hbWVcIi4gRm9yXG4gICAqIGFsbCB0ZXh0IHRoYXQgYXBwZWFycyBvdXRzaWRlIGEgc3ltYm9sIHRoaXMgZWxlbWVudCBpcyBcInRleHRcIi5cbiAgICpcbiAgICogVGhlIHNlY29uZCBlbGVtZW50IG9mIGEgdG9rZW4gaXMgaXRzIFwidmFsdWVcIi4gRm9yIG11c3RhY2hlIHRhZ3MgdGhpcyBpc1xuICAgKiB3aGF0ZXZlciBlbHNlIHdhcyBpbnNpZGUgdGhlIHRhZyBiZXNpZGVzIHRoZSBvcGVuaW5nIHN5bWJvbC4gRm9yIHRleHQgdG9rZW5zXG4gICAqIHRoaXMgaXMgdGhlIHRleHQgaXRzZWxmLlxuICAgKlxuICAgKiBUaGUgdGhpcmQgYW5kIGZvdXJ0aCBlbGVtZW50cyBvZiB0aGUgdG9rZW4gYXJlIHRoZSBzdGFydCBhbmQgZW5kIGluZGljZXMsXG4gICAqIHJlc3BlY3RpdmVseSwgb2YgdGhlIHRva2VuIGluIHRoZSBvcmlnaW5hbCB0ZW1wbGF0ZS5cbiAgICpcbiAgICogVG9rZW5zIHRoYXQgYXJlIHRoZSByb290IG5vZGUgb2YgYSBzdWJ0cmVlIGNvbnRhaW4gdHdvIG1vcmUgZWxlbWVudHM6IDEpIGFuXG4gICAqIGFycmF5IG9mIHRva2VucyBpbiB0aGUgc3VidHJlZSBhbmQgMikgdGhlIGluZGV4IGluIHRoZSBvcmlnaW5hbCB0ZW1wbGF0ZSBhdFxuICAgKiB3aGljaCB0aGUgY2xvc2luZyB0YWcgZm9yIHRoYXQgc2VjdGlvbiBiZWdpbnMuXG4gICAqL1xuICBmdW5jdGlvbiBwYXJzZVRlbXBsYXRlICh0ZW1wbGF0ZSwgdGFncykge1xuICAgIGlmICghdGVtcGxhdGUpXG4gICAgICByZXR1cm4gW107XG5cbiAgICB2YXIgc2VjdGlvbnMgPSBbXTsgICAgIC8vIFN0YWNrIHRvIGhvbGQgc2VjdGlvbiB0b2tlbnNcbiAgICB2YXIgdG9rZW5zID0gW107ICAgICAgIC8vIEJ1ZmZlciB0byBob2xkIHRoZSB0b2tlbnNcbiAgICB2YXIgc3BhY2VzID0gW107ICAgICAgIC8vIEluZGljZXMgb2Ygd2hpdGVzcGFjZSB0b2tlbnMgb24gdGhlIGN1cnJlbnQgbGluZVxuICAgIHZhciBoYXNUYWcgPSBmYWxzZTsgICAgLy8gSXMgdGhlcmUgYSB7e3RhZ319IG9uIHRoZSBjdXJyZW50IGxpbmU/XG4gICAgdmFyIG5vblNwYWNlID0gZmFsc2U7ICAvLyBJcyB0aGVyZSBhIG5vbi1zcGFjZSBjaGFyIG9uIHRoZSBjdXJyZW50IGxpbmU/XG5cbiAgICAvLyBTdHJpcHMgYWxsIHdoaXRlc3BhY2UgdG9rZW5zIGFycmF5IGZvciB0aGUgY3VycmVudCBsaW5lXG4gICAgLy8gaWYgdGhlcmUgd2FzIGEge3sjdGFnfX0gb24gaXQgYW5kIG90aGVyd2lzZSBvbmx5IHNwYWNlLlxuICAgIGZ1bmN0aW9uIHN0cmlwU3BhY2UgKCkge1xuICAgICAgaWYgKGhhc1RhZyAmJiAhbm9uU3BhY2UpIHtcbiAgICAgICAgd2hpbGUgKHNwYWNlcy5sZW5ndGgpXG4gICAgICAgICAgZGVsZXRlIHRva2Vuc1tzcGFjZXMucG9wKCldO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgc3BhY2VzID0gW107XG4gICAgICB9XG5cbiAgICAgIGhhc1RhZyA9IGZhbHNlO1xuICAgICAgbm9uU3BhY2UgPSBmYWxzZTtcbiAgICB9XG5cbiAgICB2YXIgb3BlbmluZ1RhZ1JlLCBjbG9zaW5nVGFnUmUsIGNsb3NpbmdDdXJseVJlO1xuICAgIGZ1bmN0aW9uIGNvbXBpbGVUYWdzICh0YWdzVG9Db21waWxlKSB7XG4gICAgICBpZiAodHlwZW9mIHRhZ3NUb0NvbXBpbGUgPT09ICdzdHJpbmcnKVxuICAgICAgICB0YWdzVG9Db21waWxlID0gdGFnc1RvQ29tcGlsZS5zcGxpdChzcGFjZVJlLCAyKTtcblxuICAgICAgaWYgKCFpc0FycmF5KHRhZ3NUb0NvbXBpbGUpIHx8IHRhZ3NUb0NvbXBpbGUubGVuZ3RoICE9PSAyKVxuICAgICAgICB0aHJvdyBuZXcgRXJyb3IoJ0ludmFsaWQgdGFnczogJyArIHRhZ3NUb0NvbXBpbGUpO1xuXG4gICAgICBvcGVuaW5nVGFnUmUgPSBuZXcgUmVnRXhwKGVzY2FwZVJlZ0V4cCh0YWdzVG9Db21waWxlWzBdKSArICdcXFxccyonKTtcbiAgICAgIGNsb3NpbmdUYWdSZSA9IG5ldyBSZWdFeHAoJ1xcXFxzKicgKyBlc2NhcGVSZWdFeHAodGFnc1RvQ29tcGlsZVsxXSkpO1xuICAgICAgY2xvc2luZ0N1cmx5UmUgPSBuZXcgUmVnRXhwKCdcXFxccyonICsgZXNjYXBlUmVnRXhwKCd9JyArIHRhZ3NUb0NvbXBpbGVbMV0pKTtcbiAgICB9XG5cbiAgICBjb21waWxlVGFncyh0YWdzIHx8IG11c3RhY2hlLnRhZ3MpO1xuXG4gICAgdmFyIHNjYW5uZXIgPSBuZXcgU2Nhbm5lcih0ZW1wbGF0ZSk7XG5cbiAgICB2YXIgc3RhcnQsIHR5cGUsIHZhbHVlLCBjaHIsIHRva2VuLCBvcGVuU2VjdGlvbjtcbiAgICB3aGlsZSAoIXNjYW5uZXIuZW9zKCkpIHtcbiAgICAgIHN0YXJ0ID0gc2Nhbm5lci5wb3M7XG5cbiAgICAgIC8vIE1hdGNoIGFueSB0ZXh0IGJldHdlZW4gdGFncy5cbiAgICAgIHZhbHVlID0gc2Nhbm5lci5zY2FuVW50aWwob3BlbmluZ1RhZ1JlKTtcblxuICAgICAgaWYgKHZhbHVlKSB7XG4gICAgICAgIGZvciAodmFyIGkgPSAwLCB2YWx1ZUxlbmd0aCA9IHZhbHVlLmxlbmd0aDsgaSA8IHZhbHVlTGVuZ3RoOyArK2kpIHtcbiAgICAgICAgICBjaHIgPSB2YWx1ZS5jaGFyQXQoaSk7XG5cbiAgICAgICAgICBpZiAoaXNXaGl0ZXNwYWNlKGNocikpIHtcbiAgICAgICAgICAgIHNwYWNlcy5wdXNoKHRva2Vucy5sZW5ndGgpO1xuICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICBub25TcGFjZSA9IHRydWU7XG4gICAgICAgICAgfVxuXG4gICAgICAgICAgdG9rZW5zLnB1c2goWyAndGV4dCcsIGNociwgc3RhcnQsIHN0YXJ0ICsgMSBdKTtcbiAgICAgICAgICBzdGFydCArPSAxO1xuXG4gICAgICAgICAgLy8gQ2hlY2sgZm9yIHdoaXRlc3BhY2Ugb24gdGhlIGN1cnJlbnQgbGluZS5cbiAgICAgICAgICBpZiAoY2hyID09PSAnXFxuJylcbiAgICAgICAgICAgIHN0cmlwU3BhY2UoKTtcbiAgICAgICAgfVxuICAgICAgfVxuXG4gICAgICAvLyBNYXRjaCB0aGUgb3BlbmluZyB0YWcuXG4gICAgICBpZiAoIXNjYW5uZXIuc2NhbihvcGVuaW5nVGFnUmUpKVxuICAgICAgICBicmVhaztcblxuICAgICAgaGFzVGFnID0gdHJ1ZTtcblxuICAgICAgLy8gR2V0IHRoZSB0YWcgdHlwZS5cbiAgICAgIHR5cGUgPSBzY2FubmVyLnNjYW4odGFnUmUpIHx8ICduYW1lJztcbiAgICAgIHNjYW5uZXIuc2Nhbih3aGl0ZVJlKTtcblxuICAgICAgLy8gR2V0IHRoZSB0YWcgdmFsdWUuXG4gICAgICBpZiAodHlwZSA9PT0gJz0nKSB7XG4gICAgICAgIHZhbHVlID0gc2Nhbm5lci5zY2FuVW50aWwoZXF1YWxzUmUpO1xuICAgICAgICBzY2FubmVyLnNjYW4oZXF1YWxzUmUpO1xuICAgICAgICBzY2FubmVyLnNjYW5VbnRpbChjbG9zaW5nVGFnUmUpO1xuICAgICAgfSBlbHNlIGlmICh0eXBlID09PSAneycpIHtcbiAgICAgICAgdmFsdWUgPSBzY2FubmVyLnNjYW5VbnRpbChjbG9zaW5nQ3VybHlSZSk7XG4gICAgICAgIHNjYW5uZXIuc2NhbihjdXJseVJlKTtcbiAgICAgICAgc2Nhbm5lci5zY2FuVW50aWwoY2xvc2luZ1RhZ1JlKTtcbiAgICAgICAgdHlwZSA9ICcmJztcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIHZhbHVlID0gc2Nhbm5lci5zY2FuVW50aWwoY2xvc2luZ1RhZ1JlKTtcbiAgICAgIH1cblxuICAgICAgLy8gTWF0Y2ggdGhlIGNsb3NpbmcgdGFnLlxuICAgICAgaWYgKCFzY2FubmVyLnNjYW4oY2xvc2luZ1RhZ1JlKSlcbiAgICAgICAgdGhyb3cgbmV3IEVycm9yKCdVbmNsb3NlZCB0YWcgYXQgJyArIHNjYW5uZXIucG9zKTtcblxuICAgICAgdG9rZW4gPSBbIHR5cGUsIHZhbHVlLCBzdGFydCwgc2Nhbm5lci5wb3MgXTtcbiAgICAgIHRva2Vucy5wdXNoKHRva2VuKTtcblxuICAgICAgaWYgKHR5cGUgPT09ICcjJyB8fCB0eXBlID09PSAnXicpIHtcbiAgICAgICAgc2VjdGlvbnMucHVzaCh0b2tlbik7XG4gICAgICB9IGVsc2UgaWYgKHR5cGUgPT09ICcvJykge1xuICAgICAgICAvLyBDaGVjayBzZWN0aW9uIG5lc3RpbmcuXG4gICAgICAgIG9wZW5TZWN0aW9uID0gc2VjdGlvbnMucG9wKCk7XG5cbiAgICAgICAgaWYgKCFvcGVuU2VjdGlvbilcbiAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IoJ1Vub3BlbmVkIHNlY3Rpb24gXCInICsgdmFsdWUgKyAnXCIgYXQgJyArIHN0YXJ0KTtcblxuICAgICAgICBpZiAob3BlblNlY3Rpb25bMV0gIT09IHZhbHVlKVxuICAgICAgICAgIHRocm93IG5ldyBFcnJvcignVW5jbG9zZWQgc2VjdGlvbiBcIicgKyBvcGVuU2VjdGlvblsxXSArICdcIiBhdCAnICsgc3RhcnQpO1xuICAgICAgfSBlbHNlIGlmICh0eXBlID09PSAnbmFtZScgfHwgdHlwZSA9PT0gJ3snIHx8IHR5cGUgPT09ICcmJykge1xuICAgICAgICBub25TcGFjZSA9IHRydWU7XG4gICAgICB9IGVsc2UgaWYgKHR5cGUgPT09ICc9Jykge1xuICAgICAgICAvLyBTZXQgdGhlIHRhZ3MgZm9yIHRoZSBuZXh0IHRpbWUgYXJvdW5kLlxuICAgICAgICBjb21waWxlVGFncyh2YWx1ZSk7XG4gICAgICB9XG4gICAgfVxuXG4gICAgLy8gTWFrZSBzdXJlIHRoZXJlIGFyZSBubyBvcGVuIHNlY3Rpb25zIHdoZW4gd2UncmUgZG9uZS5cbiAgICBvcGVuU2VjdGlvbiA9IHNlY3Rpb25zLnBvcCgpO1xuXG4gICAgaWYgKG9wZW5TZWN0aW9uKVxuICAgICAgdGhyb3cgbmV3IEVycm9yKCdVbmNsb3NlZCBzZWN0aW9uIFwiJyArIG9wZW5TZWN0aW9uWzFdICsgJ1wiIGF0ICcgKyBzY2FubmVyLnBvcyk7XG5cbiAgICByZXR1cm4gbmVzdFRva2VucyhzcXVhc2hUb2tlbnModG9rZW5zKSk7XG4gIH1cblxuICAvKipcbiAgICogQ29tYmluZXMgdGhlIHZhbHVlcyBvZiBjb25zZWN1dGl2ZSB0ZXh0IHRva2VucyBpbiB0aGUgZ2l2ZW4gYHRva2Vuc2AgYXJyYXlcbiAgICogdG8gYSBzaW5nbGUgdG9rZW4uXG4gICAqL1xuICBmdW5jdGlvbiBzcXVhc2hUb2tlbnMgKHRva2Vucykge1xuICAgIHZhciBzcXVhc2hlZFRva2VucyA9IFtdO1xuXG4gICAgdmFyIHRva2VuLCBsYXN0VG9rZW47XG4gICAgZm9yICh2YXIgaSA9IDAsIG51bVRva2VucyA9IHRva2Vucy5sZW5ndGg7IGkgPCBudW1Ub2tlbnM7ICsraSkge1xuICAgICAgdG9rZW4gPSB0b2tlbnNbaV07XG5cbiAgICAgIGlmICh0b2tlbikge1xuICAgICAgICBpZiAodG9rZW5bMF0gPT09ICd0ZXh0JyAmJiBsYXN0VG9rZW4gJiYgbGFzdFRva2VuWzBdID09PSAndGV4dCcpIHtcbiAgICAgICAgICBsYXN0VG9rZW5bMV0gKz0gdG9rZW5bMV07XG4gICAgICAgICAgbGFzdFRva2VuWzNdID0gdG9rZW5bM107XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgc3F1YXNoZWRUb2tlbnMucHVzaCh0b2tlbik7XG4gICAgICAgICAgbGFzdFRva2VuID0gdG9rZW47XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG5cbiAgICByZXR1cm4gc3F1YXNoZWRUb2tlbnM7XG4gIH1cblxuICAvKipcbiAgICogRm9ybXMgdGhlIGdpdmVuIGFycmF5IG9mIGB0b2tlbnNgIGludG8gYSBuZXN0ZWQgdHJlZSBzdHJ1Y3R1cmUgd2hlcmVcbiAgICogdG9rZW5zIHRoYXQgcmVwcmVzZW50IGEgc2VjdGlvbiBoYXZlIHR3byBhZGRpdGlvbmFsIGl0ZW1zOiAxKSBhbiBhcnJheSBvZlxuICAgKiBhbGwgdG9rZW5zIHRoYXQgYXBwZWFyIGluIHRoYXQgc2VjdGlvbiBhbmQgMikgdGhlIGluZGV4IGluIHRoZSBvcmlnaW5hbFxuICAgKiB0ZW1wbGF0ZSB0aGF0IHJlcHJlc2VudHMgdGhlIGVuZCBvZiB0aGF0IHNlY3Rpb24uXG4gICAqL1xuICBmdW5jdGlvbiBuZXN0VG9rZW5zICh0b2tlbnMpIHtcbiAgICB2YXIgbmVzdGVkVG9rZW5zID0gW107XG4gICAgdmFyIGNvbGxlY3RvciA9IG5lc3RlZFRva2VucztcbiAgICB2YXIgc2VjdGlvbnMgPSBbXTtcblxuICAgIHZhciB0b2tlbiwgc2VjdGlvbjtcbiAgICBmb3IgKHZhciBpID0gMCwgbnVtVG9rZW5zID0gdG9rZW5zLmxlbmd0aDsgaSA8IG51bVRva2VuczsgKytpKSB7XG4gICAgICB0b2tlbiA9IHRva2Vuc1tpXTtcblxuICAgICAgc3dpdGNoICh0b2tlblswXSkge1xuICAgICAgICBjYXNlICcjJzpcbiAgICAgICAgY2FzZSAnXic6XG4gICAgICAgICAgY29sbGVjdG9yLnB1c2godG9rZW4pO1xuICAgICAgICAgIHNlY3Rpb25zLnB1c2godG9rZW4pO1xuICAgICAgICAgIGNvbGxlY3RvciA9IHRva2VuWzRdID0gW107XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIGNhc2UgJy8nOlxuICAgICAgICAgIHNlY3Rpb24gPSBzZWN0aW9ucy5wb3AoKTtcbiAgICAgICAgICBzZWN0aW9uWzVdID0gdG9rZW5bMl07XG4gICAgICAgICAgY29sbGVjdG9yID0gc2VjdGlvbnMubGVuZ3RoID4gMCA/IHNlY3Rpb25zW3NlY3Rpb25zLmxlbmd0aCAtIDFdWzRdIDogbmVzdGVkVG9rZW5zO1xuICAgICAgICAgIGJyZWFrO1xuICAgICAgICBkZWZhdWx0OlxuICAgICAgICAgIGNvbGxlY3Rvci5wdXNoKHRva2VuKTtcbiAgICAgIH1cbiAgICB9XG5cbiAgICByZXR1cm4gbmVzdGVkVG9rZW5zO1xuICB9XG5cbiAgLyoqXG4gICAqIEEgc2ltcGxlIHN0cmluZyBzY2FubmVyIHRoYXQgaXMgdXNlZCBieSB0aGUgdGVtcGxhdGUgcGFyc2VyIHRvIGZpbmRcbiAgICogdG9rZW5zIGluIHRlbXBsYXRlIHN0cmluZ3MuXG4gICAqL1xuICBmdW5jdGlvbiBTY2FubmVyIChzdHJpbmcpIHtcbiAgICB0aGlzLnN0cmluZyA9IHN0cmluZztcbiAgICB0aGlzLnRhaWwgPSBzdHJpbmc7XG4gICAgdGhpcy5wb3MgPSAwO1xuICB9XG5cbiAgLyoqXG4gICAqIFJldHVybnMgYHRydWVgIGlmIHRoZSB0YWlsIGlzIGVtcHR5IChlbmQgb2Ygc3RyaW5nKS5cbiAgICovXG4gIFNjYW5uZXIucHJvdG90eXBlLmVvcyA9IGZ1bmN0aW9uIGVvcyAoKSB7XG4gICAgcmV0dXJuIHRoaXMudGFpbCA9PT0gJyc7XG4gIH07XG5cbiAgLyoqXG4gICAqIFRyaWVzIHRvIG1hdGNoIHRoZSBnaXZlbiByZWd1bGFyIGV4cHJlc3Npb24gYXQgdGhlIGN1cnJlbnQgcG9zaXRpb24uXG4gICAqIFJldHVybnMgdGhlIG1hdGNoZWQgdGV4dCBpZiBpdCBjYW4gbWF0Y2gsIHRoZSBlbXB0eSBzdHJpbmcgb3RoZXJ3aXNlLlxuICAgKi9cbiAgU2Nhbm5lci5wcm90b3R5cGUuc2NhbiA9IGZ1bmN0aW9uIHNjYW4gKHJlKSB7XG4gICAgdmFyIG1hdGNoID0gdGhpcy50YWlsLm1hdGNoKHJlKTtcblxuICAgIGlmICghbWF0Y2ggfHwgbWF0Y2guaW5kZXggIT09IDApXG4gICAgICByZXR1cm4gJyc7XG5cbiAgICB2YXIgc3RyaW5nID0gbWF0Y2hbMF07XG5cbiAgICB0aGlzLnRhaWwgPSB0aGlzLnRhaWwuc3Vic3RyaW5nKHN0cmluZy5sZW5ndGgpO1xuICAgIHRoaXMucG9zICs9IHN0cmluZy5sZW5ndGg7XG5cbiAgICByZXR1cm4gc3RyaW5nO1xuICB9O1xuXG4gIC8qKlxuICAgKiBTa2lwcyBhbGwgdGV4dCB1bnRpbCB0aGUgZ2l2ZW4gcmVndWxhciBleHByZXNzaW9uIGNhbiBiZSBtYXRjaGVkLiBSZXR1cm5zXG4gICAqIHRoZSBza2lwcGVkIHN0cmluZywgd2hpY2ggaXMgdGhlIGVudGlyZSB0YWlsIGlmIG5vIG1hdGNoIGNhbiBiZSBtYWRlLlxuICAgKi9cbiAgU2Nhbm5lci5wcm90b3R5cGUuc2NhblVudGlsID0gZnVuY3Rpb24gc2NhblVudGlsIChyZSkge1xuICAgIHZhciBpbmRleCA9IHRoaXMudGFpbC5zZWFyY2gocmUpLCBtYXRjaDtcblxuICAgIHN3aXRjaCAoaW5kZXgpIHtcbiAgICAgIGNhc2UgLTE6XG4gICAgICAgIG1hdGNoID0gdGhpcy50YWlsO1xuICAgICAgICB0aGlzLnRhaWwgPSAnJztcbiAgICAgICAgYnJlYWs7XG4gICAgICBjYXNlIDA6XG4gICAgICAgIG1hdGNoID0gJyc7XG4gICAgICAgIGJyZWFrO1xuICAgICAgZGVmYXVsdDpcbiAgICAgICAgbWF0Y2ggPSB0aGlzLnRhaWwuc3Vic3RyaW5nKDAsIGluZGV4KTtcbiAgICAgICAgdGhpcy50YWlsID0gdGhpcy50YWlsLnN1YnN0cmluZyhpbmRleCk7XG4gICAgfVxuXG4gICAgdGhpcy5wb3MgKz0gbWF0Y2gubGVuZ3RoO1xuXG4gICAgcmV0dXJuIG1hdGNoO1xuICB9O1xuXG4gIC8qKlxuICAgKiBSZXByZXNlbnRzIGEgcmVuZGVyaW5nIGNvbnRleHQgYnkgd3JhcHBpbmcgYSB2aWV3IG9iamVjdCBhbmRcbiAgICogbWFpbnRhaW5pbmcgYSByZWZlcmVuY2UgdG8gdGhlIHBhcmVudCBjb250ZXh0LlxuICAgKi9cbiAgZnVuY3Rpb24gQ29udGV4dCAodmlldywgcGFyZW50Q29udGV4dCkge1xuICAgIHRoaXMudmlldyA9IHZpZXc7XG4gICAgdGhpcy5jYWNoZSA9IHsgJy4nOiB0aGlzLnZpZXcgfTtcbiAgICB0aGlzLnBhcmVudCA9IHBhcmVudENvbnRleHQ7XG4gIH1cblxuICAvKipcbiAgICogQ3JlYXRlcyBhIG5ldyBjb250ZXh0IHVzaW5nIHRoZSBnaXZlbiB2aWV3IHdpdGggdGhpcyBjb250ZXh0XG4gICAqIGFzIHRoZSBwYXJlbnQuXG4gICAqL1xuICBDb250ZXh0LnByb3RvdHlwZS5wdXNoID0gZnVuY3Rpb24gcHVzaCAodmlldykge1xuICAgIHJldHVybiBuZXcgQ29udGV4dCh2aWV3LCB0aGlzKTtcbiAgfTtcblxuICAvKipcbiAgICogUmV0dXJucyB0aGUgdmFsdWUgb2YgdGhlIGdpdmVuIG5hbWUgaW4gdGhpcyBjb250ZXh0LCB0cmF2ZXJzaW5nXG4gICAqIHVwIHRoZSBjb250ZXh0IGhpZXJhcmNoeSBpZiB0aGUgdmFsdWUgaXMgYWJzZW50IGluIHRoaXMgY29udGV4dCdzIHZpZXcuXG4gICAqL1xuICBDb250ZXh0LnByb3RvdHlwZS5sb29rdXAgPSBmdW5jdGlvbiBsb29rdXAgKG5hbWUpIHtcbiAgICB2YXIgY2FjaGUgPSB0aGlzLmNhY2hlO1xuXG4gICAgdmFyIHZhbHVlO1xuICAgIGlmIChjYWNoZS5oYXNPd25Qcm9wZXJ0eShuYW1lKSkge1xuICAgICAgdmFsdWUgPSBjYWNoZVtuYW1lXTtcbiAgICB9IGVsc2Uge1xuICAgICAgdmFyIGNvbnRleHQgPSB0aGlzLCBuYW1lcywgaW5kZXgsIGxvb2t1cEhpdCA9IGZhbHNlO1xuXG4gICAgICB3aGlsZSAoY29udGV4dCkge1xuICAgICAgICBpZiAobmFtZS5pbmRleE9mKCcuJykgPiAwKSB7XG4gICAgICAgICAgdmFsdWUgPSBjb250ZXh0LnZpZXc7XG4gICAgICAgICAgbmFtZXMgPSBuYW1lLnNwbGl0KCcuJyk7XG4gICAgICAgICAgaW5kZXggPSAwO1xuXG4gICAgICAgICAgLyoqXG4gICAgICAgICAgICogVXNpbmcgdGhlIGRvdCBub3Rpb24gcGF0aCBpbiBgbmFtZWAsIHdlIGRlc2NlbmQgdGhyb3VnaCB0aGVcbiAgICAgICAgICAgKiBuZXN0ZWQgb2JqZWN0cy5cbiAgICAgICAgICAgKlxuICAgICAgICAgICAqIFRvIGJlIGNlcnRhaW4gdGhhdCB0aGUgbG9va3VwIGhhcyBiZWVuIHN1Y2Nlc3NmdWwsIHdlIGhhdmUgdG9cbiAgICAgICAgICAgKiBjaGVjayBpZiB0aGUgbGFzdCBvYmplY3QgaW4gdGhlIHBhdGggYWN0dWFsbHkgaGFzIHRoZSBwcm9wZXJ0eVxuICAgICAgICAgICAqIHdlIGFyZSBsb29raW5nIGZvci4gV2Ugc3RvcmUgdGhlIHJlc3VsdCBpbiBgbG9va3VwSGl0YC5cbiAgICAgICAgICAgKlxuICAgICAgICAgICAqIFRoaXMgaXMgc3BlY2lhbGx5IG5lY2Vzc2FyeSBmb3Igd2hlbiB0aGUgdmFsdWUgaGFzIGJlZW4gc2V0IHRvXG4gICAgICAgICAgICogYHVuZGVmaW5lZGAgYW5kIHdlIHdhbnQgdG8gYXZvaWQgbG9va2luZyB1cCBwYXJlbnQgY29udGV4dHMuXG4gICAgICAgICAgICoqL1xuICAgICAgICAgIHdoaWxlICh2YWx1ZSAhPSBudWxsICYmIGluZGV4IDwgbmFtZXMubGVuZ3RoKSB7XG4gICAgICAgICAgICBpZiAoaW5kZXggPT09IG5hbWVzLmxlbmd0aCAtIDEpXG4gICAgICAgICAgICAgIGxvb2t1cEhpdCA9IGhhc1Byb3BlcnR5KHZhbHVlLCBuYW1lc1tpbmRleF0pO1xuXG4gICAgICAgICAgICB2YWx1ZSA9IHZhbHVlW25hbWVzW2luZGV4KytdXTtcbiAgICAgICAgICB9XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgdmFsdWUgPSBjb250ZXh0LnZpZXdbbmFtZV07XG4gICAgICAgICAgbG9va3VwSGl0ID0gaGFzUHJvcGVydHkoY29udGV4dC52aWV3LCBuYW1lKTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChsb29rdXBIaXQpXG4gICAgICAgICAgYnJlYWs7XG5cbiAgICAgICAgY29udGV4dCA9IGNvbnRleHQucGFyZW50O1xuICAgICAgfVxuXG4gICAgICBjYWNoZVtuYW1lXSA9IHZhbHVlO1xuICAgIH1cblxuICAgIGlmIChpc0Z1bmN0aW9uKHZhbHVlKSlcbiAgICAgIHZhbHVlID0gdmFsdWUuY2FsbCh0aGlzLnZpZXcpO1xuXG4gICAgcmV0dXJuIHZhbHVlO1xuICB9O1xuXG4gIC8qKlxuICAgKiBBIFdyaXRlciBrbm93cyBob3cgdG8gdGFrZSBhIHN0cmVhbSBvZiB0b2tlbnMgYW5kIHJlbmRlciB0aGVtIHRvIGFcbiAgICogc3RyaW5nLCBnaXZlbiBhIGNvbnRleHQuIEl0IGFsc28gbWFpbnRhaW5zIGEgY2FjaGUgb2YgdGVtcGxhdGVzIHRvXG4gICAqIGF2b2lkIHRoZSBuZWVkIHRvIHBhcnNlIHRoZSBzYW1lIHRlbXBsYXRlIHR3aWNlLlxuICAgKi9cbiAgZnVuY3Rpb24gV3JpdGVyICgpIHtcbiAgICB0aGlzLmNhY2hlID0ge307XG4gIH1cblxuICAvKipcbiAgICogQ2xlYXJzIGFsbCBjYWNoZWQgdGVtcGxhdGVzIGluIHRoaXMgd3JpdGVyLlxuICAgKi9cbiAgV3JpdGVyLnByb3RvdHlwZS5jbGVhckNhY2hlID0gZnVuY3Rpb24gY2xlYXJDYWNoZSAoKSB7XG4gICAgdGhpcy5jYWNoZSA9IHt9O1xuICB9O1xuXG4gIC8qKlxuICAgKiBQYXJzZXMgYW5kIGNhY2hlcyB0aGUgZ2l2ZW4gYHRlbXBsYXRlYCBhbmQgcmV0dXJucyB0aGUgYXJyYXkgb2YgdG9rZW5zXG4gICAqIHRoYXQgaXMgZ2VuZXJhdGVkIGZyb20gdGhlIHBhcnNlLlxuICAgKi9cbiAgV3JpdGVyLnByb3RvdHlwZS5wYXJzZSA9IGZ1bmN0aW9uIHBhcnNlICh0ZW1wbGF0ZSwgdGFncykge1xuICAgIHZhciBjYWNoZSA9IHRoaXMuY2FjaGU7XG4gICAgdmFyIHRva2VucyA9IGNhY2hlW3RlbXBsYXRlXTtcblxuICAgIGlmICh0b2tlbnMgPT0gbnVsbClcbiAgICAgIHRva2VucyA9IGNhY2hlW3RlbXBsYXRlXSA9IHBhcnNlVGVtcGxhdGUodGVtcGxhdGUsIHRhZ3MpO1xuXG4gICAgcmV0dXJuIHRva2VucztcbiAgfTtcblxuICAvKipcbiAgICogSGlnaC1sZXZlbCBtZXRob2QgdGhhdCBpcyB1c2VkIHRvIHJlbmRlciB0aGUgZ2l2ZW4gYHRlbXBsYXRlYCB3aXRoXG4gICAqIHRoZSBnaXZlbiBgdmlld2AuXG4gICAqXG4gICAqIFRoZSBvcHRpb25hbCBgcGFydGlhbHNgIGFyZ3VtZW50IG1heSBiZSBhbiBvYmplY3QgdGhhdCBjb250YWlucyB0aGVcbiAgICogbmFtZXMgYW5kIHRlbXBsYXRlcyBvZiBwYXJ0aWFscyB0aGF0IGFyZSB1c2VkIGluIHRoZSB0ZW1wbGF0ZS4gSXQgbWF5XG4gICAqIGFsc28gYmUgYSBmdW5jdGlvbiB0aGF0IGlzIHVzZWQgdG8gbG9hZCBwYXJ0aWFsIHRlbXBsYXRlcyBvbiB0aGUgZmx5XG4gICAqIHRoYXQgdGFrZXMgYSBzaW5nbGUgYXJndW1lbnQ6IHRoZSBuYW1lIG9mIHRoZSBwYXJ0aWFsLlxuICAgKi9cbiAgV3JpdGVyLnByb3RvdHlwZS5yZW5kZXIgPSBmdW5jdGlvbiByZW5kZXIgKHRlbXBsYXRlLCB2aWV3LCBwYXJ0aWFscykge1xuICAgIHZhciB0b2tlbnMgPSB0aGlzLnBhcnNlKHRlbXBsYXRlKTtcbiAgICB2YXIgY29udGV4dCA9ICh2aWV3IGluc3RhbmNlb2YgQ29udGV4dCkgPyB2aWV3IDogbmV3IENvbnRleHQodmlldyk7XG4gICAgcmV0dXJuIHRoaXMucmVuZGVyVG9rZW5zKHRva2VucywgY29udGV4dCwgcGFydGlhbHMsIHRlbXBsYXRlKTtcbiAgfTtcblxuICAvKipcbiAgICogTG93LWxldmVsIG1ldGhvZCB0aGF0IHJlbmRlcnMgdGhlIGdpdmVuIGFycmF5IG9mIGB0b2tlbnNgIHVzaW5nXG4gICAqIHRoZSBnaXZlbiBgY29udGV4dGAgYW5kIGBwYXJ0aWFsc2AuXG4gICAqXG4gICAqIE5vdGU6IFRoZSBgb3JpZ2luYWxUZW1wbGF0ZWAgaXMgb25seSBldmVyIHVzZWQgdG8gZXh0cmFjdCB0aGUgcG9ydGlvblxuICAgKiBvZiB0aGUgb3JpZ2luYWwgdGVtcGxhdGUgdGhhdCB3YXMgY29udGFpbmVkIGluIGEgaGlnaGVyLW9yZGVyIHNlY3Rpb24uXG4gICAqIElmIHRoZSB0ZW1wbGF0ZSBkb2Vzbid0IHVzZSBoaWdoZXItb3JkZXIgc2VjdGlvbnMsIHRoaXMgYXJndW1lbnQgbWF5XG4gICAqIGJlIG9taXR0ZWQuXG4gICAqL1xuICBXcml0ZXIucHJvdG90eXBlLnJlbmRlclRva2VucyA9IGZ1bmN0aW9uIHJlbmRlclRva2VucyAodG9rZW5zLCBjb250ZXh0LCBwYXJ0aWFscywgb3JpZ2luYWxUZW1wbGF0ZSkge1xuICAgIHZhciBidWZmZXIgPSAnJztcblxuICAgIHZhciB0b2tlbiwgc3ltYm9sLCB2YWx1ZTtcbiAgICBmb3IgKHZhciBpID0gMCwgbnVtVG9rZW5zID0gdG9rZW5zLmxlbmd0aDsgaSA8IG51bVRva2VuczsgKytpKSB7XG4gICAgICB2YWx1ZSA9IHVuZGVmaW5lZDtcbiAgICAgIHRva2VuID0gdG9rZW5zW2ldO1xuICAgICAgc3ltYm9sID0gdG9rZW5bMF07XG5cbiAgICAgIGlmIChzeW1ib2wgPT09ICcjJykgdmFsdWUgPSB0aGlzLnJlbmRlclNlY3Rpb24odG9rZW4sIGNvbnRleHQsIHBhcnRpYWxzLCBvcmlnaW5hbFRlbXBsYXRlKTtcbiAgICAgIGVsc2UgaWYgKHN5bWJvbCA9PT0gJ14nKSB2YWx1ZSA9IHRoaXMucmVuZGVySW52ZXJ0ZWQodG9rZW4sIGNvbnRleHQsIHBhcnRpYWxzLCBvcmlnaW5hbFRlbXBsYXRlKTtcbiAgICAgIGVsc2UgaWYgKHN5bWJvbCA9PT0gJz4nKSB2YWx1ZSA9IHRoaXMucmVuZGVyUGFydGlhbCh0b2tlbiwgY29udGV4dCwgcGFydGlhbHMsIG9yaWdpbmFsVGVtcGxhdGUpO1xuICAgICAgZWxzZSBpZiAoc3ltYm9sID09PSAnJicpIHZhbHVlID0gdGhpcy51bmVzY2FwZWRWYWx1ZSh0b2tlbiwgY29udGV4dCk7XG4gICAgICBlbHNlIGlmIChzeW1ib2wgPT09ICduYW1lJykgdmFsdWUgPSB0aGlzLmVzY2FwZWRWYWx1ZSh0b2tlbiwgY29udGV4dCk7XG4gICAgICBlbHNlIGlmIChzeW1ib2wgPT09ICd0ZXh0JykgdmFsdWUgPSB0aGlzLnJhd1ZhbHVlKHRva2VuKTtcblxuICAgICAgaWYgKHZhbHVlICE9PSB1bmRlZmluZWQpXG4gICAgICAgIGJ1ZmZlciArPSB2YWx1ZTtcbiAgICB9XG5cbiAgICByZXR1cm4gYnVmZmVyO1xuICB9O1xuXG4gIFdyaXRlci5wcm90b3R5cGUucmVuZGVyU2VjdGlvbiA9IGZ1bmN0aW9uIHJlbmRlclNlY3Rpb24gKHRva2VuLCBjb250ZXh0LCBwYXJ0aWFscywgb3JpZ2luYWxUZW1wbGF0ZSkge1xuICAgIHZhciBzZWxmID0gdGhpcztcbiAgICB2YXIgYnVmZmVyID0gJyc7XG4gICAgdmFyIHZhbHVlID0gY29udGV4dC5sb29rdXAodG9rZW5bMV0pO1xuXG4gICAgLy8gVGhpcyBmdW5jdGlvbiBpcyB1c2VkIHRvIHJlbmRlciBhbiBhcmJpdHJhcnkgdGVtcGxhdGVcbiAgICAvLyBpbiB0aGUgY3VycmVudCBjb250ZXh0IGJ5IGhpZ2hlci1vcmRlciBzZWN0aW9ucy5cbiAgICBmdW5jdGlvbiBzdWJSZW5kZXIgKHRlbXBsYXRlKSB7XG4gICAgICByZXR1cm4gc2VsZi5yZW5kZXIodGVtcGxhdGUsIGNvbnRleHQsIHBhcnRpYWxzKTtcbiAgICB9XG5cbiAgICBpZiAoIXZhbHVlKSByZXR1cm47XG5cbiAgICBpZiAoaXNBcnJheSh2YWx1ZSkpIHtcbiAgICAgIGZvciAodmFyIGogPSAwLCB2YWx1ZUxlbmd0aCA9IHZhbHVlLmxlbmd0aDsgaiA8IHZhbHVlTGVuZ3RoOyArK2opIHtcbiAgICAgICAgYnVmZmVyICs9IHRoaXMucmVuZGVyVG9rZW5zKHRva2VuWzRdLCBjb250ZXh0LnB1c2godmFsdWVbal0pLCBwYXJ0aWFscywgb3JpZ2luYWxUZW1wbGF0ZSk7XG4gICAgICB9XG4gICAgfSBlbHNlIGlmICh0eXBlb2YgdmFsdWUgPT09ICdvYmplY3QnIHx8IHR5cGVvZiB2YWx1ZSA9PT0gJ3N0cmluZycgfHwgdHlwZW9mIHZhbHVlID09PSAnbnVtYmVyJykge1xuICAgICAgYnVmZmVyICs9IHRoaXMucmVuZGVyVG9rZW5zKHRva2VuWzRdLCBjb250ZXh0LnB1c2godmFsdWUpLCBwYXJ0aWFscywgb3JpZ2luYWxUZW1wbGF0ZSk7XG4gICAgfSBlbHNlIGlmIChpc0Z1bmN0aW9uKHZhbHVlKSkge1xuICAgICAgaWYgKHR5cGVvZiBvcmlnaW5hbFRlbXBsYXRlICE9PSAnc3RyaW5nJylcbiAgICAgICAgdGhyb3cgbmV3IEVycm9yKCdDYW5ub3QgdXNlIGhpZ2hlci1vcmRlciBzZWN0aW9ucyB3aXRob3V0IHRoZSBvcmlnaW5hbCB0ZW1wbGF0ZScpO1xuXG4gICAgICAvLyBFeHRyYWN0IHRoZSBwb3J0aW9uIG9mIHRoZSBvcmlnaW5hbCB0ZW1wbGF0ZSB0aGF0IHRoZSBzZWN0aW9uIGNvbnRhaW5zLlxuICAgICAgdmFsdWUgPSB2YWx1ZS5jYWxsKGNvbnRleHQudmlldywgb3JpZ2luYWxUZW1wbGF0ZS5zbGljZSh0b2tlblszXSwgdG9rZW5bNV0pLCBzdWJSZW5kZXIpO1xuXG4gICAgICBpZiAodmFsdWUgIT0gbnVsbClcbiAgICAgICAgYnVmZmVyICs9IHZhbHVlO1xuICAgIH0gZWxzZSB7XG4gICAgICBidWZmZXIgKz0gdGhpcy5yZW5kZXJUb2tlbnModG9rZW5bNF0sIGNvbnRleHQsIHBhcnRpYWxzLCBvcmlnaW5hbFRlbXBsYXRlKTtcbiAgICB9XG4gICAgcmV0dXJuIGJ1ZmZlcjtcbiAgfTtcblxuICBXcml0ZXIucHJvdG90eXBlLnJlbmRlckludmVydGVkID0gZnVuY3Rpb24gcmVuZGVySW52ZXJ0ZWQgKHRva2VuLCBjb250ZXh0LCBwYXJ0aWFscywgb3JpZ2luYWxUZW1wbGF0ZSkge1xuICAgIHZhciB2YWx1ZSA9IGNvbnRleHQubG9va3VwKHRva2VuWzFdKTtcblxuICAgIC8vIFVzZSBKYXZhU2NyaXB0J3MgZGVmaW5pdGlvbiBvZiBmYWxzeS4gSW5jbHVkZSBlbXB0eSBhcnJheXMuXG4gICAgLy8gU2VlIGh0dHBzOi8vZ2l0aHViLmNvbS9qYW5sL211c3RhY2hlLmpzL2lzc3Vlcy8xODZcbiAgICBpZiAoIXZhbHVlIHx8IChpc0FycmF5KHZhbHVlKSAmJiB2YWx1ZS5sZW5ndGggPT09IDApKVxuICAgICAgcmV0dXJuIHRoaXMucmVuZGVyVG9rZW5zKHRva2VuWzRdLCBjb250ZXh0LCBwYXJ0aWFscywgb3JpZ2luYWxUZW1wbGF0ZSk7XG4gIH07XG5cbiAgV3JpdGVyLnByb3RvdHlwZS5yZW5kZXJQYXJ0aWFsID0gZnVuY3Rpb24gcmVuZGVyUGFydGlhbCAodG9rZW4sIGNvbnRleHQsIHBhcnRpYWxzKSB7XG4gICAgaWYgKCFwYXJ0aWFscykgcmV0dXJuO1xuXG4gICAgdmFyIHZhbHVlID0gaXNGdW5jdGlvbihwYXJ0aWFscykgPyBwYXJ0aWFscyh0b2tlblsxXSkgOiBwYXJ0aWFsc1t0b2tlblsxXV07XG4gICAgaWYgKHZhbHVlICE9IG51bGwpXG4gICAgICByZXR1cm4gdGhpcy5yZW5kZXJUb2tlbnModGhpcy5wYXJzZSh2YWx1ZSksIGNvbnRleHQsIHBhcnRpYWxzLCB2YWx1ZSk7XG4gIH07XG5cbiAgV3JpdGVyLnByb3RvdHlwZS51bmVzY2FwZWRWYWx1ZSA9IGZ1bmN0aW9uIHVuZXNjYXBlZFZhbHVlICh0b2tlbiwgY29udGV4dCkge1xuICAgIHZhciB2YWx1ZSA9IGNvbnRleHQubG9va3VwKHRva2VuWzFdKTtcbiAgICBpZiAodmFsdWUgIT0gbnVsbClcbiAgICAgIHJldHVybiB2YWx1ZTtcbiAgfTtcblxuICBXcml0ZXIucHJvdG90eXBlLmVzY2FwZWRWYWx1ZSA9IGZ1bmN0aW9uIGVzY2FwZWRWYWx1ZSAodG9rZW4sIGNvbnRleHQpIHtcbiAgICB2YXIgdmFsdWUgPSBjb250ZXh0Lmxvb2t1cCh0b2tlblsxXSk7XG4gICAgaWYgKHZhbHVlICE9IG51bGwpXG4gICAgICByZXR1cm4gbXVzdGFjaGUuZXNjYXBlKHZhbHVlKTtcbiAgfTtcblxuICBXcml0ZXIucHJvdG90eXBlLnJhd1ZhbHVlID0gZnVuY3Rpb24gcmF3VmFsdWUgKHRva2VuKSB7XG4gICAgcmV0dXJuIHRva2VuWzFdO1xuICB9O1xuXG4gIG11c3RhY2hlLm5hbWUgPSAnbXVzdGFjaGUuanMnO1xuICBtdXN0YWNoZS52ZXJzaW9uID0gJzIuMi4xJztcbiAgbXVzdGFjaGUudGFncyA9IFsgJ3t7JywgJ319JyBdO1xuXG4gIC8vIEFsbCBoaWdoLWxldmVsIG11c3RhY2hlLiogZnVuY3Rpb25zIHVzZSB0aGlzIHdyaXRlci5cbiAgdmFyIGRlZmF1bHRXcml0ZXIgPSBuZXcgV3JpdGVyKCk7XG5cbiAgLyoqXG4gICAqIENsZWFycyBhbGwgY2FjaGVkIHRlbXBsYXRlcyBpbiB0aGUgZGVmYXVsdCB3cml0ZXIuXG4gICAqL1xuICBtdXN0YWNoZS5jbGVhckNhY2hlID0gZnVuY3Rpb24gY2xlYXJDYWNoZSAoKSB7XG4gICAgcmV0dXJuIGRlZmF1bHRXcml0ZXIuY2xlYXJDYWNoZSgpO1xuICB9O1xuXG4gIC8qKlxuICAgKiBQYXJzZXMgYW5kIGNhY2hlcyB0aGUgZ2l2ZW4gdGVtcGxhdGUgaW4gdGhlIGRlZmF1bHQgd3JpdGVyIGFuZCByZXR1cm5zIHRoZVxuICAgKiBhcnJheSBvZiB0b2tlbnMgaXQgY29udGFpbnMuIERvaW5nIHRoaXMgYWhlYWQgb2YgdGltZSBhdm9pZHMgdGhlIG5lZWQgdG9cbiAgICogcGFyc2UgdGVtcGxhdGVzIG9uIHRoZSBmbHkgYXMgdGhleSBhcmUgcmVuZGVyZWQuXG4gICAqL1xuICBtdXN0YWNoZS5wYXJzZSA9IGZ1bmN0aW9uIHBhcnNlICh0ZW1wbGF0ZSwgdGFncykge1xuICAgIHJldHVybiBkZWZhdWx0V3JpdGVyLnBhcnNlKHRlbXBsYXRlLCB0YWdzKTtcbiAgfTtcblxuICAvKipcbiAgICogUmVuZGVycyB0aGUgYHRlbXBsYXRlYCB3aXRoIHRoZSBnaXZlbiBgdmlld2AgYW5kIGBwYXJ0aWFsc2AgdXNpbmcgdGhlXG4gICAqIGRlZmF1bHQgd3JpdGVyLlxuICAgKi9cbiAgbXVzdGFjaGUucmVuZGVyID0gZnVuY3Rpb24gcmVuZGVyICh0ZW1wbGF0ZSwgdmlldywgcGFydGlhbHMpIHtcbiAgICBpZiAodHlwZW9mIHRlbXBsYXRlICE9PSAnc3RyaW5nJykge1xuICAgICAgdGhyb3cgbmV3IFR5cGVFcnJvcignSW52YWxpZCB0ZW1wbGF0ZSEgVGVtcGxhdGUgc2hvdWxkIGJlIGEgXCJzdHJpbmdcIiAnICtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgJ2J1dCBcIicgKyB0eXBlU3RyKHRlbXBsYXRlKSArICdcIiB3YXMgZ2l2ZW4gYXMgdGhlIGZpcnN0ICcgK1xuICAgICAgICAgICAgICAgICAgICAgICAgICAnYXJndW1lbnQgZm9yIG11c3RhY2hlI3JlbmRlcih0ZW1wbGF0ZSwgdmlldywgcGFydGlhbHMpJyk7XG4gICAgfVxuXG4gICAgcmV0dXJuIGRlZmF1bHRXcml0ZXIucmVuZGVyKHRlbXBsYXRlLCB2aWV3LCBwYXJ0aWFscyk7XG4gIH07XG5cbiAgLy8gVGhpcyBpcyBoZXJlIGZvciBiYWNrd2FyZHMgY29tcGF0aWJpbGl0eSB3aXRoIDAuNC54LixcbiAgLyplc2xpbnQtZGlzYWJsZSAqLyAvLyBlc2xpbnQgd2FudHMgY2FtZWwgY2FzZWQgZnVuY3Rpb24gbmFtZVxuICBtdXN0YWNoZS50b19odG1sID0gZnVuY3Rpb24gdG9faHRtbCAodGVtcGxhdGUsIHZpZXcsIHBhcnRpYWxzLCBzZW5kKSB7XG4gICAgLyplc2xpbnQtZW5hYmxlKi9cblxuICAgIHZhciByZXN1bHQgPSBtdXN0YWNoZS5yZW5kZXIodGVtcGxhdGUsIHZpZXcsIHBhcnRpYWxzKTtcblxuICAgIGlmIChpc0Z1bmN0aW9uKHNlbmQpKSB7XG4gICAgICBzZW5kKHJlc3VsdCk7XG4gICAgfSBlbHNlIHtcbiAgICAgIHJldHVybiByZXN1bHQ7XG4gICAgfVxuICB9O1xuXG4gIC8vIEV4cG9ydCB0aGUgZXNjYXBpbmcgZnVuY3Rpb24gc28gdGhhdCB0aGUgdXNlciBtYXkgb3ZlcnJpZGUgaXQuXG4gIC8vIFNlZSBodHRwczovL2dpdGh1Yi5jb20vamFubC9tdXN0YWNoZS5qcy9pc3N1ZXMvMjQ0XG4gIG11c3RhY2hlLmVzY2FwZSA9IGVzY2FwZUh0bWw7XG5cbiAgLy8gRXhwb3J0IHRoZXNlIG1haW5seSBmb3IgdGVzdGluZywgYnV0IGFsc28gZm9yIGFkdmFuY2VkIHVzYWdlLlxuICBtdXN0YWNoZS5TY2FubmVyID0gU2Nhbm5lcjtcbiAgbXVzdGFjaGUuQ29udGV4dCA9IENvbnRleHQ7XG4gIG11c3RhY2hlLldyaXRlciA9IFdyaXRlcjtcblxufSkpO1xuIiwidmFyIGh0bWxfc2FuaXRpemUgPSByZXF1aXJlKCcuL3Nhbml0aXplci1idW5kbGUuanMnKTtcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihfKSB7XG4gICAgaWYgKCFfKSByZXR1cm4gJyc7XG4gICAgcmV0dXJuIGh0bWxfc2FuaXRpemUoXywgY2xlYW5VcmwsIGNsZWFuSWQpO1xufTtcblxuLy8gaHR0cHM6Ly9idWd6aWxsYS5tb3ppbGxhLm9yZy9zaG93X2J1Zy5jZ2k/aWQ9MjU1MTA3XG5mdW5jdGlvbiBjbGVhblVybCh1cmwpIHtcbiAgICAndXNlIHN0cmljdCc7XG4gICAgaWYgKC9eaHR0cHM/Ly50ZXN0KHVybC5nZXRTY2hlbWUoKSkpIHJldHVybiB1cmwudG9TdHJpbmcoKTtcbiAgICBpZiAoL15tYWlsdG8/Ly50ZXN0KHVybC5nZXRTY2hlbWUoKSkpIHJldHVybiB1cmwudG9TdHJpbmcoKTtcbiAgICBpZiAoJ2RhdGEnID09IHVybC5nZXRTY2hlbWUoKSAmJiAvXmltYWdlLy50ZXN0KHVybC5nZXRQYXRoKCkpKSB7XG4gICAgICAgIHJldHVybiB1cmwudG9TdHJpbmcoKTtcbiAgICB9XG59XG5cbmZ1bmN0aW9uIGNsZWFuSWQoaWQpIHsgcmV0dXJuIGlkOyB9XG4iLCJcbi8vIENvcHlyaWdodCAoQykgMjAxMCBHb29nbGUgSW5jLlxuLy9cbi8vIExpY2Vuc2VkIHVuZGVyIHRoZSBBcGFjaGUgTGljZW5zZSwgVmVyc2lvbiAyLjAgKHRoZSBcIkxpY2Vuc2VcIik7XG4vLyB5b3UgbWF5IG5vdCB1c2UgdGhpcyBmaWxlIGV4Y2VwdCBpbiBjb21wbGlhbmNlIHdpdGggdGhlIExpY2Vuc2UuXG4vLyBZb3UgbWF5IG9idGFpbiBhIGNvcHkgb2YgdGhlIExpY2Vuc2UgYXRcbi8vXG4vLyAgICAgIGh0dHA6Ly93d3cuYXBhY2hlLm9yZy9saWNlbnNlcy9MSUNFTlNFLTIuMFxuLy9cbi8vIFVubGVzcyByZXF1aXJlZCBieSBhcHBsaWNhYmxlIGxhdyBvciBhZ3JlZWQgdG8gaW4gd3JpdGluZywgc29mdHdhcmVcbi8vIGRpc3RyaWJ1dGVkIHVuZGVyIHRoZSBMaWNlbnNlIGlzIGRpc3RyaWJ1dGVkIG9uIGFuIFwiQVMgSVNcIiBCQVNJUyxcbi8vIFdJVEhPVVQgV0FSUkFOVElFUyBPUiBDT05ESVRJT05TIE9GIEFOWSBLSU5ELCBlaXRoZXIgZXhwcmVzcyBvciBpbXBsaWVkLlxuLy8gU2VlIHRoZSBMaWNlbnNlIGZvciB0aGUgc3BlY2lmaWMgbGFuZ3VhZ2UgZ292ZXJuaW5nIHBlcm1pc3Npb25zIGFuZFxuLy8gbGltaXRhdGlvbnMgdW5kZXIgdGhlIExpY2Vuc2UuXG5cbi8qKlxuICogQGZpbGVvdmVydmlld1xuICogSW1wbGVtZW50cyBSRkMgMzk4NiBmb3IgcGFyc2luZy9mb3JtYXR0aW5nIFVSSXMuXG4gKlxuICogQGF1dGhvciBtaWtlc2FtdWVsQGdtYWlsLmNvbVxuICogXFxAcHJvdmlkZXMgVVJJXG4gKiBcXEBvdmVycmlkZXMgd2luZG93XG4gKi9cblxudmFyIFVSSSA9IChmdW5jdGlvbiAoKSB7XG5cbi8qKlxuICogY3JlYXRlcyBhIHVyaSBmcm9tIHRoZSBzdHJpbmcgZm9ybS4gIFRoZSBwYXJzZXIgaXMgcmVsYXhlZCwgc28gc3BlY2lhbFxuICogY2hhcmFjdGVycyB0aGF0IGFyZW4ndCBlc2NhcGVkIGJ1dCBkb24ndCBjYXVzZSBhbWJpZ3VpdGllcyB3aWxsIG5vdCBjYXVzZVxuICogcGFyc2UgZmFpbHVyZXMuXG4gKlxuICogQHJldHVybiB7VVJJfG51bGx9XG4gKi9cbmZ1bmN0aW9uIHBhcnNlKHVyaVN0cikge1xuICB2YXIgbSA9ICgnJyArIHVyaVN0cikubWF0Y2goVVJJX1JFXyk7XG4gIGlmICghbSkgeyByZXR1cm4gbnVsbDsgfVxuICByZXR1cm4gbmV3IFVSSShcbiAgICAgIG51bGxJZkFic2VudChtWzFdKSxcbiAgICAgIG51bGxJZkFic2VudChtWzJdKSxcbiAgICAgIG51bGxJZkFic2VudChtWzNdKSxcbiAgICAgIG51bGxJZkFic2VudChtWzRdKSxcbiAgICAgIG51bGxJZkFic2VudChtWzVdKSxcbiAgICAgIG51bGxJZkFic2VudChtWzZdKSxcbiAgICAgIG51bGxJZkFic2VudChtWzddKSk7XG59XG5cblxuLyoqXG4gKiBjcmVhdGVzIGEgdXJpIGZyb20gdGhlIGdpdmVuIHBhcnRzLlxuICpcbiAqIEBwYXJhbSBzY2hlbWUge3N0cmluZ30gYW4gdW5lbmNvZGVkIHNjaGVtZSBzdWNoIGFzIFwiaHR0cFwiIG9yIG51bGxcbiAqIEBwYXJhbSBjcmVkZW50aWFscyB7c3RyaW5nfSB1bmVuY29kZWQgdXNlciBjcmVkZW50aWFscyBvciBudWxsXG4gKiBAcGFyYW0gZG9tYWluIHtzdHJpbmd9IGFuIHVuZW5jb2RlZCBkb21haW4gbmFtZSBvciBudWxsXG4gKiBAcGFyYW0gcG9ydCB7bnVtYmVyfSBhIHBvcnQgbnVtYmVyIGluIFsxLCAzMjc2OF0uXG4gKiAgICAtMSBpbmRpY2F0ZXMgbm8gcG9ydCwgYXMgZG9lcyBudWxsLlxuICogQHBhcmFtIHBhdGgge3N0cmluZ30gYW4gdW5lbmNvZGVkIHBhdGhcbiAqIEBwYXJhbSBxdWVyeSB7QXJyYXkuPHN0cmluZz58c3RyaW5nfG51bGx9IGEgbGlzdCBvZiB1bmVuY29kZWQgY2dpXG4gKiAgIHBhcmFtZXRlcnMgd2hlcmUgZXZlbiB2YWx1ZXMgYXJlIGtleXMgYW5kIG9kZHMgdGhlIGNvcnJlc3BvbmRpbmcgdmFsdWVzXG4gKiAgIG9yIGFuIHVuZW5jb2RlZCBxdWVyeS5cbiAqIEBwYXJhbSBmcmFnbWVudCB7c3RyaW5nfSBhbiB1bmVuY29kZWQgZnJhZ21lbnQgd2l0aG91dCB0aGUgXCIjXCIgb3IgbnVsbC5cbiAqIEByZXR1cm4ge1VSSX1cbiAqL1xuZnVuY3Rpb24gY3JlYXRlKHNjaGVtZSwgY3JlZGVudGlhbHMsIGRvbWFpbiwgcG9ydCwgcGF0aCwgcXVlcnksIGZyYWdtZW50KSB7XG4gIHZhciB1cmkgPSBuZXcgVVJJKFxuICAgICAgZW5jb2RlSWZFeGlzdHMyKHNjaGVtZSwgVVJJX0RJU0FMTE9XRURfSU5fU0NIRU1FX09SX0NSRURFTlRJQUxTXyksXG4gICAgICBlbmNvZGVJZkV4aXN0czIoXG4gICAgICAgICAgY3JlZGVudGlhbHMsIFVSSV9ESVNBTExPV0VEX0lOX1NDSEVNRV9PUl9DUkVERU5USUFMU18pLFxuICAgICAgZW5jb2RlSWZFeGlzdHMoZG9tYWluKSxcbiAgICAgIHBvcnQgPiAwID8gcG9ydC50b1N0cmluZygpIDogbnVsbCxcbiAgICAgIGVuY29kZUlmRXhpc3RzMihwYXRoLCBVUklfRElTQUxMT1dFRF9JTl9QQVRIXyksXG4gICAgICBudWxsLFxuICAgICAgZW5jb2RlSWZFeGlzdHMoZnJhZ21lbnQpKTtcbiAgaWYgKHF1ZXJ5KSB7XG4gICAgaWYgKCdzdHJpbmcnID09PSB0eXBlb2YgcXVlcnkpIHtcbiAgICAgIHVyaS5zZXRSYXdRdWVyeShxdWVyeS5yZXBsYWNlKC9bXj8mPTAtOUEtWmEtel9cXC1+LiVdL2csIGVuY29kZU9uZSkpO1xuICAgIH0gZWxzZSB7XG4gICAgICB1cmkuc2V0QWxsUGFyYW1ldGVycyhxdWVyeSk7XG4gICAgfVxuICB9XG4gIHJldHVybiB1cmk7XG59XG5mdW5jdGlvbiBlbmNvZGVJZkV4aXN0cyh1bmVzY2FwZWRQYXJ0KSB7XG4gIGlmICgnc3RyaW5nJyA9PSB0eXBlb2YgdW5lc2NhcGVkUGFydCkge1xuICAgIHJldHVybiBlbmNvZGVVUklDb21wb25lbnQodW5lc2NhcGVkUGFydCk7XG4gIH1cbiAgcmV0dXJuIG51bGw7XG59O1xuLyoqXG4gKiBpZiB1bmVzY2FwZWRQYXJ0IGlzIG5vbiBudWxsLCB0aGVuIGVzY2FwZXMgYW55IGNoYXJhY3RlcnMgaW4gaXQgdGhhdCBhcmVuJ3RcbiAqIHZhbGlkIGNoYXJhY3RlcnMgaW4gYSB1cmwgYW5kIGFsc28gZXNjYXBlcyBhbnkgc3BlY2lhbCBjaGFyYWN0ZXJzIHRoYXRcbiAqIGFwcGVhciBpbiBleHRyYS5cbiAqXG4gKiBAcGFyYW0gdW5lc2NhcGVkUGFydCB7c3RyaW5nfVxuICogQHBhcmFtIGV4dHJhIHtSZWdFeHB9IGEgY2hhcmFjdGVyIHNldCBvZiBjaGFyYWN0ZXJzIGluIFtcXDAxLVxcMTc3XS5cbiAqIEByZXR1cm4ge3N0cmluZ3xudWxsfSBudWxsIGlmZiB1bmVzY2FwZWRQYXJ0ID09IG51bGwuXG4gKi9cbmZ1bmN0aW9uIGVuY29kZUlmRXhpc3RzMih1bmVzY2FwZWRQYXJ0LCBleHRyYSkge1xuICBpZiAoJ3N0cmluZycgPT0gdHlwZW9mIHVuZXNjYXBlZFBhcnQpIHtcbiAgICByZXR1cm4gZW5jb2RlVVJJKHVuZXNjYXBlZFBhcnQpLnJlcGxhY2UoZXh0cmEsIGVuY29kZU9uZSk7XG4gIH1cbiAgcmV0dXJuIG51bGw7XG59O1xuLyoqIGNvbnZlcnRzIGEgY2hhcmFjdGVyIGluIFtcXDAxLVxcMTc3XSB0byBpdHMgdXJsIGVuY29kZWQgZXF1aXZhbGVudC4gKi9cbmZ1bmN0aW9uIGVuY29kZU9uZShjaCkge1xuICB2YXIgbiA9IGNoLmNoYXJDb2RlQXQoMCk7XG4gIHJldHVybiAnJScgKyAnMDEyMzQ1Njc4OUFCQ0RFRicuY2hhckF0KChuID4+IDQpICYgMHhmKSArXG4gICAgICAnMDEyMzQ1Njc4OUFCQ0RFRicuY2hhckF0KG4gJiAweGYpO1xufVxuXG4vKipcbiAqIHtAdXBkb2NcbiAqICAkIG5vcm1QYXRoKCdmb28vLi9iYXInKVxuICogICMgJ2Zvby9iYXInXG4gKiAgJCBub3JtUGF0aCgnLi9mb28nKVxuICogICMgJ2ZvbydcbiAqICAkIG5vcm1QYXRoKCdmb28vLicpXG4gKiAgIyAnZm9vJ1xuICogICQgbm9ybVBhdGgoJ2Zvby8vYmFyJylcbiAqICAjICdmb28vYmFyJ1xuICogfVxuICovXG5mdW5jdGlvbiBub3JtUGF0aChwYXRoKSB7XG4gIHJldHVybiBwYXRoLnJlcGxhY2UoLyhefFxcLylcXC4oPzpcXC98JCkvZywgJyQxJykucmVwbGFjZSgvXFwvezIsfS9nLCAnLycpO1xufVxuXG52YXIgUEFSRU5UX0RJUkVDVE9SWV9IQU5ETEVSID0gbmV3IFJlZ0V4cChcbiAgICAnJ1xuICAgIC8vIEEgcGF0aCBicmVha1xuICAgICsgJygvfF4pJ1xuICAgIC8vIGZvbGxvd2VkIGJ5IGEgbm9uIC4uIHBhdGggZWxlbWVudFxuICAgIC8vIChjYW5ub3QgYmUgLiBiZWNhdXNlIG5vcm1QYXRoIGlzIHVzZWQgcHJpb3IgdG8gdGhpcyBSZWdFeHApXG4gICAgKyAnKD86W14uL11bXi9dKnxcXFxcLnsyLH0oPzpbXi4vXVteL10qKXxcXFxcLnszLH1bXi9dKiknXG4gICAgLy8gZm9sbG93ZWQgYnkgLi4gZm9sbG93ZWQgYnkgYSBwYXRoIGJyZWFrLlxuICAgICsgJy9cXFxcLlxcXFwuKD86L3wkKScpO1xuXG52YXIgUEFSRU5UX0RJUkVDVE9SWV9IQU5ETEVSX1JFID0gbmV3IFJlZ0V4cChQQVJFTlRfRElSRUNUT1JZX0hBTkRMRVIpO1xuXG52YXIgRVhUUkFfUEFSRU5UX1BBVEhTX1JFID0gL14oPzpcXC5cXC5cXC8pKig/OlxcLlxcLiQpPy87XG5cbi8qKlxuICogTm9ybWFsaXplcyBpdHMgaW5wdXQgcGF0aCBhbmQgY29sbGFwc2VzIGFsbCAuIGFuZCAuLiBzZXF1ZW5jZXMgZXhjZXB0IGZvclxuICogLi4gc2VxdWVuY2VzIHRoYXQgd291bGQgdGFrZSBpdCBhYm92ZSB0aGUgcm9vdCBvZiB0aGUgY3VycmVudCBwYXJlbnRcbiAqIGRpcmVjdG9yeS5cbiAqIHtAdXBkb2NcbiAqICAkIGNvbGxhcHNlX2RvdHMoJ2Zvby8uLi9iYXInKVxuICogICMgJ2JhcidcbiAqICAkIGNvbGxhcHNlX2RvdHMoJ2Zvby8uL2JhcicpXG4gKiAgIyAnZm9vL2JhcidcbiAqICAkIGNvbGxhcHNlX2RvdHMoJ2Zvby8uLi9iYXIvLi8uLi8uLi9iYXonKVxuICogICMgJ2JheidcbiAqICAkIGNvbGxhcHNlX2RvdHMoJy4uL2ZvbycpXG4gKiAgIyAnLi4vZm9vJ1xuICogICQgY29sbGFwc2VfZG90cygnLi4vZm9vJykucmVwbGFjZShFWFRSQV9QQVJFTlRfUEFUSFNfUkUsICcnKVxuICogICMgJ2ZvbydcbiAqIH1cbiAqL1xuZnVuY3Rpb24gY29sbGFwc2VfZG90cyhwYXRoKSB7XG4gIGlmIChwYXRoID09PSBudWxsKSB7IHJldHVybiBudWxsOyB9XG4gIHZhciBwID0gbm9ybVBhdGgocGF0aCk7XG4gIC8vIE9ubHkgLy4uLyBsZWZ0IHRvIGZsYXR0ZW5cbiAgdmFyIHIgPSBQQVJFTlRfRElSRUNUT1JZX0hBTkRMRVJfUkU7XG4gIC8vIFdlIHJlcGxhY2Ugd2l0aCAkMSB3aGljaCBtYXRjaGVzIGEgLyBiZWZvcmUgdGhlIC4uIGJlY2F1c2UgdGhpc1xuICAvLyBndWFyYW50ZWVzIHRoYXQ6XG4gIC8vICgxKSB3ZSBoYXZlIGF0IG1vc3QgMSAvIGJldHdlZW4gdGhlIGFkamFjZW50IHBsYWNlLFxuICAvLyAoMikgYWx3YXlzIGhhdmUgYSBzbGFzaCBpZiB0aGVyZSBpcyBhIHByZWNlZGluZyBwYXRoIHNlY3Rpb24sIGFuZFxuICAvLyAoMykgd2UgbmV2ZXIgdHVybiBhIHJlbGF0aXZlIHBhdGggaW50byBhbiBhYnNvbHV0ZSBwYXRoLlxuICBmb3IgKHZhciBxOyAocSA9IHAucmVwbGFjZShyLCAnJDEnKSkgIT0gcDsgcCA9IHEpIHt9O1xuICByZXR1cm4gcDtcbn1cblxuLyoqXG4gKiByZXNvbHZlcyBhIHJlbGF0aXZlIHVybCBzdHJpbmcgdG8gYSBiYXNlIHVyaS5cbiAqIEByZXR1cm4ge1VSSX1cbiAqL1xuZnVuY3Rpb24gcmVzb2x2ZShiYXNlVXJpLCByZWxhdGl2ZVVyaSkge1xuICAvLyB0aGVyZSBhcmUgc2V2ZXJhbCBraW5kcyBvZiByZWxhdGl2ZSB1cmxzOlxuICAvLyAxLiAvL2ZvbyAtIHJlcGxhY2VzIGV2ZXJ5dGhpbmcgZnJvbSB0aGUgZG9tYWluIG9uLiAgZm9vIGlzIGEgZG9tYWluIG5hbWVcbiAgLy8gMi4gZm9vIC0gcmVwbGFjZXMgdGhlIGxhc3QgcGFydCBvZiB0aGUgcGF0aCwgdGhlIHdob2xlIHF1ZXJ5IGFuZCBmcmFnbWVudFxuICAvLyAzLiAvZm9vIC0gcmVwbGFjZXMgdGhlIHRoZSBwYXRoLCB0aGUgcXVlcnkgYW5kIGZyYWdtZW50XG4gIC8vIDQuID9mb28gLSByZXBsYWNlIHRoZSBxdWVyeSBhbmQgZnJhZ21lbnRcbiAgLy8gNS4gI2ZvbyAtIHJlcGxhY2UgdGhlIGZyYWdtZW50IG9ubHlcblxuICB2YXIgYWJzb2x1dGVVcmkgPSBiYXNlVXJpLmNsb25lKCk7XG4gIC8vIHdlIHNhdGlzZnkgdGhlc2UgY29uZGl0aW9ucyBieSBsb29raW5nIGZvciB0aGUgZmlyc3QgcGFydCBvZiByZWxhdGl2ZVVyaVxuICAvLyB0aGF0IGlzIG5vdCBibGFuayBhbmQgYXBwbHlpbmcgZGVmYXVsdHMgdG8gdGhlIHJlc3RcblxuICB2YXIgb3ZlcnJpZGRlbiA9IHJlbGF0aXZlVXJpLmhhc1NjaGVtZSgpO1xuXG4gIGlmIChvdmVycmlkZGVuKSB7XG4gICAgYWJzb2x1dGVVcmkuc2V0UmF3U2NoZW1lKHJlbGF0aXZlVXJpLmdldFJhd1NjaGVtZSgpKTtcbiAgfSBlbHNlIHtcbiAgICBvdmVycmlkZGVuID0gcmVsYXRpdmVVcmkuaGFzQ3JlZGVudGlhbHMoKTtcbiAgfVxuXG4gIGlmIChvdmVycmlkZGVuKSB7XG4gICAgYWJzb2x1dGVVcmkuc2V0UmF3Q3JlZGVudGlhbHMocmVsYXRpdmVVcmkuZ2V0UmF3Q3JlZGVudGlhbHMoKSk7XG4gIH0gZWxzZSB7XG4gICAgb3ZlcnJpZGRlbiA9IHJlbGF0aXZlVXJpLmhhc0RvbWFpbigpO1xuICB9XG5cbiAgaWYgKG92ZXJyaWRkZW4pIHtcbiAgICBhYnNvbHV0ZVVyaS5zZXRSYXdEb21haW4ocmVsYXRpdmVVcmkuZ2V0UmF3RG9tYWluKCkpO1xuICB9IGVsc2Uge1xuICAgIG92ZXJyaWRkZW4gPSByZWxhdGl2ZVVyaS5oYXNQb3J0KCk7XG4gIH1cblxuICB2YXIgcmF3UGF0aCA9IHJlbGF0aXZlVXJpLmdldFJhd1BhdGgoKTtcbiAgdmFyIHNpbXBsaWZpZWRQYXRoID0gY29sbGFwc2VfZG90cyhyYXdQYXRoKTtcbiAgaWYgKG92ZXJyaWRkZW4pIHtcbiAgICBhYnNvbHV0ZVVyaS5zZXRQb3J0KHJlbGF0aXZlVXJpLmdldFBvcnQoKSk7XG4gICAgc2ltcGxpZmllZFBhdGggPSBzaW1wbGlmaWVkUGF0aFxuICAgICAgICAmJiBzaW1wbGlmaWVkUGF0aC5yZXBsYWNlKEVYVFJBX1BBUkVOVF9QQVRIU19SRSwgJycpO1xuICB9IGVsc2Uge1xuICAgIG92ZXJyaWRkZW4gPSAhIXJhd1BhdGg7XG4gICAgaWYgKG92ZXJyaWRkZW4pIHtcbiAgICAgIC8vIHJlc29sdmUgcGF0aCBwcm9wZXJseVxuICAgICAgaWYgKHNpbXBsaWZpZWRQYXRoLmNoYXJDb2RlQXQoMCkgIT09IDB4MmYgLyogLyAqLykgeyAgLy8gcGF0aCBpcyByZWxhdGl2ZVxuICAgICAgICB2YXIgYWJzUmF3UGF0aCA9IGNvbGxhcHNlX2RvdHMoYWJzb2x1dGVVcmkuZ2V0UmF3UGF0aCgpIHx8ICcnKVxuICAgICAgICAgICAgLnJlcGxhY2UoRVhUUkFfUEFSRU5UX1BBVEhTX1JFLCAnJyk7XG4gICAgICAgIHZhciBzbGFzaCA9IGFic1Jhd1BhdGgubGFzdEluZGV4T2YoJy8nKSArIDE7XG4gICAgICAgIHNpbXBsaWZpZWRQYXRoID0gY29sbGFwc2VfZG90cyhcbiAgICAgICAgICAgIChzbGFzaCA/IGFic1Jhd1BhdGguc3Vic3RyaW5nKDAsIHNsYXNoKSA6ICcnKVxuICAgICAgICAgICAgKyBjb2xsYXBzZV9kb3RzKHJhd1BhdGgpKVxuICAgICAgICAgICAgLnJlcGxhY2UoRVhUUkFfUEFSRU5UX1BBVEhTX1JFLCAnJyk7XG4gICAgICB9XG4gICAgfSBlbHNlIHtcbiAgICAgIHNpbXBsaWZpZWRQYXRoID0gc2ltcGxpZmllZFBhdGhcbiAgICAgICAgICAmJiBzaW1wbGlmaWVkUGF0aC5yZXBsYWNlKEVYVFJBX1BBUkVOVF9QQVRIU19SRSwgJycpO1xuICAgICAgaWYgKHNpbXBsaWZpZWRQYXRoICE9PSByYXdQYXRoKSB7XG4gICAgICAgIGFic29sdXRlVXJpLnNldFJhd1BhdGgoc2ltcGxpZmllZFBhdGgpO1xuICAgICAgfVxuICAgIH1cbiAgfVxuXG4gIGlmIChvdmVycmlkZGVuKSB7XG4gICAgYWJzb2x1dGVVcmkuc2V0UmF3UGF0aChzaW1wbGlmaWVkUGF0aCk7XG4gIH0gZWxzZSB7XG4gICAgb3ZlcnJpZGRlbiA9IHJlbGF0aXZlVXJpLmhhc1F1ZXJ5KCk7XG4gIH1cblxuICBpZiAob3ZlcnJpZGRlbikge1xuICAgIGFic29sdXRlVXJpLnNldFJhd1F1ZXJ5KHJlbGF0aXZlVXJpLmdldFJhd1F1ZXJ5KCkpO1xuICB9IGVsc2Uge1xuICAgIG92ZXJyaWRkZW4gPSByZWxhdGl2ZVVyaS5oYXNGcmFnbWVudCgpO1xuICB9XG5cbiAgaWYgKG92ZXJyaWRkZW4pIHtcbiAgICBhYnNvbHV0ZVVyaS5zZXRSYXdGcmFnbWVudChyZWxhdGl2ZVVyaS5nZXRSYXdGcmFnbWVudCgpKTtcbiAgfVxuXG4gIHJldHVybiBhYnNvbHV0ZVVyaTtcbn1cblxuLyoqXG4gKiBhIG11dGFibGUgVVJJLlxuICpcbiAqIFRoaXMgY2xhc3MgY29udGFpbnMgc2V0dGVycyBhbmQgZ2V0dGVycyBmb3IgdGhlIHBhcnRzIG9mIHRoZSBVUkkuXG4gKiBUaGUgPHR0PmdldFhZWjwvdHQ+Lzx0dD5zZXRYWVo8L3R0PiBtZXRob2RzIHJldHVybiB0aGUgZGVjb2RlZCBwYXJ0IC0tIHNvXG4gKiA8Y29kZT51cmkucGFyc2UoJy9mb28lMjBiYXInKS5nZXRQYXRoKCk8L2NvZGU+IHdpbGwgcmV0dXJuIHRoZSBkZWNvZGVkIHBhdGgsXG4gKiA8dHQ+L2ZvbyBiYXI8L3R0Pi5cbiAqXG4gKiA8cD5UaGUgcmF3IHZlcnNpb25zIG9mIGZpZWxkcyBhcmUgYXZhaWxhYmxlIHRvby5cbiAqIDxjb2RlPnVyaS5wYXJzZSgnL2ZvbyUyMGJhcicpLmdldFJhd1BhdGgoKTwvY29kZT4gd2lsbCByZXR1cm4gdGhlIHJhdyBwYXRoLFxuICogPHR0Pi9mb28lMjBiYXI8L3R0Pi4gIFVzZSB0aGUgcmF3IHNldHRlcnMgd2l0aCBjYXJlLCBzaW5jZVxuICogPGNvZGU+VVJJOjp0b1N0cmluZzwvY29kZT4gaXMgbm90IGd1YXJhbnRlZWQgdG8gcmV0dXJuIGEgdmFsaWQgdXJsIGlmIGFcbiAqIHJhdyBzZXR0ZXIgd2FzIHVzZWQuXG4gKlxuICogPHA+QWxsIHNldHRlcnMgcmV0dXJuIDx0dD50aGlzPC90dD4gYW5kIHNvIG1heSBiZSBjaGFpbmVkLCBhIGxhXG4gKiA8Y29kZT51cmkucGFyc2UoJy9mb28nKS5zZXRGcmFnbWVudCgncGFydCcpLnRvU3RyaW5nKCk8L2NvZGU+LlxuICpcbiAqIDxwPllvdSBzaG91bGQgbm90IHVzZSB0aGlzIGNvbnN0cnVjdG9yIGRpcmVjdGx5IC0tIHBsZWFzZSBwcmVmZXIgdGhlIGZhY3RvcnlcbiAqIGZ1bmN0aW9ucyB7QGxpbmsgdXJpLnBhcnNlfSwge0BsaW5rIHVyaS5jcmVhdGV9LCB7QGxpbmsgdXJpLnJlc29sdmV9XG4gKiBpbnN0ZWFkLjwvcD5cbiAqXG4gKiA8cD5UaGUgcGFyYW1ldGVycyBhcmUgYWxsIHJhdyAoYXNzdW1lZCB0byBiZSBwcm9wZXJseSBlc2NhcGVkKSBwYXJ0cywgYW5kXG4gKiBhbnkgKGJ1dCBub3QgYWxsKSBtYXkgYmUgbnVsbC4gIFVuZGVmaW5lZCBpcyBub3QgYWxsb3dlZC48L3A+XG4gKlxuICogQGNvbnN0cnVjdG9yXG4gKi9cbmZ1bmN0aW9uIFVSSShcbiAgICByYXdTY2hlbWUsXG4gICAgcmF3Q3JlZGVudGlhbHMsIHJhd0RvbWFpbiwgcG9ydCxcbiAgICByYXdQYXRoLCByYXdRdWVyeSwgcmF3RnJhZ21lbnQpIHtcbiAgdGhpcy5zY2hlbWVfID0gcmF3U2NoZW1lO1xuICB0aGlzLmNyZWRlbnRpYWxzXyA9IHJhd0NyZWRlbnRpYWxzO1xuICB0aGlzLmRvbWFpbl8gPSByYXdEb21haW47XG4gIHRoaXMucG9ydF8gPSBwb3J0O1xuICB0aGlzLnBhdGhfID0gcmF3UGF0aDtcbiAgdGhpcy5xdWVyeV8gPSByYXdRdWVyeTtcbiAgdGhpcy5mcmFnbWVudF8gPSByYXdGcmFnbWVudDtcbiAgLyoqXG4gICAqIEB0eXBlIHtBcnJheXxudWxsfVxuICAgKi9cbiAgdGhpcy5wYXJhbUNhY2hlXyA9IG51bGw7XG59XG5cbi8qKiByZXR1cm5zIHRoZSBzdHJpbmcgZm9ybSBvZiB0aGUgdXJsLiAqL1xuVVJJLnByb3RvdHlwZS50b1N0cmluZyA9IGZ1bmN0aW9uICgpIHtcbiAgdmFyIG91dCA9IFtdO1xuICBpZiAobnVsbCAhPT0gdGhpcy5zY2hlbWVfKSB7IG91dC5wdXNoKHRoaXMuc2NoZW1lXywgJzonKTsgfVxuICBpZiAobnVsbCAhPT0gdGhpcy5kb21haW5fKSB7XG4gICAgb3V0LnB1c2goJy8vJyk7XG4gICAgaWYgKG51bGwgIT09IHRoaXMuY3JlZGVudGlhbHNfKSB7IG91dC5wdXNoKHRoaXMuY3JlZGVudGlhbHNfLCAnQCcpOyB9XG4gICAgb3V0LnB1c2godGhpcy5kb21haW5fKTtcbiAgICBpZiAobnVsbCAhPT0gdGhpcy5wb3J0XykgeyBvdXQucHVzaCgnOicsIHRoaXMucG9ydF8udG9TdHJpbmcoKSk7IH1cbiAgfVxuICBpZiAobnVsbCAhPT0gdGhpcy5wYXRoXykgeyBvdXQucHVzaCh0aGlzLnBhdGhfKTsgfVxuICBpZiAobnVsbCAhPT0gdGhpcy5xdWVyeV8pIHsgb3V0LnB1c2goJz8nLCB0aGlzLnF1ZXJ5Xyk7IH1cbiAgaWYgKG51bGwgIT09IHRoaXMuZnJhZ21lbnRfKSB7IG91dC5wdXNoKCcjJywgdGhpcy5mcmFnbWVudF8pOyB9XG4gIHJldHVybiBvdXQuam9pbignJyk7XG59O1xuXG5VUkkucHJvdG90eXBlLmNsb25lID0gZnVuY3Rpb24gKCkge1xuICByZXR1cm4gbmV3IFVSSSh0aGlzLnNjaGVtZV8sIHRoaXMuY3JlZGVudGlhbHNfLCB0aGlzLmRvbWFpbl8sIHRoaXMucG9ydF8sXG4gICAgICAgICAgICAgICAgIHRoaXMucGF0aF8sIHRoaXMucXVlcnlfLCB0aGlzLmZyYWdtZW50Xyk7XG59O1xuXG5VUkkucHJvdG90eXBlLmdldFNjaGVtZSA9IGZ1bmN0aW9uICgpIHtcbiAgLy8gSFRNTDUgc3BlYyBkb2VzIG5vdCByZXF1aXJlIHRoZSBzY2hlbWUgdG8gYmUgbG93ZXJjYXNlZCBidXRcbiAgLy8gYWxsIGNvbW1vbiBicm93c2VycyBleGNlcHQgU2FmYXJpIGxvd2VyY2FzZSB0aGUgc2NoZW1lLlxuICByZXR1cm4gdGhpcy5zY2hlbWVfICYmIGRlY29kZVVSSUNvbXBvbmVudCh0aGlzLnNjaGVtZV8pLnRvTG93ZXJDYXNlKCk7XG59O1xuVVJJLnByb3RvdHlwZS5nZXRSYXdTY2hlbWUgPSBmdW5jdGlvbiAoKSB7XG4gIHJldHVybiB0aGlzLnNjaGVtZV87XG59O1xuVVJJLnByb3RvdHlwZS5zZXRTY2hlbWUgPSBmdW5jdGlvbiAobmV3U2NoZW1lKSB7XG4gIHRoaXMuc2NoZW1lXyA9IGVuY29kZUlmRXhpc3RzMihcbiAgICAgIG5ld1NjaGVtZSwgVVJJX0RJU0FMTE9XRURfSU5fU0NIRU1FX09SX0NSRURFTlRJQUxTXyk7XG4gIHJldHVybiB0aGlzO1xufTtcblVSSS5wcm90b3R5cGUuc2V0UmF3U2NoZW1lID0gZnVuY3Rpb24gKG5ld1NjaGVtZSkge1xuICB0aGlzLnNjaGVtZV8gPSBuZXdTY2hlbWUgPyBuZXdTY2hlbWUgOiBudWxsO1xuICByZXR1cm4gdGhpcztcbn07XG5VUkkucHJvdG90eXBlLmhhc1NjaGVtZSA9IGZ1bmN0aW9uICgpIHtcbiAgcmV0dXJuIG51bGwgIT09IHRoaXMuc2NoZW1lXztcbn07XG5cblxuVVJJLnByb3RvdHlwZS5nZXRDcmVkZW50aWFscyA9IGZ1bmN0aW9uICgpIHtcbiAgcmV0dXJuIHRoaXMuY3JlZGVudGlhbHNfICYmIGRlY29kZVVSSUNvbXBvbmVudCh0aGlzLmNyZWRlbnRpYWxzXyk7XG59O1xuVVJJLnByb3RvdHlwZS5nZXRSYXdDcmVkZW50aWFscyA9IGZ1bmN0aW9uICgpIHtcbiAgcmV0dXJuIHRoaXMuY3JlZGVudGlhbHNfO1xufTtcblVSSS5wcm90b3R5cGUuc2V0Q3JlZGVudGlhbHMgPSBmdW5jdGlvbiAobmV3Q3JlZGVudGlhbHMpIHtcbiAgdGhpcy5jcmVkZW50aWFsc18gPSBlbmNvZGVJZkV4aXN0czIoXG4gICAgICBuZXdDcmVkZW50aWFscywgVVJJX0RJU0FMTE9XRURfSU5fU0NIRU1FX09SX0NSRURFTlRJQUxTXyk7XG5cbiAgcmV0dXJuIHRoaXM7XG59O1xuVVJJLnByb3RvdHlwZS5zZXRSYXdDcmVkZW50aWFscyA9IGZ1bmN0aW9uIChuZXdDcmVkZW50aWFscykge1xuICB0aGlzLmNyZWRlbnRpYWxzXyA9IG5ld0NyZWRlbnRpYWxzID8gbmV3Q3JlZGVudGlhbHMgOiBudWxsO1xuICByZXR1cm4gdGhpcztcbn07XG5VUkkucHJvdG90eXBlLmhhc0NyZWRlbnRpYWxzID0gZnVuY3Rpb24gKCkge1xuICByZXR1cm4gbnVsbCAhPT0gdGhpcy5jcmVkZW50aWFsc187XG59O1xuXG5cblVSSS5wcm90b3R5cGUuZ2V0RG9tYWluID0gZnVuY3Rpb24gKCkge1xuICByZXR1cm4gdGhpcy5kb21haW5fICYmIGRlY29kZVVSSUNvbXBvbmVudCh0aGlzLmRvbWFpbl8pO1xufTtcblVSSS5wcm90b3R5cGUuZ2V0UmF3RG9tYWluID0gZnVuY3Rpb24gKCkge1xuICByZXR1cm4gdGhpcy5kb21haW5fO1xufTtcblVSSS5wcm90b3R5cGUuc2V0RG9tYWluID0gZnVuY3Rpb24gKG5ld0RvbWFpbikge1xuICByZXR1cm4gdGhpcy5zZXRSYXdEb21haW4obmV3RG9tYWluICYmIGVuY29kZVVSSUNvbXBvbmVudChuZXdEb21haW4pKTtcbn07XG5VUkkucHJvdG90eXBlLnNldFJhd0RvbWFpbiA9IGZ1bmN0aW9uIChuZXdEb21haW4pIHtcbiAgdGhpcy5kb21haW5fID0gbmV3RG9tYWluID8gbmV3RG9tYWluIDogbnVsbDtcbiAgLy8gTWFpbnRhaW4gdGhlIGludmFyaWFudCB0aGF0IHBhdGhzIG11c3Qgc3RhcnQgd2l0aCBhIHNsYXNoIHdoZW4gdGhlIFVSSVxuICAvLyBpcyBub3QgcGF0aC1yZWxhdGl2ZS5cbiAgcmV0dXJuIHRoaXMuc2V0UmF3UGF0aCh0aGlzLnBhdGhfKTtcbn07XG5VUkkucHJvdG90eXBlLmhhc0RvbWFpbiA9IGZ1bmN0aW9uICgpIHtcbiAgcmV0dXJuIG51bGwgIT09IHRoaXMuZG9tYWluXztcbn07XG5cblxuVVJJLnByb3RvdHlwZS5nZXRQb3J0ID0gZnVuY3Rpb24gKCkge1xuICByZXR1cm4gdGhpcy5wb3J0XyAmJiBkZWNvZGVVUklDb21wb25lbnQodGhpcy5wb3J0Xyk7XG59O1xuVVJJLnByb3RvdHlwZS5zZXRQb3J0ID0gZnVuY3Rpb24gKG5ld1BvcnQpIHtcbiAgaWYgKG5ld1BvcnQpIHtcbiAgICBuZXdQb3J0ID0gTnVtYmVyKG5ld1BvcnQpO1xuICAgIGlmIChuZXdQb3J0ICE9PSAobmV3UG9ydCAmIDB4ZmZmZikpIHtcbiAgICAgIHRocm93IG5ldyBFcnJvcignQmFkIHBvcnQgbnVtYmVyICcgKyBuZXdQb3J0KTtcbiAgICB9XG4gICAgdGhpcy5wb3J0XyA9ICcnICsgbmV3UG9ydDtcbiAgfSBlbHNlIHtcbiAgICB0aGlzLnBvcnRfID0gbnVsbDtcbiAgfVxuICByZXR1cm4gdGhpcztcbn07XG5VUkkucHJvdG90eXBlLmhhc1BvcnQgPSBmdW5jdGlvbiAoKSB7XG4gIHJldHVybiBudWxsICE9PSB0aGlzLnBvcnRfO1xufTtcblxuXG5VUkkucHJvdG90eXBlLmdldFBhdGggPSBmdW5jdGlvbiAoKSB7XG4gIHJldHVybiB0aGlzLnBhdGhfICYmIGRlY29kZVVSSUNvbXBvbmVudCh0aGlzLnBhdGhfKTtcbn07XG5VUkkucHJvdG90eXBlLmdldFJhd1BhdGggPSBmdW5jdGlvbiAoKSB7XG4gIHJldHVybiB0aGlzLnBhdGhfO1xufTtcblVSSS5wcm90b3R5cGUuc2V0UGF0aCA9IGZ1bmN0aW9uIChuZXdQYXRoKSB7XG4gIHJldHVybiB0aGlzLnNldFJhd1BhdGgoZW5jb2RlSWZFeGlzdHMyKG5ld1BhdGgsIFVSSV9ESVNBTExPV0VEX0lOX1BBVEhfKSk7XG59O1xuVVJJLnByb3RvdHlwZS5zZXRSYXdQYXRoID0gZnVuY3Rpb24gKG5ld1BhdGgpIHtcbiAgaWYgKG5ld1BhdGgpIHtcbiAgICBuZXdQYXRoID0gU3RyaW5nKG5ld1BhdGgpO1xuICAgIHRoaXMucGF0aF8gPSBcbiAgICAgIC8vIFBhdGhzIG11c3Qgc3RhcnQgd2l0aCAnLycgdW5sZXNzIHRoaXMgaXMgYSBwYXRoLXJlbGF0aXZlIFVSTC5cbiAgICAgICghdGhpcy5kb21haW5fIHx8IC9eXFwvLy50ZXN0KG5ld1BhdGgpKSA/IG5ld1BhdGggOiAnLycgKyBuZXdQYXRoO1xuICB9IGVsc2Uge1xuICAgIHRoaXMucGF0aF8gPSBudWxsO1xuICB9XG4gIHJldHVybiB0aGlzO1xufTtcblVSSS5wcm90b3R5cGUuaGFzUGF0aCA9IGZ1bmN0aW9uICgpIHtcbiAgcmV0dXJuIG51bGwgIT09IHRoaXMucGF0aF87XG59O1xuXG5cblVSSS5wcm90b3R5cGUuZ2V0UXVlcnkgPSBmdW5jdGlvbiAoKSB7XG4gIC8vIEZyb20gaHR0cDovL3d3dy53My5vcmcvQWRkcmVzc2luZy9VUkwvNF9VUklfUmVjb21tZW50YXRpb25zLmh0bWxcbiAgLy8gV2l0aGluIHRoZSBxdWVyeSBzdHJpbmcsIHRoZSBwbHVzIHNpZ24gaXMgcmVzZXJ2ZWQgYXMgc2hvcnRoYW5kIG5vdGF0aW9uXG4gIC8vIGZvciBhIHNwYWNlLlxuICByZXR1cm4gdGhpcy5xdWVyeV8gJiYgZGVjb2RlVVJJQ29tcG9uZW50KHRoaXMucXVlcnlfKS5yZXBsYWNlKC9cXCsvZywgJyAnKTtcbn07XG5VUkkucHJvdG90eXBlLmdldFJhd1F1ZXJ5ID0gZnVuY3Rpb24gKCkge1xuICByZXR1cm4gdGhpcy5xdWVyeV87XG59O1xuVVJJLnByb3RvdHlwZS5zZXRRdWVyeSA9IGZ1bmN0aW9uIChuZXdRdWVyeSkge1xuICB0aGlzLnBhcmFtQ2FjaGVfID0gbnVsbDtcbiAgdGhpcy5xdWVyeV8gPSBlbmNvZGVJZkV4aXN0cyhuZXdRdWVyeSk7XG4gIHJldHVybiB0aGlzO1xufTtcblVSSS5wcm90b3R5cGUuc2V0UmF3UXVlcnkgPSBmdW5jdGlvbiAobmV3UXVlcnkpIHtcbiAgdGhpcy5wYXJhbUNhY2hlXyA9IG51bGw7XG4gIHRoaXMucXVlcnlfID0gbmV3UXVlcnkgPyBuZXdRdWVyeSA6IG51bGw7XG4gIHJldHVybiB0aGlzO1xufTtcblVSSS5wcm90b3R5cGUuaGFzUXVlcnkgPSBmdW5jdGlvbiAoKSB7XG4gIHJldHVybiBudWxsICE9PSB0aGlzLnF1ZXJ5Xztcbn07XG5cbi8qKlxuICogc2V0cyB0aGUgcXVlcnkgZ2l2ZW4gYSBsaXN0IG9mIHN0cmluZ3Mgb2YgdGhlIGZvcm1cbiAqIFsga2V5MCwgdmFsdWUwLCBrZXkxLCB2YWx1ZTEsIC4uLiBdLlxuICpcbiAqIDxwPjxjb2RlPnVyaS5zZXRBbGxQYXJhbWV0ZXJzKFsnYScsICdiJywgJ2MnLCAnZCddKS5nZXRRdWVyeSgpPC9jb2RlPlxuICogd2lsbCB5aWVsZCA8Y29kZT4nYT1iJmM9ZCc8L2NvZGU+LlxuICovXG5VUkkucHJvdG90eXBlLnNldEFsbFBhcmFtZXRlcnMgPSBmdW5jdGlvbiAocGFyYW1zKSB7XG4gIGlmICh0eXBlb2YgcGFyYW1zID09PSAnb2JqZWN0Jykge1xuICAgIGlmICghKHBhcmFtcyBpbnN0YW5jZW9mIEFycmF5KVxuICAgICAgICAmJiAocGFyYW1zIGluc3RhbmNlb2YgT2JqZWN0XG4gICAgICAgICAgICB8fCBPYmplY3QucHJvdG90eXBlLnRvU3RyaW5nLmNhbGwocGFyYW1zKSAhPT0gJ1tvYmplY3QgQXJyYXldJykpIHtcbiAgICAgIHZhciBuZXdQYXJhbXMgPSBbXTtcbiAgICAgIHZhciBpID0gLTE7XG4gICAgICBmb3IgKHZhciBrIGluIHBhcmFtcykge1xuICAgICAgICB2YXIgdiA9IHBhcmFtc1trXTtcbiAgICAgICAgaWYgKCdzdHJpbmcnID09PSB0eXBlb2Ygdikge1xuICAgICAgICAgIG5ld1BhcmFtc1srK2ldID0gaztcbiAgICAgICAgICBuZXdQYXJhbXNbKytpXSA9IHY7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICAgIHBhcmFtcyA9IG5ld1BhcmFtcztcbiAgICB9XG4gIH1cbiAgdGhpcy5wYXJhbUNhY2hlXyA9IG51bGw7XG4gIHZhciBxdWVyeUJ1ZiA9IFtdO1xuICB2YXIgc2VwYXJhdG9yID0gJyc7XG4gIGZvciAodmFyIGogPSAwOyBqIDwgcGFyYW1zLmxlbmd0aDspIHtcbiAgICB2YXIgayA9IHBhcmFtc1tqKytdO1xuICAgIHZhciB2ID0gcGFyYW1zW2orK107XG4gICAgcXVlcnlCdWYucHVzaChzZXBhcmF0b3IsIGVuY29kZVVSSUNvbXBvbmVudChrLnRvU3RyaW5nKCkpKTtcbiAgICBzZXBhcmF0b3IgPSAnJic7XG4gICAgaWYgKHYpIHtcbiAgICAgIHF1ZXJ5QnVmLnB1c2goJz0nLCBlbmNvZGVVUklDb21wb25lbnQodi50b1N0cmluZygpKSk7XG4gICAgfVxuICB9XG4gIHRoaXMucXVlcnlfID0gcXVlcnlCdWYuam9pbignJyk7XG4gIHJldHVybiB0aGlzO1xufTtcblVSSS5wcm90b3R5cGUuY2hlY2tQYXJhbWV0ZXJDYWNoZV8gPSBmdW5jdGlvbiAoKSB7XG4gIGlmICghdGhpcy5wYXJhbUNhY2hlXykge1xuICAgIHZhciBxID0gdGhpcy5xdWVyeV87XG4gICAgaWYgKCFxKSB7XG4gICAgICB0aGlzLnBhcmFtQ2FjaGVfID0gW107XG4gICAgfSBlbHNlIHtcbiAgICAgIHZhciBjZ2lQYXJhbXMgPSBxLnNwbGl0KC9bJlxcP10vKTtcbiAgICAgIHZhciBvdXQgPSBbXTtcbiAgICAgIHZhciBrID0gLTE7XG4gICAgICBmb3IgKHZhciBpID0gMDsgaSA8IGNnaVBhcmFtcy5sZW5ndGg7ICsraSkge1xuICAgICAgICB2YXIgbSA9IGNnaVBhcmFtc1tpXS5tYXRjaCgvXihbXj1dKikoPzo9KC4qKSk/JC8pO1xuICAgICAgICAvLyBGcm9tIGh0dHA6Ly93d3cudzMub3JnL0FkZHJlc3NpbmcvVVJMLzRfVVJJX1JlY29tbWVudGF0aW9ucy5odG1sXG4gICAgICAgIC8vIFdpdGhpbiB0aGUgcXVlcnkgc3RyaW5nLCB0aGUgcGx1cyBzaWduIGlzIHJlc2VydmVkIGFzIHNob3J0aGFuZFxuICAgICAgICAvLyBub3RhdGlvbiBmb3IgYSBzcGFjZS5cbiAgICAgICAgb3V0Wysra10gPSBkZWNvZGVVUklDb21wb25lbnQobVsxXSkucmVwbGFjZSgvXFwrL2csICcgJyk7XG4gICAgICAgIG91dFsrK2tdID0gZGVjb2RlVVJJQ29tcG9uZW50KG1bMl0gfHwgJycpLnJlcGxhY2UoL1xcKy9nLCAnICcpO1xuICAgICAgfVxuICAgICAgdGhpcy5wYXJhbUNhY2hlXyA9IG91dDtcbiAgICB9XG4gIH1cbn07XG4vKipcbiAqIHNldHMgdGhlIHZhbHVlcyBvZiB0aGUgbmFtZWQgY2dpIHBhcmFtZXRlcnMuXG4gKlxuICogPHA+U28sIDxjb2RlPnVyaS5wYXJzZSgnZm9vP2E9YiZjPWQmZT1mJykuc2V0UGFyYW1ldGVyVmFsdWVzKCdjJywgWyduZXcnXSlcbiAqIDwvY29kZT4geWllbGRzIDx0dD5mb28/YT1iJmM9bmV3JmU9ZjwvdHQ+LjwvcD5cbiAqXG4gKiBAcGFyYW0ga2V5IHtzdHJpbmd9XG4gKiBAcGFyYW0gdmFsdWVzIHtBcnJheS48c3RyaW5nPn0gdGhlIG5ldyB2YWx1ZXMuICBJZiB2YWx1ZXMgaXMgYSBzaW5nbGUgc3RyaW5nXG4gKiAgIHRoZW4gaXQgd2lsbCBiZSB0cmVhdGVkIGFzIHRoZSBzb2xlIHZhbHVlLlxuICovXG5VUkkucHJvdG90eXBlLnNldFBhcmFtZXRlclZhbHVlcyA9IGZ1bmN0aW9uIChrZXksIHZhbHVlcykge1xuICAvLyBiZSBuaWNlIGFuZCBhdm9pZCBzdWJ0bGUgYnVncyB3aGVyZSBbXSBvcGVyYXRvciBvbiBzdHJpbmcgcGVyZm9ybXMgY2hhckF0XG4gIC8vIG9uIHNvbWUgYnJvd3NlcnMgYW5kIGNyYXNoZXMgb24gSUVcbiAgaWYgKHR5cGVvZiB2YWx1ZXMgPT09ICdzdHJpbmcnKSB7XG4gICAgdmFsdWVzID0gWyB2YWx1ZXMgXTtcbiAgfVxuXG4gIHRoaXMuY2hlY2tQYXJhbWV0ZXJDYWNoZV8oKTtcbiAgdmFyIG5ld1ZhbHVlSW5kZXggPSAwO1xuICB2YXIgcGMgPSB0aGlzLnBhcmFtQ2FjaGVfO1xuICB2YXIgcGFyYW1zID0gW107XG4gIGZvciAodmFyIGkgPSAwLCBrID0gMDsgaSA8IHBjLmxlbmd0aDsgaSArPSAyKSB7XG4gICAgaWYgKGtleSA9PT0gcGNbaV0pIHtcbiAgICAgIGlmIChuZXdWYWx1ZUluZGV4IDwgdmFsdWVzLmxlbmd0aCkge1xuICAgICAgICBwYXJhbXMucHVzaChrZXksIHZhbHVlc1tuZXdWYWx1ZUluZGV4KytdKTtcbiAgICAgIH1cbiAgICB9IGVsc2Uge1xuICAgICAgcGFyYW1zLnB1c2gocGNbaV0sIHBjW2kgKyAxXSk7XG4gICAgfVxuICB9XG4gIHdoaWxlIChuZXdWYWx1ZUluZGV4IDwgdmFsdWVzLmxlbmd0aCkge1xuICAgIHBhcmFtcy5wdXNoKGtleSwgdmFsdWVzW25ld1ZhbHVlSW5kZXgrK10pO1xuICB9XG4gIHRoaXMuc2V0QWxsUGFyYW1ldGVycyhwYXJhbXMpO1xuICByZXR1cm4gdGhpcztcbn07XG5VUkkucHJvdG90eXBlLnJlbW92ZVBhcmFtZXRlciA9IGZ1bmN0aW9uIChrZXkpIHtcbiAgcmV0dXJuIHRoaXMuc2V0UGFyYW1ldGVyVmFsdWVzKGtleSwgW10pO1xufTtcbi8qKlxuICogcmV0dXJucyB0aGUgcGFyYW1ldGVycyBzcGVjaWZpZWQgaW4gdGhlIHF1ZXJ5IHBhcnQgb2YgdGhlIHVyaSBhcyBhIGxpc3Qgb2ZcbiAqIGtleXMgYW5kIHZhbHVlcyBsaWtlIFsga2V5MCwgdmFsdWUwLCBrZXkxLCB2YWx1ZTEsIC4uLiBdLlxuICpcbiAqIEByZXR1cm4ge0FycmF5LjxzdHJpbmc+fVxuICovXG5VUkkucHJvdG90eXBlLmdldEFsbFBhcmFtZXRlcnMgPSBmdW5jdGlvbiAoKSB7XG4gIHRoaXMuY2hlY2tQYXJhbWV0ZXJDYWNoZV8oKTtcbiAgcmV0dXJuIHRoaXMucGFyYW1DYWNoZV8uc2xpY2UoMCwgdGhpcy5wYXJhbUNhY2hlXy5sZW5ndGgpO1xufTtcbi8qKlxuICogcmV0dXJucyB0aGUgdmFsdWU8Yj5zPC9iPiBmb3IgYSBnaXZlbiBjZ2kgcGFyYW1ldGVyIGFzIGEgbGlzdCBvZiBkZWNvZGVkXG4gKiBxdWVyeSBwYXJhbWV0ZXIgdmFsdWVzLlxuICogQHJldHVybiB7QXJyYXkuPHN0cmluZz59XG4gKi9cblVSSS5wcm90b3R5cGUuZ2V0UGFyYW1ldGVyVmFsdWVzID0gZnVuY3Rpb24gKHBhcmFtTmFtZVVuZXNjYXBlZCkge1xuICB0aGlzLmNoZWNrUGFyYW1ldGVyQ2FjaGVfKCk7XG4gIHZhciB2YWx1ZXMgPSBbXTtcbiAgZm9yICh2YXIgaSA9IDA7IGkgPCB0aGlzLnBhcmFtQ2FjaGVfLmxlbmd0aDsgaSArPSAyKSB7XG4gICAgaWYgKHBhcmFtTmFtZVVuZXNjYXBlZCA9PT0gdGhpcy5wYXJhbUNhY2hlX1tpXSkge1xuICAgICAgdmFsdWVzLnB1c2godGhpcy5wYXJhbUNhY2hlX1tpICsgMV0pO1xuICAgIH1cbiAgfVxuICByZXR1cm4gdmFsdWVzO1xufTtcbi8qKlxuICogcmV0dXJucyBhIG1hcCBvZiBjZ2kgcGFyYW1ldGVyIG5hbWVzIHRvIChub24tZW1wdHkpIGxpc3RzIG9mIHZhbHVlcy5cbiAqIEByZXR1cm4ge09iamVjdC48c3RyaW5nLEFycmF5LjxzdHJpbmc+Pn1cbiAqL1xuVVJJLnByb3RvdHlwZS5nZXRQYXJhbWV0ZXJNYXAgPSBmdW5jdGlvbiAocGFyYW1OYW1lVW5lc2NhcGVkKSB7XG4gIHRoaXMuY2hlY2tQYXJhbWV0ZXJDYWNoZV8oKTtcbiAgdmFyIHBhcmFtTWFwID0ge307XG4gIGZvciAodmFyIGkgPSAwOyBpIDwgdGhpcy5wYXJhbUNhY2hlXy5sZW5ndGg7IGkgKz0gMikge1xuICAgIHZhciBrZXkgPSB0aGlzLnBhcmFtQ2FjaGVfW2krK10sXG4gICAgICB2YWx1ZSA9IHRoaXMucGFyYW1DYWNoZV9baSsrXTtcbiAgICBpZiAoIShrZXkgaW4gcGFyYW1NYXApKSB7XG4gICAgICBwYXJhbU1hcFtrZXldID0gW3ZhbHVlXTtcbiAgICB9IGVsc2Uge1xuICAgICAgcGFyYW1NYXBba2V5XS5wdXNoKHZhbHVlKTtcbiAgICB9XG4gIH1cbiAgcmV0dXJuIHBhcmFtTWFwO1xufTtcbi8qKlxuICogcmV0dXJucyB0aGUgZmlyc3QgdmFsdWUgZm9yIGEgZ2l2ZW4gY2dpIHBhcmFtZXRlciBvciBudWxsIGlmIHRoZSBnaXZlblxuICogcGFyYW1ldGVyIG5hbWUgZG9lcyBub3QgYXBwZWFyIGluIHRoZSBxdWVyeSBzdHJpbmcuXG4gKiBJZiB0aGUgZ2l2ZW4gcGFyYW1ldGVyIG5hbWUgZG9lcyBhcHBlYXIsIGJ1dCBoYXMgbm8gJzx0dD49PC90dD4nIGZvbGxvd2luZ1xuICogaXQsIHRoZW4gdGhlIGVtcHR5IHN0cmluZyB3aWxsIGJlIHJldHVybmVkLlxuICogQHJldHVybiB7c3RyaW5nfG51bGx9XG4gKi9cblVSSS5wcm90b3R5cGUuZ2V0UGFyYW1ldGVyVmFsdWUgPSBmdW5jdGlvbiAocGFyYW1OYW1lVW5lc2NhcGVkKSB7XG4gIHRoaXMuY2hlY2tQYXJhbWV0ZXJDYWNoZV8oKTtcbiAgZm9yICh2YXIgaSA9IDA7IGkgPCB0aGlzLnBhcmFtQ2FjaGVfLmxlbmd0aDsgaSArPSAyKSB7XG4gICAgaWYgKHBhcmFtTmFtZVVuZXNjYXBlZCA9PT0gdGhpcy5wYXJhbUNhY2hlX1tpXSkge1xuICAgICAgcmV0dXJuIHRoaXMucGFyYW1DYWNoZV9baSArIDFdO1xuICAgIH1cbiAgfVxuICByZXR1cm4gbnVsbDtcbn07XG5cblVSSS5wcm90b3R5cGUuZ2V0RnJhZ21lbnQgPSBmdW5jdGlvbiAoKSB7XG4gIHJldHVybiB0aGlzLmZyYWdtZW50XyAmJiBkZWNvZGVVUklDb21wb25lbnQodGhpcy5mcmFnbWVudF8pO1xufTtcblVSSS5wcm90b3R5cGUuZ2V0UmF3RnJhZ21lbnQgPSBmdW5jdGlvbiAoKSB7XG4gIHJldHVybiB0aGlzLmZyYWdtZW50Xztcbn07XG5VUkkucHJvdG90eXBlLnNldEZyYWdtZW50ID0gZnVuY3Rpb24gKG5ld0ZyYWdtZW50KSB7XG4gIHRoaXMuZnJhZ21lbnRfID0gbmV3RnJhZ21lbnQgPyBlbmNvZGVVUklDb21wb25lbnQobmV3RnJhZ21lbnQpIDogbnVsbDtcbiAgcmV0dXJuIHRoaXM7XG59O1xuVVJJLnByb3RvdHlwZS5zZXRSYXdGcmFnbWVudCA9IGZ1bmN0aW9uIChuZXdGcmFnbWVudCkge1xuICB0aGlzLmZyYWdtZW50XyA9IG5ld0ZyYWdtZW50ID8gbmV3RnJhZ21lbnQgOiBudWxsO1xuICByZXR1cm4gdGhpcztcbn07XG5VUkkucHJvdG90eXBlLmhhc0ZyYWdtZW50ID0gZnVuY3Rpb24gKCkge1xuICByZXR1cm4gbnVsbCAhPT0gdGhpcy5mcmFnbWVudF87XG59O1xuXG5mdW5jdGlvbiBudWxsSWZBYnNlbnQobWF0Y2hQYXJ0KSB7XG4gIHJldHVybiAoJ3N0cmluZycgPT0gdHlwZW9mIG1hdGNoUGFydCkgJiYgKG1hdGNoUGFydC5sZW5ndGggPiAwKVxuICAgICAgICAgPyBtYXRjaFBhcnRcbiAgICAgICAgIDogbnVsbDtcbn1cblxuXG5cblxuLyoqXG4gKiBhIHJlZ3VsYXIgZXhwcmVzc2lvbiBmb3IgYnJlYWtpbmcgYSBVUkkgaW50byBpdHMgY29tcG9uZW50IHBhcnRzLlxuICpcbiAqIDxwPmh0dHA6Ly93d3cuZ2Jpdi5jb20vcHJvdG9jb2xzL3VyaS9yZmMvcmZjMzk4Ni5odG1sI1JGQzIyMzQgc2F5c1xuICogQXMgdGhlIFwiZmlyc3QtbWF0Y2gtd2luc1wiIGFsZ29yaXRobSBpcyBpZGVudGljYWwgdG8gdGhlIFwiZ3JlZWR5XCJcbiAqIGRpc2FtYmlndWF0aW9uIG1ldGhvZCB1c2VkIGJ5IFBPU0lYIHJlZ3VsYXIgZXhwcmVzc2lvbnMsIGl0IGlzIG5hdHVyYWwgYW5kXG4gKiBjb21tb25wbGFjZSB0byB1c2UgYSByZWd1bGFyIGV4cHJlc3Npb24gZm9yIHBhcnNpbmcgdGhlIHBvdGVudGlhbCBmaXZlXG4gKiBjb21wb25lbnRzIG9mIGEgVVJJIHJlZmVyZW5jZS5cbiAqXG4gKiA8cD5UaGUgZm9sbG93aW5nIGxpbmUgaXMgdGhlIHJlZ3VsYXIgZXhwcmVzc2lvbiBmb3IgYnJlYWtpbmctZG93biBhXG4gKiB3ZWxsLWZvcm1lZCBVUkkgcmVmZXJlbmNlIGludG8gaXRzIGNvbXBvbmVudHMuXG4gKlxuICogPHByZT5cbiAqIF4oKFteOi8/I10rKTopPygvLyhbXi8/I10qKSk/KFtePyNdKikoXFw/KFteI10qKSk/KCMoLiopKT9cbiAqICAxMiAgICAgICAgICAgIDMgIDQgICAgICAgICAgNSAgICAgICA2ICA3ICAgICAgICA4IDlcbiAqIDwvcHJlPlxuICpcbiAqIDxwPlRoZSBudW1iZXJzIGluIHRoZSBzZWNvbmQgbGluZSBhYm92ZSBhcmUgb25seSB0byBhc3Npc3QgcmVhZGFiaWxpdHk7IHRoZXlcbiAqIGluZGljYXRlIHRoZSByZWZlcmVuY2UgcG9pbnRzIGZvciBlYWNoIHN1YmV4cHJlc3Npb24gKGkuZS4sIGVhY2ggcGFpcmVkXG4gKiBwYXJlbnRoZXNpcykuIFdlIHJlZmVyIHRvIHRoZSB2YWx1ZSBtYXRjaGVkIGZvciBzdWJleHByZXNzaW9uIDxuPiBhcyAkPG4+LlxuICogRm9yIGV4YW1wbGUsIG1hdGNoaW5nIHRoZSBhYm92ZSBleHByZXNzaW9uIHRvXG4gKiA8cHJlPlxuICogICAgIGh0dHA6Ly93d3cuaWNzLnVjaS5lZHUvcHViL2lldGYvdXJpLyNSZWxhdGVkXG4gKiA8L3ByZT5cbiAqIHJlc3VsdHMgaW4gdGhlIGZvbGxvd2luZyBzdWJleHByZXNzaW9uIG1hdGNoZXM6XG4gKiA8cHJlPlxuICogICAgJDEgPSBodHRwOlxuICogICAgJDIgPSBodHRwXG4gKiAgICAkMyA9IC8vd3d3Lmljcy51Y2kuZWR1XG4gKiAgICAkNCA9IHd3dy5pY3MudWNpLmVkdVxuICogICAgJDUgPSAvcHViL2lldGYvdXJpL1xuICogICAgJDYgPSA8dW5kZWZpbmVkPlxuICogICAgJDcgPSA8dW5kZWZpbmVkPlxuICogICAgJDggPSAjUmVsYXRlZFxuICogICAgJDkgPSBSZWxhdGVkXG4gKiA8L3ByZT5cbiAqIHdoZXJlIDx1bmRlZmluZWQ+IGluZGljYXRlcyB0aGF0IHRoZSBjb21wb25lbnQgaXMgbm90IHByZXNlbnQsIGFzIGlzIHRoZVxuICogY2FzZSBmb3IgdGhlIHF1ZXJ5IGNvbXBvbmVudCBpbiB0aGUgYWJvdmUgZXhhbXBsZS4gVGhlcmVmb3JlLCB3ZSBjYW5cbiAqIGRldGVybWluZSB0aGUgdmFsdWUgb2YgdGhlIGZpdmUgY29tcG9uZW50cyBhc1xuICogPHByZT5cbiAqICAgIHNjaGVtZSAgICA9ICQyXG4gKiAgICBhdXRob3JpdHkgPSAkNFxuICogICAgcGF0aCAgICAgID0gJDVcbiAqICAgIHF1ZXJ5ICAgICA9ICQ3XG4gKiAgICBmcmFnbWVudCAgPSAkOVxuICogPC9wcmU+XG4gKlxuICogPHA+bXNhbXVlbDogSSBoYXZlIG1vZGlmaWVkIHRoZSByZWd1bGFyIGV4cHJlc3Npb24gc2xpZ2h0bHkgdG8gZXhwb3NlIHRoZVxuICogY3JlZGVudGlhbHMsIGRvbWFpbiwgYW5kIHBvcnQgc2VwYXJhdGVseSBmcm9tIHRoZSBhdXRob3JpdHkuXG4gKiBUaGUgbW9kaWZpZWQgdmVyc2lvbiB5aWVsZHNcbiAqIDxwcmU+XG4gKiAgICAkMSA9IGh0dHAgICAgICAgICAgICAgIHNjaGVtZVxuICogICAgJDIgPSA8dW5kZWZpbmVkPiAgICAgICBjcmVkZW50aWFscyAtXFxcbiAqICAgICQzID0gd3d3Lmljcy51Y2kuZWR1ICAgZG9tYWluICAgICAgIHwgYXV0aG9yaXR5XG4gKiAgICAkNCA9IDx1bmRlZmluZWQ+ICAgICAgIHBvcnQgICAgICAgIC0vXG4gKiAgICAkNSA9IC9wdWIvaWV0Zi91cmkvICAgIHBhdGhcbiAqICAgICQ2ID0gPHVuZGVmaW5lZD4gICAgICAgcXVlcnkgd2l0aG91dCA/XG4gKiAgICAkNyA9IFJlbGF0ZWQgICAgICAgICAgIGZyYWdtZW50IHdpdGhvdXQgI1xuICogPC9wcmU+XG4gKi9cbnZhciBVUklfUkVfID0gbmV3IFJlZ0V4cChcbiAgICAgIFwiXlwiICtcbiAgICAgIFwiKD86XCIgK1xuICAgICAgICBcIihbXjovPyNdKylcIiArICAgICAgICAgLy8gc2NoZW1lXG4gICAgICBcIjopP1wiICtcbiAgICAgIFwiKD86Ly9cIiArXG4gICAgICAgIFwiKD86KFteLz8jXSopQCk/XCIgKyAgICAvLyBjcmVkZW50aWFsc1xuICAgICAgICBcIihbXi8/IzpAXSopXCIgKyAgICAgICAgLy8gZG9tYWluXG4gICAgICAgIFwiKD86OihbMC05XSspKT9cIiArICAgICAvLyBwb3J0XG4gICAgICBcIik/XCIgK1xuICAgICAgXCIoW14/I10rKT9cIiArICAgICAgICAgICAgLy8gcGF0aFxuICAgICAgXCIoPzpcXFxcPyhbXiNdKikpP1wiICsgICAgICAvLyBxdWVyeVxuICAgICAgXCIoPzojKC4qKSk/XCIgKyAgICAgICAgICAgLy8gZnJhZ21lbnRcbiAgICAgIFwiJFwiXG4gICAgICApO1xuXG52YXIgVVJJX0RJU0FMTE9XRURfSU5fU0NIRU1FX09SX0NSRURFTlRJQUxTXyA9IC9bI1xcL1xcP0BdL2c7XG52YXIgVVJJX0RJU0FMTE9XRURfSU5fUEFUSF8gPSAvW1xcI1xcP10vZztcblxuVVJJLnBhcnNlID0gcGFyc2U7XG5VUkkuY3JlYXRlID0gY3JlYXRlO1xuVVJJLnJlc29sdmUgPSByZXNvbHZlO1xuVVJJLmNvbGxhcHNlX2RvdHMgPSBjb2xsYXBzZV9kb3RzOyAgLy8gVmlzaWJsZSBmb3IgdGVzdGluZy5cblxuLy8gbGlnaHR3ZWlnaHQgc3RyaW5nLWJhc2VkIGFwaSBmb3IgbG9hZE1vZHVsZU1ha2VyXG5VUkkudXRpbHMgPSB7XG4gIG1pbWVUeXBlT2Y6IGZ1bmN0aW9uICh1cmkpIHtcbiAgICB2YXIgdXJpT2JqID0gcGFyc2UodXJpKTtcbiAgICBpZiAoL1xcLmh0bWwkLy50ZXN0KHVyaU9iai5nZXRQYXRoKCkpKSB7XG4gICAgICByZXR1cm4gJ3RleHQvaHRtbCc7XG4gICAgfSBlbHNlIHtcbiAgICAgIHJldHVybiAnYXBwbGljYXRpb24vamF2YXNjcmlwdCc7XG4gICAgfVxuICB9LFxuICByZXNvbHZlOiBmdW5jdGlvbiAoYmFzZSwgdXJpKSB7XG4gICAgaWYgKGJhc2UpIHtcbiAgICAgIHJldHVybiByZXNvbHZlKHBhcnNlKGJhc2UpLCBwYXJzZSh1cmkpKS50b1N0cmluZygpO1xuICAgIH0gZWxzZSB7XG4gICAgICByZXR1cm4gJycgKyB1cmk7XG4gICAgfVxuICB9XG59O1xuXG5cbnJldHVybiBVUkk7XG59KSgpO1xuXG4vLyBDb3B5cmlnaHQgR29vZ2xlIEluYy5cbi8vIExpY2Vuc2VkIHVuZGVyIHRoZSBBcGFjaGUgTGljZW5jZSBWZXJzaW9uIDIuMFxuLy8gQXV0b2dlbmVyYXRlZCBhdCBNb24gRmViIDI1IDEzOjA1OjQyIEVTVCAyMDEzXG4vLyBAb3ZlcnJpZGVzIHdpbmRvd1xuLy8gQHByb3ZpZGVzIGh0bWw0XG52YXIgaHRtbDQgPSB7fTtcbmh0bWw0LmF0eXBlID0ge1xuICAnTk9ORSc6IDAsXG4gICdVUkknOiAxLFxuICAnVVJJX0ZSQUdNRU5UJzogMTEsXG4gICdTQ1JJUFQnOiAyLFxuICAnU1RZTEUnOiAzLFxuICAnSFRNTCc6IDEyLFxuICAnSUQnOiA0LFxuICAnSURSRUYnOiA1LFxuICAnSURSRUZTJzogNixcbiAgJ0dMT0JBTF9OQU1FJzogNyxcbiAgJ0xPQ0FMX05BTUUnOiA4LFxuICAnQ0xBU1NFUyc6IDksXG4gICdGUkFNRV9UQVJHRVQnOiAxMCxcbiAgJ01FRElBX1FVRVJZJzogMTNcbn07XG5odG1sNFsgJ2F0eXBlJyBdID0gaHRtbDQuYXR5cGU7XG5odG1sNC5BVFRSSUJTID0ge1xuICAnKjo6Y2xhc3MnOiA5LFxuICAnKjo6ZGlyJzogMCxcbiAgJyo6OmRyYWdnYWJsZSc6IDAsXG4gICcqOjpoaWRkZW4nOiAwLFxuICAnKjo6aWQnOiA0LFxuICAnKjo6aW5lcnQnOiAwLFxuICAnKjo6aXRlbXByb3AnOiAwLFxuICAnKjo6aXRlbXJlZic6IDYsXG4gICcqOjppdGVtc2NvcGUnOiAwLFxuICAnKjo6bGFuZyc6IDAsXG4gICcqOjpvbmJsdXInOiAyLFxuICAnKjo6b25jaGFuZ2UnOiAyLFxuICAnKjo6b25jbGljayc6IDIsXG4gICcqOjpvbmRibGNsaWNrJzogMixcbiAgJyo6Om9uZm9jdXMnOiAyLFxuICAnKjo6b25rZXlkb3duJzogMixcbiAgJyo6Om9ua2V5cHJlc3MnOiAyLFxuICAnKjo6b25rZXl1cCc6IDIsXG4gICcqOjpvbmxvYWQnOiAyLFxuICAnKjo6b25tb3VzZWRvd24nOiAyLFxuICAnKjo6b25tb3VzZW1vdmUnOiAyLFxuICAnKjo6b25tb3VzZW91dCc6IDIsXG4gICcqOjpvbm1vdXNlb3Zlcic6IDIsXG4gICcqOjpvbm1vdXNldXAnOiAyLFxuICAnKjo6b25yZXNldCc6IDIsXG4gICcqOjpvbnNjcm9sbCc6IDIsXG4gICcqOjpvbnNlbGVjdCc6IDIsXG4gICcqOjpvbnN1Ym1pdCc6IDIsXG4gICcqOjpvbnVubG9hZCc6IDIsXG4gICcqOjpzcGVsbGNoZWNrJzogMCxcbiAgJyo6OnN0eWxlJzogMyxcbiAgJyo6OnRpdGxlJzogMCxcbiAgJyo6OnRyYW5zbGF0ZSc6IDAsXG4gICdhOjphY2Nlc3NrZXknOiAwLFxuICAnYTo6Y29vcmRzJzogMCxcbiAgJ2E6OmhyZWYnOiAxLFxuICAnYTo6aHJlZmxhbmcnOiAwLFxuICAnYTo6bmFtZSc6IDcsXG4gICdhOjpvbmJsdXInOiAyLFxuICAnYTo6b25mb2N1cyc6IDIsXG4gICdhOjpzaGFwZSc6IDAsXG4gICdhOjp0YWJpbmRleCc6IDAsXG4gICdhOjp0YXJnZXQnOiAxMCxcbiAgJ2E6OnR5cGUnOiAwLFxuICAnYXJlYTo6YWNjZXNza2V5JzogMCxcbiAgJ2FyZWE6OmFsdCc6IDAsXG4gICdhcmVhOjpjb29yZHMnOiAwLFxuICAnYXJlYTo6aHJlZic6IDEsXG4gICdhcmVhOjpub2hyZWYnOiAwLFxuICAnYXJlYTo6b25ibHVyJzogMixcbiAgJ2FyZWE6Om9uZm9jdXMnOiAyLFxuICAnYXJlYTo6c2hhcGUnOiAwLFxuICAnYXJlYTo6dGFiaW5kZXgnOiAwLFxuICAnYXJlYTo6dGFyZ2V0JzogMTAsXG4gICdhdWRpbzo6Y29udHJvbHMnOiAwLFxuICAnYXVkaW86Omxvb3AnOiAwLFxuICAnYXVkaW86Om1lZGlhZ3JvdXAnOiA1LFxuICAnYXVkaW86Om11dGVkJzogMCxcbiAgJ2F1ZGlvOjpwcmVsb2FkJzogMCxcbiAgJ2Jkbzo6ZGlyJzogMCxcbiAgJ2Jsb2NrcXVvdGU6OmNpdGUnOiAxLFxuICAnYnI6OmNsZWFyJzogMCxcbiAgJ2J1dHRvbjo6YWNjZXNza2V5JzogMCxcbiAgJ2J1dHRvbjo6ZGlzYWJsZWQnOiAwLFxuICAnYnV0dG9uOjpuYW1lJzogOCxcbiAgJ2J1dHRvbjo6b25ibHVyJzogMixcbiAgJ2J1dHRvbjo6b25mb2N1cyc6IDIsXG4gICdidXR0b246OnRhYmluZGV4JzogMCxcbiAgJ2J1dHRvbjo6dHlwZSc6IDAsXG4gICdidXR0b246OnZhbHVlJzogMCxcbiAgJ2NhbnZhczo6aGVpZ2h0JzogMCxcbiAgJ2NhbnZhczo6d2lkdGgnOiAwLFxuICAnY2FwdGlvbjo6YWxpZ24nOiAwLFxuICAnY29sOjphbGlnbic6IDAsXG4gICdjb2w6OmNoYXInOiAwLFxuICAnY29sOjpjaGFyb2ZmJzogMCxcbiAgJ2NvbDo6c3Bhbic6IDAsXG4gICdjb2w6OnZhbGlnbic6IDAsXG4gICdjb2w6OndpZHRoJzogMCxcbiAgJ2NvbGdyb3VwOjphbGlnbic6IDAsXG4gICdjb2xncm91cDo6Y2hhcic6IDAsXG4gICdjb2xncm91cDo6Y2hhcm9mZic6IDAsXG4gICdjb2xncm91cDo6c3Bhbic6IDAsXG4gICdjb2xncm91cDo6dmFsaWduJzogMCxcbiAgJ2NvbGdyb3VwOjp3aWR0aCc6IDAsXG4gICdjb21tYW5kOjpjaGVja2VkJzogMCxcbiAgJ2NvbW1hbmQ6OmNvbW1hbmQnOiA1LFxuICAnY29tbWFuZDo6ZGlzYWJsZWQnOiAwLFxuICAnY29tbWFuZDo6aWNvbic6IDEsXG4gICdjb21tYW5kOjpsYWJlbCc6IDAsXG4gICdjb21tYW5kOjpyYWRpb2dyb3VwJzogMCxcbiAgJ2NvbW1hbmQ6OnR5cGUnOiAwLFxuICAnZGF0YTo6dmFsdWUnOiAwLFxuICAnZGVsOjpjaXRlJzogMSxcbiAgJ2RlbDo6ZGF0ZXRpbWUnOiAwLFxuICAnZGV0YWlsczo6b3Blbic6IDAsXG4gICdkaXI6OmNvbXBhY3QnOiAwLFxuICAnZGl2OjphbGlnbic6IDAsXG4gICdkbDo6Y29tcGFjdCc6IDAsXG4gICdmaWVsZHNldDo6ZGlzYWJsZWQnOiAwLFxuICAnZm9udDo6Y29sb3InOiAwLFxuICAnZm9udDo6ZmFjZSc6IDAsXG4gICdmb250OjpzaXplJzogMCxcbiAgJ2Zvcm06OmFjY2VwdCc6IDAsXG4gICdmb3JtOjphY3Rpb24nOiAxLFxuICAnZm9ybTo6YXV0b2NvbXBsZXRlJzogMCxcbiAgJ2Zvcm06OmVuY3R5cGUnOiAwLFxuICAnZm9ybTo6bWV0aG9kJzogMCxcbiAgJ2Zvcm06Om5hbWUnOiA3LFxuICAnZm9ybTo6bm92YWxpZGF0ZSc6IDAsXG4gICdmb3JtOjpvbnJlc2V0JzogMixcbiAgJ2Zvcm06Om9uc3VibWl0JzogMixcbiAgJ2Zvcm06OnRhcmdldCc6IDEwLFxuICAnaDE6OmFsaWduJzogMCxcbiAgJ2gyOjphbGlnbic6IDAsXG4gICdoMzo6YWxpZ24nOiAwLFxuICAnaDQ6OmFsaWduJzogMCxcbiAgJ2g1OjphbGlnbic6IDAsXG4gICdoNjo6YWxpZ24nOiAwLFxuICAnaHI6OmFsaWduJzogMCxcbiAgJ2hyOjpub3NoYWRlJzogMCxcbiAgJ2hyOjpzaXplJzogMCxcbiAgJ2hyOjp3aWR0aCc6IDAsXG4gICdpZnJhbWU6OmFsaWduJzogMCxcbiAgJ2lmcmFtZTo6ZnJhbWVib3JkZXInOiAwLFxuICAnaWZyYW1lOjpoZWlnaHQnOiAwLFxuICAnaWZyYW1lOjptYXJnaW5oZWlnaHQnOiAwLFxuICAnaWZyYW1lOjptYXJnaW53aWR0aCc6IDAsXG4gICdpZnJhbWU6OndpZHRoJzogMCxcbiAgJ2ltZzo6YWxpZ24nOiAwLFxuICAnaW1nOjphbHQnOiAwLFxuICAnaW1nOjpib3JkZXInOiAwLFxuICAnaW1nOjpoZWlnaHQnOiAwLFxuICAnaW1nOjpoc3BhY2UnOiAwLFxuICAnaW1nOjppc21hcCc6IDAsXG4gICdpbWc6Om5hbWUnOiA3LFxuICAnaW1nOjpzcmMnOiAxLFxuICAnaW1nOjp1c2VtYXAnOiAxMSxcbiAgJ2ltZzo6dnNwYWNlJzogMCxcbiAgJ2ltZzo6d2lkdGgnOiAwLFxuICAnaW5wdXQ6OmFjY2VwdCc6IDAsXG4gICdpbnB1dDo6YWNjZXNza2V5JzogMCxcbiAgJ2lucHV0OjphbGlnbic6IDAsXG4gICdpbnB1dDo6YWx0JzogMCxcbiAgJ2lucHV0OjphdXRvY29tcGxldGUnOiAwLFxuICAnaW5wdXQ6OmNoZWNrZWQnOiAwLFxuICAnaW5wdXQ6OmRpc2FibGVkJzogMCxcbiAgJ2lucHV0OjppbnB1dG1vZGUnOiAwLFxuICAnaW5wdXQ6OmlzbWFwJzogMCxcbiAgJ2lucHV0OjpsaXN0JzogNSxcbiAgJ2lucHV0OjptYXgnOiAwLFxuICAnaW5wdXQ6Om1heGxlbmd0aCc6IDAsXG4gICdpbnB1dDo6bWluJzogMCxcbiAgJ2lucHV0OjptdWx0aXBsZSc6IDAsXG4gICdpbnB1dDo6bmFtZSc6IDgsXG4gICdpbnB1dDo6b25ibHVyJzogMixcbiAgJ2lucHV0OjpvbmNoYW5nZSc6IDIsXG4gICdpbnB1dDo6b25mb2N1cyc6IDIsXG4gICdpbnB1dDo6b25zZWxlY3QnOiAyLFxuICAnaW5wdXQ6OnBsYWNlaG9sZGVyJzogMCxcbiAgJ2lucHV0OjpyZWFkb25seSc6IDAsXG4gICdpbnB1dDo6cmVxdWlyZWQnOiAwLFxuICAnaW5wdXQ6OnNpemUnOiAwLFxuICAnaW5wdXQ6OnNyYyc6IDEsXG4gICdpbnB1dDo6c3RlcCc6IDAsXG4gICdpbnB1dDo6dGFiaW5kZXgnOiAwLFxuICAnaW5wdXQ6OnR5cGUnOiAwLFxuICAnaW5wdXQ6OnVzZW1hcCc6IDExLFxuICAnaW5wdXQ6OnZhbHVlJzogMCxcbiAgJ2luczo6Y2l0ZSc6IDEsXG4gICdpbnM6OmRhdGV0aW1lJzogMCxcbiAgJ2xhYmVsOjphY2Nlc3NrZXknOiAwLFxuICAnbGFiZWw6OmZvcic6IDUsXG4gICdsYWJlbDo6b25ibHVyJzogMixcbiAgJ2xhYmVsOjpvbmZvY3VzJzogMixcbiAgJ2xlZ2VuZDo6YWNjZXNza2V5JzogMCxcbiAgJ2xlZ2VuZDo6YWxpZ24nOiAwLFxuICAnbGk6OnR5cGUnOiAwLFxuICAnbGk6OnZhbHVlJzogMCxcbiAgJ21hcDo6bmFtZSc6IDcsXG4gICdtZW51Ojpjb21wYWN0JzogMCxcbiAgJ21lbnU6OmxhYmVsJzogMCxcbiAgJ21lbnU6OnR5cGUnOiAwLFxuICAnbWV0ZXI6OmhpZ2gnOiAwLFxuICAnbWV0ZXI6Omxvdyc6IDAsXG4gICdtZXRlcjo6bWF4JzogMCxcbiAgJ21ldGVyOjptaW4nOiAwLFxuICAnbWV0ZXI6OnZhbHVlJzogMCxcbiAgJ29sOjpjb21wYWN0JzogMCxcbiAgJ29sOjpyZXZlcnNlZCc6IDAsXG4gICdvbDo6c3RhcnQnOiAwLFxuICAnb2w6OnR5cGUnOiAwLFxuICAnb3B0Z3JvdXA6OmRpc2FibGVkJzogMCxcbiAgJ29wdGdyb3VwOjpsYWJlbCc6IDAsXG4gICdvcHRpb246OmRpc2FibGVkJzogMCxcbiAgJ29wdGlvbjo6bGFiZWwnOiAwLFxuICAnb3B0aW9uOjpzZWxlY3RlZCc6IDAsXG4gICdvcHRpb246OnZhbHVlJzogMCxcbiAgJ291dHB1dDo6Zm9yJzogNixcbiAgJ291dHB1dDo6bmFtZSc6IDgsXG4gICdwOjphbGlnbic6IDAsXG4gICdwcmU6OndpZHRoJzogMCxcbiAgJ3Byb2dyZXNzOjptYXgnOiAwLFxuICAncHJvZ3Jlc3M6Om1pbic6IDAsXG4gICdwcm9ncmVzczo6dmFsdWUnOiAwLFxuICAncTo6Y2l0ZSc6IDEsXG4gICdzZWxlY3Q6OmF1dG9jb21wbGV0ZSc6IDAsXG4gICdzZWxlY3Q6OmRpc2FibGVkJzogMCxcbiAgJ3NlbGVjdDo6bXVsdGlwbGUnOiAwLFxuICAnc2VsZWN0OjpuYW1lJzogOCxcbiAgJ3NlbGVjdDo6b25ibHVyJzogMixcbiAgJ3NlbGVjdDo6b25jaGFuZ2UnOiAyLFxuICAnc2VsZWN0OjpvbmZvY3VzJzogMixcbiAgJ3NlbGVjdDo6cmVxdWlyZWQnOiAwLFxuICAnc2VsZWN0OjpzaXplJzogMCxcbiAgJ3NlbGVjdDo6dGFiaW5kZXgnOiAwLFxuICAnc291cmNlOjp0eXBlJzogMCxcbiAgJ3RhYmxlOjphbGlnbic6IDAsXG4gICd0YWJsZTo6Ymdjb2xvcic6IDAsXG4gICd0YWJsZTo6Ym9yZGVyJzogMCxcbiAgJ3RhYmxlOjpjZWxscGFkZGluZyc6IDAsXG4gICd0YWJsZTo6Y2VsbHNwYWNpbmcnOiAwLFxuICAndGFibGU6OmZyYW1lJzogMCxcbiAgJ3RhYmxlOjpydWxlcyc6IDAsXG4gICd0YWJsZTo6c3VtbWFyeSc6IDAsXG4gICd0YWJsZTo6d2lkdGgnOiAwLFxuICAndGJvZHk6OmFsaWduJzogMCxcbiAgJ3Rib2R5OjpjaGFyJzogMCxcbiAgJ3Rib2R5OjpjaGFyb2ZmJzogMCxcbiAgJ3Rib2R5Ojp2YWxpZ24nOiAwLFxuICAndGQ6OmFiYnInOiAwLFxuICAndGQ6OmFsaWduJzogMCxcbiAgJ3RkOjpheGlzJzogMCxcbiAgJ3RkOjpiZ2NvbG9yJzogMCxcbiAgJ3RkOjpjaGFyJzogMCxcbiAgJ3RkOjpjaGFyb2ZmJzogMCxcbiAgJ3RkOjpjb2xzcGFuJzogMCxcbiAgJ3RkOjpoZWFkZXJzJzogNixcbiAgJ3RkOjpoZWlnaHQnOiAwLFxuICAndGQ6Om5vd3JhcCc6IDAsXG4gICd0ZDo6cm93c3Bhbic6IDAsXG4gICd0ZDo6c2NvcGUnOiAwLFxuICAndGQ6OnZhbGlnbic6IDAsXG4gICd0ZDo6d2lkdGgnOiAwLFxuICAndGV4dGFyZWE6OmFjY2Vzc2tleSc6IDAsXG4gICd0ZXh0YXJlYTo6YXV0b2NvbXBsZXRlJzogMCxcbiAgJ3RleHRhcmVhOjpjb2xzJzogMCxcbiAgJ3RleHRhcmVhOjpkaXNhYmxlZCc6IDAsXG4gICd0ZXh0YXJlYTo6aW5wdXRtb2RlJzogMCxcbiAgJ3RleHRhcmVhOjpuYW1lJzogOCxcbiAgJ3RleHRhcmVhOjpvbmJsdXInOiAyLFxuICAndGV4dGFyZWE6Om9uY2hhbmdlJzogMixcbiAgJ3RleHRhcmVhOjpvbmZvY3VzJzogMixcbiAgJ3RleHRhcmVhOjpvbnNlbGVjdCc6IDIsXG4gICd0ZXh0YXJlYTo6cGxhY2Vob2xkZXInOiAwLFxuICAndGV4dGFyZWE6OnJlYWRvbmx5JzogMCxcbiAgJ3RleHRhcmVhOjpyZXF1aXJlZCc6IDAsXG4gICd0ZXh0YXJlYTo6cm93cyc6IDAsXG4gICd0ZXh0YXJlYTo6dGFiaW5kZXgnOiAwLFxuICAndGV4dGFyZWE6OndyYXAnOiAwLFxuICAndGZvb3Q6OmFsaWduJzogMCxcbiAgJ3Rmb290OjpjaGFyJzogMCxcbiAgJ3Rmb290OjpjaGFyb2ZmJzogMCxcbiAgJ3Rmb290Ojp2YWxpZ24nOiAwLFxuICAndGg6OmFiYnInOiAwLFxuICAndGg6OmFsaWduJzogMCxcbiAgJ3RoOjpheGlzJzogMCxcbiAgJ3RoOjpiZ2NvbG9yJzogMCxcbiAgJ3RoOjpjaGFyJzogMCxcbiAgJ3RoOjpjaGFyb2ZmJzogMCxcbiAgJ3RoOjpjb2xzcGFuJzogMCxcbiAgJ3RoOjpoZWFkZXJzJzogNixcbiAgJ3RoOjpoZWlnaHQnOiAwLFxuICAndGg6Om5vd3JhcCc6IDAsXG4gICd0aDo6cm93c3Bhbic6IDAsXG4gICd0aDo6c2NvcGUnOiAwLFxuICAndGg6OnZhbGlnbic6IDAsXG4gICd0aDo6d2lkdGgnOiAwLFxuICAndGhlYWQ6OmFsaWduJzogMCxcbiAgJ3RoZWFkOjpjaGFyJzogMCxcbiAgJ3RoZWFkOjpjaGFyb2ZmJzogMCxcbiAgJ3RoZWFkOjp2YWxpZ24nOiAwLFxuICAndHI6OmFsaWduJzogMCxcbiAgJ3RyOjpiZ2NvbG9yJzogMCxcbiAgJ3RyOjpjaGFyJzogMCxcbiAgJ3RyOjpjaGFyb2ZmJzogMCxcbiAgJ3RyOjp2YWxpZ24nOiAwLFxuICAndHJhY2s6OmRlZmF1bHQnOiAwLFxuICAndHJhY2s6OmtpbmQnOiAwLFxuICAndHJhY2s6OmxhYmVsJzogMCxcbiAgJ3RyYWNrOjpzcmNsYW5nJzogMCxcbiAgJ3VsOjpjb21wYWN0JzogMCxcbiAgJ3VsOjp0eXBlJzogMCxcbiAgJ3ZpZGVvOjpjb250cm9scyc6IDAsXG4gICd2aWRlbzo6aGVpZ2h0JzogMCxcbiAgJ3ZpZGVvOjpsb29wJzogMCxcbiAgJ3ZpZGVvOjptZWRpYWdyb3VwJzogNSxcbiAgJ3ZpZGVvOjptdXRlZCc6IDAsXG4gICd2aWRlbzo6cG9zdGVyJzogMSxcbiAgJ3ZpZGVvOjpwcmVsb2FkJzogMCxcbiAgJ3ZpZGVvOjp3aWR0aCc6IDBcbn07XG5odG1sNFsgJ0FUVFJJQlMnIF0gPSBodG1sNC5BVFRSSUJTO1xuaHRtbDQuZWZsYWdzID0ge1xuICAnT1BUSU9OQUxfRU5EVEFHJzogMSxcbiAgJ0VNUFRZJzogMixcbiAgJ0NEQVRBJzogNCxcbiAgJ1JDREFUQSc6IDgsXG4gICdVTlNBRkUnOiAxNixcbiAgJ0ZPTERBQkxFJzogMzIsXG4gICdTQ1JJUFQnOiA2NCxcbiAgJ1NUWUxFJzogMTI4LFxuICAnVklSVFVBTElaRUQnOiAyNTZcbn07XG5odG1sNFsgJ2VmbGFncycgXSA9IGh0bWw0LmVmbGFncztcbi8vIHRoZXNlIGFyZSBiaXRtYXNrcyBvZiB0aGUgZWZsYWdzIGFib3ZlLlxuaHRtbDQuRUxFTUVOVFMgPSB7XG4gICdhJzogMCxcbiAgJ2FiYnInOiAwLFxuICAnYWNyb255bSc6IDAsXG4gICdhZGRyZXNzJzogMCxcbiAgJ2FwcGxldCc6IDI3MixcbiAgJ2FyZWEnOiAyLFxuICAnYXJ0aWNsZSc6IDAsXG4gICdhc2lkZSc6IDAsXG4gICdhdWRpbyc6IDAsXG4gICdiJzogMCxcbiAgJ2Jhc2UnOiAyNzQsXG4gICdiYXNlZm9udCc6IDI3NCxcbiAgJ2JkaSc6IDAsXG4gICdiZG8nOiAwLFxuICAnYmlnJzogMCxcbiAgJ2Jsb2NrcXVvdGUnOiAwLFxuICAnYm9keSc6IDMwNSxcbiAgJ2JyJzogMixcbiAgJ2J1dHRvbic6IDAsXG4gICdjYW52YXMnOiAwLFxuICAnY2FwdGlvbic6IDAsXG4gICdjZW50ZXInOiAwLFxuICAnY2l0ZSc6IDAsXG4gICdjb2RlJzogMCxcbiAgJ2NvbCc6IDIsXG4gICdjb2xncm91cCc6IDEsXG4gICdjb21tYW5kJzogMixcbiAgJ2RhdGEnOiAwLFxuICAnZGF0YWxpc3QnOiAwLFxuICAnZGQnOiAxLFxuICAnZGVsJzogMCxcbiAgJ2RldGFpbHMnOiAwLFxuICAnZGZuJzogMCxcbiAgJ2RpYWxvZyc6IDI3MixcbiAgJ2Rpcic6IDAsXG4gICdkaXYnOiAwLFxuICAnZGwnOiAwLFxuICAnZHQnOiAxLFxuICAnZW0nOiAwLFxuICAnZmllbGRzZXQnOiAwLFxuICAnZmlnY2FwdGlvbic6IDAsXG4gICdmaWd1cmUnOiAwLFxuICAnZm9udCc6IDAsXG4gICdmb290ZXInOiAwLFxuICAnZm9ybSc6IDAsXG4gICdmcmFtZSc6IDI3NCxcbiAgJ2ZyYW1lc2V0JzogMjcyLFxuICAnaDEnOiAwLFxuICAnaDInOiAwLFxuICAnaDMnOiAwLFxuICAnaDQnOiAwLFxuICAnaDUnOiAwLFxuICAnaDYnOiAwLFxuICAnaGVhZCc6IDMwNSxcbiAgJ2hlYWRlcic6IDAsXG4gICdoZ3JvdXAnOiAwLFxuICAnaHInOiAyLFxuICAnaHRtbCc6IDMwNSxcbiAgJ2knOiAwLFxuICAnaWZyYW1lJzogMTYsXG4gICdpbWcnOiAyLFxuICAnaW5wdXQnOiAyLFxuICAnaW5zJzogMCxcbiAgJ2lzaW5kZXgnOiAyNzQsXG4gICdrYmQnOiAwLFxuICAna2V5Z2VuJzogMjc0LFxuICAnbGFiZWwnOiAwLFxuICAnbGVnZW5kJzogMCxcbiAgJ2xpJzogMSxcbiAgJ2xpbmsnOiAyNzQsXG4gICdtYXAnOiAwLFxuICAnbWFyayc6IDAsXG4gICdtZW51JzogMCxcbiAgJ21ldGEnOiAyNzQsXG4gICdtZXRlcic6IDAsXG4gICduYXYnOiAwLFxuICAnbm9icic6IDAsXG4gICdub2VtYmVkJzogMjc2LFxuICAnbm9mcmFtZXMnOiAyNzYsXG4gICdub3NjcmlwdCc6IDI3NixcbiAgJ29iamVjdCc6IDI3MixcbiAgJ29sJzogMCxcbiAgJ29wdGdyb3VwJzogMCxcbiAgJ29wdGlvbic6IDEsXG4gICdvdXRwdXQnOiAwLFxuICAncCc6IDEsXG4gICdwYXJhbSc6IDI3NCxcbiAgJ3ByZSc6IDAsXG4gICdwcm9ncmVzcyc6IDAsXG4gICdxJzogMCxcbiAgJ3MnOiAwLFxuICAnc2FtcCc6IDAsXG4gICdzY3JpcHQnOiA4NCxcbiAgJ3NlY3Rpb24nOiAwLFxuICAnc2VsZWN0JzogMCxcbiAgJ3NtYWxsJzogMCxcbiAgJ3NvdXJjZSc6IDIsXG4gICdzcGFuJzogMCxcbiAgJ3N0cmlrZSc6IDAsXG4gICdzdHJvbmcnOiAwLFxuICAnc3R5bGUnOiAxNDgsXG4gICdzdWInOiAwLFxuICAnc3VtbWFyeSc6IDAsXG4gICdzdXAnOiAwLFxuICAndGFibGUnOiAwLFxuICAndGJvZHknOiAxLFxuICAndGQnOiAxLFxuICAndGV4dGFyZWEnOiA4LFxuICAndGZvb3QnOiAxLFxuICAndGgnOiAxLFxuICAndGhlYWQnOiAxLFxuICAndGltZSc6IDAsXG4gICd0aXRsZSc6IDI4MCxcbiAgJ3RyJzogMSxcbiAgJ3RyYWNrJzogMixcbiAgJ3R0JzogMCxcbiAgJ3UnOiAwLFxuICAndWwnOiAwLFxuICAndmFyJzogMCxcbiAgJ3ZpZGVvJzogMCxcbiAgJ3dicic6IDJcbn07XG5odG1sNFsgJ0VMRU1FTlRTJyBdID0gaHRtbDQuRUxFTUVOVFM7XG5odG1sNC5FTEVNRU5UX0RPTV9JTlRFUkZBQ0VTID0ge1xuICAnYSc6ICdIVE1MQW5jaG9yRWxlbWVudCcsXG4gICdhYmJyJzogJ0hUTUxFbGVtZW50JyxcbiAgJ2Fjcm9ueW0nOiAnSFRNTEVsZW1lbnQnLFxuICAnYWRkcmVzcyc6ICdIVE1MRWxlbWVudCcsXG4gICdhcHBsZXQnOiAnSFRNTEFwcGxldEVsZW1lbnQnLFxuICAnYXJlYSc6ICdIVE1MQXJlYUVsZW1lbnQnLFxuICAnYXJ0aWNsZSc6ICdIVE1MRWxlbWVudCcsXG4gICdhc2lkZSc6ICdIVE1MRWxlbWVudCcsXG4gICdhdWRpbyc6ICdIVE1MQXVkaW9FbGVtZW50JyxcbiAgJ2InOiAnSFRNTEVsZW1lbnQnLFxuICAnYmFzZSc6ICdIVE1MQmFzZUVsZW1lbnQnLFxuICAnYmFzZWZvbnQnOiAnSFRNTEJhc2VGb250RWxlbWVudCcsXG4gICdiZGknOiAnSFRNTEVsZW1lbnQnLFxuICAnYmRvJzogJ0hUTUxFbGVtZW50JyxcbiAgJ2JpZyc6ICdIVE1MRWxlbWVudCcsXG4gICdibG9ja3F1b3RlJzogJ0hUTUxRdW90ZUVsZW1lbnQnLFxuICAnYm9keSc6ICdIVE1MQm9keUVsZW1lbnQnLFxuICAnYnInOiAnSFRNTEJSRWxlbWVudCcsXG4gICdidXR0b24nOiAnSFRNTEJ1dHRvbkVsZW1lbnQnLFxuICAnY2FudmFzJzogJ0hUTUxDYW52YXNFbGVtZW50JyxcbiAgJ2NhcHRpb24nOiAnSFRNTFRhYmxlQ2FwdGlvbkVsZW1lbnQnLFxuICAnY2VudGVyJzogJ0hUTUxFbGVtZW50JyxcbiAgJ2NpdGUnOiAnSFRNTEVsZW1lbnQnLFxuICAnY29kZSc6ICdIVE1MRWxlbWVudCcsXG4gICdjb2wnOiAnSFRNTFRhYmxlQ29sRWxlbWVudCcsXG4gICdjb2xncm91cCc6ICdIVE1MVGFibGVDb2xFbGVtZW50JyxcbiAgJ2NvbW1hbmQnOiAnSFRNTENvbW1hbmRFbGVtZW50JyxcbiAgJ2RhdGEnOiAnSFRNTEVsZW1lbnQnLFxuICAnZGF0YWxpc3QnOiAnSFRNTERhdGFMaXN0RWxlbWVudCcsXG4gICdkZCc6ICdIVE1MRWxlbWVudCcsXG4gICdkZWwnOiAnSFRNTE1vZEVsZW1lbnQnLFxuICAnZGV0YWlscyc6ICdIVE1MRGV0YWlsc0VsZW1lbnQnLFxuICAnZGZuJzogJ0hUTUxFbGVtZW50JyxcbiAgJ2RpYWxvZyc6ICdIVE1MRGlhbG9nRWxlbWVudCcsXG4gICdkaXInOiAnSFRNTERpcmVjdG9yeUVsZW1lbnQnLFxuICAnZGl2JzogJ0hUTUxEaXZFbGVtZW50JyxcbiAgJ2RsJzogJ0hUTUxETGlzdEVsZW1lbnQnLFxuICAnZHQnOiAnSFRNTEVsZW1lbnQnLFxuICAnZW0nOiAnSFRNTEVsZW1lbnQnLFxuICAnZmllbGRzZXQnOiAnSFRNTEZpZWxkU2V0RWxlbWVudCcsXG4gICdmaWdjYXB0aW9uJzogJ0hUTUxFbGVtZW50JyxcbiAgJ2ZpZ3VyZSc6ICdIVE1MRWxlbWVudCcsXG4gICdmb250JzogJ0hUTUxGb250RWxlbWVudCcsXG4gICdmb290ZXInOiAnSFRNTEVsZW1lbnQnLFxuICAnZm9ybSc6ICdIVE1MRm9ybUVsZW1lbnQnLFxuICAnZnJhbWUnOiAnSFRNTEZyYW1lRWxlbWVudCcsXG4gICdmcmFtZXNldCc6ICdIVE1MRnJhbWVTZXRFbGVtZW50JyxcbiAgJ2gxJzogJ0hUTUxIZWFkaW5nRWxlbWVudCcsXG4gICdoMic6ICdIVE1MSGVhZGluZ0VsZW1lbnQnLFxuICAnaDMnOiAnSFRNTEhlYWRpbmdFbGVtZW50JyxcbiAgJ2g0JzogJ0hUTUxIZWFkaW5nRWxlbWVudCcsXG4gICdoNSc6ICdIVE1MSGVhZGluZ0VsZW1lbnQnLFxuICAnaDYnOiAnSFRNTEhlYWRpbmdFbGVtZW50JyxcbiAgJ2hlYWQnOiAnSFRNTEhlYWRFbGVtZW50JyxcbiAgJ2hlYWRlcic6ICdIVE1MRWxlbWVudCcsXG4gICdoZ3JvdXAnOiAnSFRNTEVsZW1lbnQnLFxuICAnaHInOiAnSFRNTEhSRWxlbWVudCcsXG4gICdodG1sJzogJ0hUTUxIdG1sRWxlbWVudCcsXG4gICdpJzogJ0hUTUxFbGVtZW50JyxcbiAgJ2lmcmFtZSc6ICdIVE1MSUZyYW1lRWxlbWVudCcsXG4gICdpbWcnOiAnSFRNTEltYWdlRWxlbWVudCcsXG4gICdpbnB1dCc6ICdIVE1MSW5wdXRFbGVtZW50JyxcbiAgJ2lucyc6ICdIVE1MTW9kRWxlbWVudCcsXG4gICdpc2luZGV4JzogJ0hUTUxVbmtub3duRWxlbWVudCcsXG4gICdrYmQnOiAnSFRNTEVsZW1lbnQnLFxuICAna2V5Z2VuJzogJ0hUTUxLZXlnZW5FbGVtZW50JyxcbiAgJ2xhYmVsJzogJ0hUTUxMYWJlbEVsZW1lbnQnLFxuICAnbGVnZW5kJzogJ0hUTUxMZWdlbmRFbGVtZW50JyxcbiAgJ2xpJzogJ0hUTUxMSUVsZW1lbnQnLFxuICAnbGluayc6ICdIVE1MTGlua0VsZW1lbnQnLFxuICAnbWFwJzogJ0hUTUxNYXBFbGVtZW50JyxcbiAgJ21hcmsnOiAnSFRNTEVsZW1lbnQnLFxuICAnbWVudSc6ICdIVE1MTWVudUVsZW1lbnQnLFxuICAnbWV0YSc6ICdIVE1MTWV0YUVsZW1lbnQnLFxuICAnbWV0ZXInOiAnSFRNTE1ldGVyRWxlbWVudCcsXG4gICduYXYnOiAnSFRNTEVsZW1lbnQnLFxuICAnbm9icic6ICdIVE1MRWxlbWVudCcsXG4gICdub2VtYmVkJzogJ0hUTUxFbGVtZW50JyxcbiAgJ25vZnJhbWVzJzogJ0hUTUxFbGVtZW50JyxcbiAgJ25vc2NyaXB0JzogJ0hUTUxFbGVtZW50JyxcbiAgJ29iamVjdCc6ICdIVE1MT2JqZWN0RWxlbWVudCcsXG4gICdvbCc6ICdIVE1MT0xpc3RFbGVtZW50JyxcbiAgJ29wdGdyb3VwJzogJ0hUTUxPcHRHcm91cEVsZW1lbnQnLFxuICAnb3B0aW9uJzogJ0hUTUxPcHRpb25FbGVtZW50JyxcbiAgJ291dHB1dCc6ICdIVE1MT3V0cHV0RWxlbWVudCcsXG4gICdwJzogJ0hUTUxQYXJhZ3JhcGhFbGVtZW50JyxcbiAgJ3BhcmFtJzogJ0hUTUxQYXJhbUVsZW1lbnQnLFxuICAncHJlJzogJ0hUTUxQcmVFbGVtZW50JyxcbiAgJ3Byb2dyZXNzJzogJ0hUTUxQcm9ncmVzc0VsZW1lbnQnLFxuICAncSc6ICdIVE1MUXVvdGVFbGVtZW50JyxcbiAgJ3MnOiAnSFRNTEVsZW1lbnQnLFxuICAnc2FtcCc6ICdIVE1MRWxlbWVudCcsXG4gICdzY3JpcHQnOiAnSFRNTFNjcmlwdEVsZW1lbnQnLFxuICAnc2VjdGlvbic6ICdIVE1MRWxlbWVudCcsXG4gICdzZWxlY3QnOiAnSFRNTFNlbGVjdEVsZW1lbnQnLFxuICAnc21hbGwnOiAnSFRNTEVsZW1lbnQnLFxuICAnc291cmNlJzogJ0hUTUxTb3VyY2VFbGVtZW50JyxcbiAgJ3NwYW4nOiAnSFRNTFNwYW5FbGVtZW50JyxcbiAgJ3N0cmlrZSc6ICdIVE1MRWxlbWVudCcsXG4gICdzdHJvbmcnOiAnSFRNTEVsZW1lbnQnLFxuICAnc3R5bGUnOiAnSFRNTFN0eWxlRWxlbWVudCcsXG4gICdzdWInOiAnSFRNTEVsZW1lbnQnLFxuICAnc3VtbWFyeSc6ICdIVE1MRWxlbWVudCcsXG4gICdzdXAnOiAnSFRNTEVsZW1lbnQnLFxuICAndGFibGUnOiAnSFRNTFRhYmxlRWxlbWVudCcsXG4gICd0Ym9keSc6ICdIVE1MVGFibGVTZWN0aW9uRWxlbWVudCcsXG4gICd0ZCc6ICdIVE1MVGFibGVEYXRhQ2VsbEVsZW1lbnQnLFxuICAndGV4dGFyZWEnOiAnSFRNTFRleHRBcmVhRWxlbWVudCcsXG4gICd0Zm9vdCc6ICdIVE1MVGFibGVTZWN0aW9uRWxlbWVudCcsXG4gICd0aCc6ICdIVE1MVGFibGVIZWFkZXJDZWxsRWxlbWVudCcsXG4gICd0aGVhZCc6ICdIVE1MVGFibGVTZWN0aW9uRWxlbWVudCcsXG4gICd0aW1lJzogJ0hUTUxUaW1lRWxlbWVudCcsXG4gICd0aXRsZSc6ICdIVE1MVGl0bGVFbGVtZW50JyxcbiAgJ3RyJzogJ0hUTUxUYWJsZVJvd0VsZW1lbnQnLFxuICAndHJhY2snOiAnSFRNTFRyYWNrRWxlbWVudCcsXG4gICd0dCc6ICdIVE1MRWxlbWVudCcsXG4gICd1JzogJ0hUTUxFbGVtZW50JyxcbiAgJ3VsJzogJ0hUTUxVTGlzdEVsZW1lbnQnLFxuICAndmFyJzogJ0hUTUxFbGVtZW50JyxcbiAgJ3ZpZGVvJzogJ0hUTUxWaWRlb0VsZW1lbnQnLFxuICAnd2JyJzogJ0hUTUxFbGVtZW50J1xufTtcbmh0bWw0WyAnRUxFTUVOVF9ET01fSU5URVJGQUNFUycgXSA9IGh0bWw0LkVMRU1FTlRfRE9NX0lOVEVSRkFDRVM7XG5odG1sNC51ZWZmZWN0cyA9IHtcbiAgJ05PVF9MT0FERUQnOiAwLFxuICAnU0FNRV9ET0NVTUVOVCc6IDEsXG4gICdORVdfRE9DVU1FTlQnOiAyXG59O1xuaHRtbDRbICd1ZWZmZWN0cycgXSA9IGh0bWw0LnVlZmZlY3RzO1xuaHRtbDQuVVJJRUZGRUNUUyA9IHtcbiAgJ2E6OmhyZWYnOiAyLFxuICAnYXJlYTo6aHJlZic6IDIsXG4gICdibG9ja3F1b3RlOjpjaXRlJzogMCxcbiAgJ2NvbW1hbmQ6Omljb24nOiAxLFxuICAnZGVsOjpjaXRlJzogMCxcbiAgJ2Zvcm06OmFjdGlvbic6IDIsXG4gICdpbWc6OnNyYyc6IDEsXG4gICdpbnB1dDo6c3JjJzogMSxcbiAgJ2luczo6Y2l0ZSc6IDAsXG4gICdxOjpjaXRlJzogMCxcbiAgJ3ZpZGVvOjpwb3N0ZXInOiAxXG59O1xuaHRtbDRbICdVUklFRkZFQ1RTJyBdID0gaHRtbDQuVVJJRUZGRUNUUztcbmh0bWw0Lmx0eXBlcyA9IHtcbiAgJ1VOU0FOREJPWEVEJzogMixcbiAgJ1NBTkRCT1hFRCc6IDEsXG4gICdEQVRBJzogMFxufTtcbmh0bWw0WyAnbHR5cGVzJyBdID0gaHRtbDQubHR5cGVzO1xuaHRtbDQuTE9BREVSVFlQRVMgPSB7XG4gICdhOjpocmVmJzogMixcbiAgJ2FyZWE6OmhyZWYnOiAyLFxuICAnYmxvY2txdW90ZTo6Y2l0ZSc6IDIsXG4gICdjb21tYW5kOjppY29uJzogMSxcbiAgJ2RlbDo6Y2l0ZSc6IDIsXG4gICdmb3JtOjphY3Rpb24nOiAyLFxuICAnaW1nOjpzcmMnOiAxLFxuICAnaW5wdXQ6OnNyYyc6IDEsXG4gICdpbnM6OmNpdGUnOiAyLFxuICAncTo6Y2l0ZSc6IDIsXG4gICd2aWRlbzo6cG9zdGVyJzogMVxufTtcbmh0bWw0WyAnTE9BREVSVFlQRVMnIF0gPSBodG1sNC5MT0FERVJUWVBFUztcblxuLy8gQ29weXJpZ2h0IChDKSAyMDA2IEdvb2dsZSBJbmMuXG4vL1xuLy8gTGljZW5zZWQgdW5kZXIgdGhlIEFwYWNoZSBMaWNlbnNlLCBWZXJzaW9uIDIuMCAodGhlIFwiTGljZW5zZVwiKTtcbi8vIHlvdSBtYXkgbm90IHVzZSB0aGlzIGZpbGUgZXhjZXB0IGluIGNvbXBsaWFuY2Ugd2l0aCB0aGUgTGljZW5zZS5cbi8vIFlvdSBtYXkgb2J0YWluIGEgY29weSBvZiB0aGUgTGljZW5zZSBhdFxuLy9cbi8vICAgICAgaHR0cDovL3d3dy5hcGFjaGUub3JnL2xpY2Vuc2VzL0xJQ0VOU0UtMi4wXG4vL1xuLy8gVW5sZXNzIHJlcXVpcmVkIGJ5IGFwcGxpY2FibGUgbGF3IG9yIGFncmVlZCB0byBpbiB3cml0aW5nLCBzb2Z0d2FyZVxuLy8gZGlzdHJpYnV0ZWQgdW5kZXIgdGhlIExpY2Vuc2UgaXMgZGlzdHJpYnV0ZWQgb24gYW4gXCJBUyBJU1wiIEJBU0lTLFxuLy8gV0lUSE9VVCBXQVJSQU5USUVTIE9SIENPTkRJVElPTlMgT0YgQU5ZIEtJTkQsIGVpdGhlciBleHByZXNzIG9yIGltcGxpZWQuXG4vLyBTZWUgdGhlIExpY2Vuc2UgZm9yIHRoZSBzcGVjaWZpYyBsYW5ndWFnZSBnb3Zlcm5pbmcgcGVybWlzc2lvbnMgYW5kXG4vLyBsaW1pdGF0aW9ucyB1bmRlciB0aGUgTGljZW5zZS5cblxuLyoqXG4gKiBAZmlsZW92ZXJ2aWV3XG4gKiBBbiBIVE1MIHNhbml0aXplciB0aGF0IGNhbiBzYXRpc2Z5IGEgdmFyaWV0eSBvZiBzZWN1cml0eSBwb2xpY2llcy5cbiAqXG4gKiA8cD5cbiAqIFRoZSBIVE1MIHNhbml0aXplciBpcyBidWlsdCBhcm91bmQgYSBTQVggcGFyc2VyIGFuZCBIVE1MIGVsZW1lbnQgYW5kXG4gKiBhdHRyaWJ1dGVzIHNjaGVtYXMuXG4gKlxuICogSWYgdGhlIGNzc3BhcnNlciBpcyBsb2FkZWQsIGlubGluZSBzdHlsZXMgYXJlIHNhbml0aXplZCB1c2luZyB0aGVcbiAqIGNzcyBwcm9wZXJ0eSBhbmQgdmFsdWUgc2NoZW1hcy4gIEVsc2UgdGhleSBhcmUgcmVtb3ZlIGR1cmluZ1xuICogc2FuaXRpemF0aW9uLlxuICpcbiAqIElmIGl0IGV4aXN0cywgdXNlcyBwYXJzZUNzc0RlY2xhcmF0aW9ucywgc2FuaXRpemVDc3NQcm9wZXJ0eSwgIGNzc1NjaGVtYVxuICpcbiAqIEBhdXRob3IgbWlrZXNhbXVlbEBnbWFpbC5jb21cbiAqIEBhdXRob3IgamFzdmlyQGdtYWlsLmNvbVxuICogXFxAcmVxdWlyZXMgaHRtbDQsIFVSSVxuICogXFxAb3ZlcnJpZGVzIHdpbmRvd1xuICogXFxAcHJvdmlkZXMgaHRtbCwgaHRtbF9zYW5pdGl6ZVxuICovXG5cbi8vIFRoZSBUdXJraXNoIGkgc2VlbXMgdG8gYmUgYSBub24taXNzdWUsIGJ1dCBhYm9ydCBpbiBjYXNlIGl0IGlzLlxuaWYgKCdJJy50b0xvd2VyQ2FzZSgpICE9PSAnaScpIHsgdGhyb3cgJ0kvaSBwcm9ibGVtJzsgfVxuXG4vKipcbiAqIFxcQG5hbWVzcGFjZVxuICovXG52YXIgaHRtbCA9IChmdW5jdGlvbihodG1sNCkge1xuXG4gIC8vIEZvciBjbG9zdXJlIGNvbXBpbGVyXG4gIHZhciBwYXJzZUNzc0RlY2xhcmF0aW9ucywgc2FuaXRpemVDc3NQcm9wZXJ0eSwgY3NzU2NoZW1hO1xuICBpZiAoJ3VuZGVmaW5lZCcgIT09IHR5cGVvZiB3aW5kb3cpIHtcbiAgICBwYXJzZUNzc0RlY2xhcmF0aW9ucyA9IHdpbmRvd1sncGFyc2VDc3NEZWNsYXJhdGlvbnMnXTtcbiAgICBzYW5pdGl6ZUNzc1Byb3BlcnR5ID0gd2luZG93WydzYW5pdGl6ZUNzc1Byb3BlcnR5J107XG4gICAgY3NzU2NoZW1hID0gd2luZG93Wydjc3NTY2hlbWEnXTtcbiAgfVxuXG4gIC8vIFRoZSBrZXlzIG9mIHRoaXMgb2JqZWN0IG11c3QgYmUgJ3F1b3RlZCcgb3IgSlNDb21waWxlciB3aWxsIG1hbmdsZSB0aGVtIVxuICAvLyBUaGlzIGlzIGEgcGFydGlhbCBsaXN0IC0tIGxvb2t1cEVudGl0eSgpIHVzZXMgdGhlIGhvc3QgYnJvd3NlcidzIHBhcnNlclxuICAvLyAod2hlbiBhdmFpbGFibGUpIHRvIGltcGxlbWVudCBmdWxsIGVudGl0eSBsb29rdXAuXG4gIC8vIE5vdGUgdGhhdCBlbnRpdGllcyBhcmUgaW4gZ2VuZXJhbCBjYXNlLXNlbnNpdGl2ZTsgdGhlIHVwcGVyY2FzZSBvbmVzIGFyZVxuICAvLyBleHBsaWNpdGx5IGRlZmluZWQgYnkgSFRNTDUgKHByZXN1bWFibHkgYXMgY29tcGF0aWJpbGl0eSkuXG4gIHZhciBFTlRJVElFUyA9IHtcbiAgICAnbHQnOiAnPCcsXG4gICAgJ0xUJzogJzwnLFxuICAgICdndCc6ICc+JyxcbiAgICAnR1QnOiAnPicsXG4gICAgJ2FtcCc6ICcmJyxcbiAgICAnQU1QJzogJyYnLFxuICAgICdxdW90JzogJ1wiJyxcbiAgICAnYXBvcyc6ICdcXCcnLFxuICAgICduYnNwJzogJ1xcdTAwQTAnXG4gIH07XG5cbiAgLy8gUGF0dGVybnMgZm9yIHR5cGVzIG9mIGVudGl0eS9jaGFyYWN0ZXIgcmVmZXJlbmNlIG5hbWVzLlxuICB2YXIgZGVjaW1hbEVzY2FwZVJlID0gL14jKFxcZCspJC87XG4gIHZhciBoZXhFc2NhcGVSZSA9IC9eI3goWzAtOUEtRmEtZl0rKSQvO1xuICAvLyBjb250YWlucyBldmVyeSBlbnRpdHkgcGVyIGh0dHA6Ly93d3cudzMub3JnL1RSLzIwMTEvV0QtaHRtbDUtMjAxMTAxMTMvbmFtZWQtY2hhcmFjdGVyLXJlZmVyZW5jZXMuaHRtbFxuICB2YXIgc2FmZUVudGl0eU5hbWVSZSA9IC9eW0EtWmEtel1bQS16YS16MC05XSskLztcbiAgLy8gVXNlZCBhcyBhIGhvb2sgdG8gaW52b2tlIHRoZSBicm93c2VyJ3MgZW50aXR5IHBhcnNpbmcuIDx0ZXh0YXJlYT4gaXMgdXNlZFxuICAvLyBiZWNhdXNlIGl0cyBjb250ZW50IGlzIHBhcnNlZCBmb3IgZW50aXRpZXMgYnV0IG5vdCB0YWdzLlxuICAvLyBUT0RPKGtwcmVpZCk6IFRoaXMgcmV0cmlldmFsIGlzIGEga2x1ZGdlIGFuZCBsZWFkcyB0byBzaWxlbnQgbG9zcyBvZlxuICAvLyBmdW5jdGlvbmFsaXR5IGlmIHRoZSBkb2N1bWVudCBpc24ndCBhdmFpbGFibGUuXG4gIHZhciBlbnRpdHlMb29rdXBFbGVtZW50ID1cbiAgICAgICgndW5kZWZpbmVkJyAhPT0gdHlwZW9mIHdpbmRvdyAmJiB3aW5kb3dbJ2RvY3VtZW50J10pXG4gICAgICAgICAgPyB3aW5kb3dbJ2RvY3VtZW50J10uY3JlYXRlRWxlbWVudCgndGV4dGFyZWEnKSA6IG51bGw7XG4gIC8qKlxuICAgKiBEZWNvZGVzIGFuIEhUTUwgZW50aXR5LlxuICAgKlxuICAgKiB7XFxAdXBkb2NcbiAgICogJCBsb29rdXBFbnRpdHkoJ2x0JylcbiAgICogIyAnPCdcbiAgICogJCBsb29rdXBFbnRpdHkoJ0dUJylcbiAgICogIyAnPidcbiAgICogJCBsb29rdXBFbnRpdHkoJ2FtcCcpXG4gICAqICMgJyYnXG4gICAqICQgbG9va3VwRW50aXR5KCduYnNwJylcbiAgICogIyAnXFx4QTAnXG4gICAqICQgbG9va3VwRW50aXR5KCdhcG9zJylcbiAgICogIyBcIidcIlxuICAgKiAkIGxvb2t1cEVudGl0eSgncXVvdCcpXG4gICAqICMgJ1wiJ1xuICAgKiAkIGxvb2t1cEVudGl0eSgnI3hhJylcbiAgICogIyAnXFxuJ1xuICAgKiAkIGxvb2t1cEVudGl0eSgnIzEwJylcbiAgICogIyAnXFxuJ1xuICAgKiAkIGxvb2t1cEVudGl0eSgnI3gwYScpXG4gICAqICMgJ1xcbidcbiAgICogJCBsb29rdXBFbnRpdHkoJyMwMTAnKVxuICAgKiAjICdcXG4nXG4gICAqICQgbG9va3VwRW50aXR5KCcjeDAwQScpXG4gICAqICMgJ1xcbidcbiAgICogJCBsb29rdXBFbnRpdHkoJ1BpJykgICAgICAvLyBLbm93biBmYWlsdXJlXG4gICAqICMgJ1xcdTAzQTAnXG4gICAqICQgbG9va3VwRW50aXR5KCdwaScpICAgICAgLy8gS25vd24gZmFpbHVyZVxuICAgKiAjICdcXHUwM0MwJ1xuICAgKiB9XG4gICAqXG4gICAqIEBwYXJhbSB7c3RyaW5nfSBuYW1lIHRoZSBjb250ZW50IGJldHdlZW4gdGhlICcmJyBhbmQgdGhlICc7Jy5cbiAgICogQHJldHVybiB7c3RyaW5nfSBhIHNpbmdsZSB1bmljb2RlIGNvZGUtcG9pbnQgYXMgYSBzdHJpbmcuXG4gICAqL1xuICBmdW5jdGlvbiBsb29rdXBFbnRpdHkobmFtZSkge1xuICAgIC8vIFRPRE86IGVudGl0eSBsb29rdXAgYXMgc3BlY2lmaWVkIGJ5IEhUTUw1IGFjdHVhbGx5IGRlcGVuZHMgb24gdGhlXG4gICAgLy8gcHJlc2VuY2Ugb2YgdGhlIFwiO1wiLlxuICAgIGlmIChFTlRJVElFUy5oYXNPd25Qcm9wZXJ0eShuYW1lKSkgeyByZXR1cm4gRU5USVRJRVNbbmFtZV07IH1cbiAgICB2YXIgbSA9IG5hbWUubWF0Y2goZGVjaW1hbEVzY2FwZVJlKTtcbiAgICBpZiAobSkge1xuICAgICAgcmV0dXJuIFN0cmluZy5mcm9tQ2hhckNvZGUocGFyc2VJbnQobVsxXSwgMTApKTtcbiAgICB9IGVsc2UgaWYgKCEhKG0gPSBuYW1lLm1hdGNoKGhleEVzY2FwZVJlKSkpIHtcbiAgICAgIHJldHVybiBTdHJpbmcuZnJvbUNoYXJDb2RlKHBhcnNlSW50KG1bMV0sIDE2KSk7XG4gICAgfSBlbHNlIGlmIChlbnRpdHlMb29rdXBFbGVtZW50ICYmIHNhZmVFbnRpdHlOYW1lUmUudGVzdChuYW1lKSkge1xuICAgICAgZW50aXR5TG9va3VwRWxlbWVudC5pbm5lckhUTUwgPSAnJicgKyBuYW1lICsgJzsnO1xuICAgICAgdmFyIHRleHQgPSBlbnRpdHlMb29rdXBFbGVtZW50LnRleHRDb250ZW50O1xuICAgICAgRU5USVRJRVNbbmFtZV0gPSB0ZXh0O1xuICAgICAgcmV0dXJuIHRleHQ7XG4gICAgfSBlbHNlIHtcbiAgICAgIHJldHVybiAnJicgKyBuYW1lICsgJzsnO1xuICAgIH1cbiAgfVxuXG4gIGZ1bmN0aW9uIGRlY29kZU9uZUVudGl0eShfLCBuYW1lKSB7XG4gICAgcmV0dXJuIGxvb2t1cEVudGl0eShuYW1lKTtcbiAgfVxuXG4gIHZhciBudWxSZSA9IC9cXDAvZztcbiAgZnVuY3Rpb24gc3RyaXBOVUxzKHMpIHtcbiAgICByZXR1cm4gcy5yZXBsYWNlKG51bFJlLCAnJyk7XG4gIH1cblxuICB2YXIgRU5USVRZX1JFXzEgPSAvJigjWzAtOV0rfCNbeFhdWzAtOUEtRmEtZl0rfFxcdyspOy9nO1xuICB2YXIgRU5USVRZX1JFXzIgPSAvXigjWzAtOV0rfCNbeFhdWzAtOUEtRmEtZl0rfFxcdyspOy87XG4gIC8qKlxuICAgKiBUaGUgcGxhaW4gdGV4dCBvZiBhIGNodW5rIG9mIEhUTUwgQ0RBVEEgd2hpY2ggcG9zc2libHkgY29udGFpbmluZy5cbiAgICpcbiAgICoge1xcQHVwZG9jXG4gICAqICQgdW5lc2NhcGVFbnRpdGllcygnJylcbiAgICogIyAnJ1xuICAgKiAkIHVuZXNjYXBlRW50aXRpZXMoJ2hlbGxvIFdvcmxkIScpXG4gICAqICMgJ2hlbGxvIFdvcmxkISdcbiAgICogJCB1bmVzY2FwZUVudGl0aWVzKCcxICZsdDsgMiAmYW1wOyZBTVA7IDQgJmd0OyAzJiMxMDsnKVxuICAgKiAjICcxIDwgMiAmJiA0ID4gM1xcbidcbiAgICogJCB1bmVzY2FwZUVudGl0aWVzKCcmbHQ7Jmx0IDwtIHVuZmluaXNoZWQgZW50aXR5Jmd0OycpXG4gICAqICMgJzwmbHQgPC0gdW5maW5pc2hlZCBlbnRpdHk+J1xuICAgKiAkIHVuZXNjYXBlRW50aXRpZXMoJy9mb28/YmFyPWJheiZjb3B5PXRydWUnKSAgLy8gJiBvZnRlbiB1bmVzY2FwZWQgaW4gVVJMU1xuICAgKiAjICcvZm9vP2Jhcj1iYXomY29weT10cnVlJ1xuICAgKiAkIHVuZXNjYXBlRW50aXRpZXMoJ3BpPSZwaTsmI3gzYzA7LCBQaT0mUGk7XFx1MDNBMCcpIC8vIEZJWE1FOiBrbm93biBmYWlsdXJlXG4gICAqICMgJ3BpPVxcdTAzQzBcXHUwM2MwLCBQaT1cXHUwM0EwXFx1MDNBMCdcbiAgICogfVxuICAgKlxuICAgKiBAcGFyYW0ge3N0cmluZ30gcyBhIGNodW5rIG9mIEhUTUwgQ0RBVEEuICBJdCBtdXN0IG5vdCBzdGFydCBvciBlbmQgaW5zaWRlXG4gICAqICAgICBhbiBIVE1MIGVudGl0eS5cbiAgICovXG4gIGZ1bmN0aW9uIHVuZXNjYXBlRW50aXRpZXMocykge1xuICAgIHJldHVybiBzLnJlcGxhY2UoRU5USVRZX1JFXzEsIGRlY29kZU9uZUVudGl0eSk7XG4gIH1cblxuICB2YXIgYW1wUmUgPSAvJi9nO1xuICB2YXIgbG9vc2VBbXBSZSA9IC8mKFteYS16I118Iyg/OlteMC05eF18eCg/OlteMC05YS1mXXwkKXwkKXwkKS9naTtcbiAgdmFyIGx0UmUgPSAvWzxdL2c7XG4gIHZhciBndFJlID0gLz4vZztcbiAgdmFyIHF1b3RSZSA9IC9cXFwiL2c7XG5cbiAgLyoqXG4gICAqIEVzY2FwZXMgSFRNTCBzcGVjaWFsIGNoYXJhY3RlcnMgaW4gYXR0cmlidXRlIHZhbHVlcy5cbiAgICpcbiAgICoge1xcQHVwZG9jXG4gICAqICQgZXNjYXBlQXR0cmliKCcnKVxuICAgKiAjICcnXG4gICAqICQgZXNjYXBlQXR0cmliKCdcIjw8Jj09Jj4+XCInKSAgLy8gRG8gbm90IGp1c3QgZXNjYXBlIHRoZSBmaXJzdCBvY2N1cnJlbmNlLlxuICAgKiAjICcmIzM0OyZsdDsmbHQ7JmFtcDsmIzYxOyYjNjE7JmFtcDsmZ3Q7Jmd0OyYjMzQ7J1xuICAgKiAkIGVzY2FwZUF0dHJpYignSGVsbG8gPFdvcmxkPiEnKVxuICAgKiAjICdIZWxsbyAmbHQ7V29ybGQmZ3Q7ISdcbiAgICogfVxuICAgKi9cbiAgZnVuY3Rpb24gZXNjYXBlQXR0cmliKHMpIHtcbiAgICByZXR1cm4gKCcnICsgcykucmVwbGFjZShhbXBSZSwgJyZhbXA7JykucmVwbGFjZShsdFJlLCAnJmx0OycpXG4gICAgICAgIC5yZXBsYWNlKGd0UmUsICcmZ3Q7JykucmVwbGFjZShxdW90UmUsICcmIzM0OycpO1xuICB9XG5cbiAgLyoqXG4gICAqIEVzY2FwZSBlbnRpdGllcyBpbiBSQ0RBVEEgdGhhdCBjYW4gYmUgZXNjYXBlZCB3aXRob3V0IGNoYW5naW5nIHRoZSBtZWFuaW5nLlxuICAgKiB7XFxAdXBkb2NcbiAgICogJCBub3JtYWxpemVSQ0RhdGEoJzEgPCAyICYmYW1wOyAzID4gNCAmYW1wOyYgNSAmbHQ7IDcmOCcpXG4gICAqICMgJzEgJmx0OyAyICZhbXA7JmFtcDsgMyAmZ3Q7IDQgJmFtcDsmYW1wOyA1ICZsdDsgNyZhbXA7OCdcbiAgICogfVxuICAgKi9cbiAgZnVuY3Rpb24gbm9ybWFsaXplUkNEYXRhKHJjZGF0YSkge1xuICAgIHJldHVybiByY2RhdGFcbiAgICAgICAgLnJlcGxhY2UobG9vc2VBbXBSZSwgJyZhbXA7JDEnKVxuICAgICAgICAucmVwbGFjZShsdFJlLCAnJmx0OycpXG4gICAgICAgIC5yZXBsYWNlKGd0UmUsICcmZ3Q7Jyk7XG4gIH1cblxuICAvLyBUT0RPKGZlbGl4OGEpOiB2YWxpZGF0ZSBzYW5pdGl6ZXIgcmVnZXhzIGFnYWluc3QgdGhlIEhUTUw1IGdyYW1tYXIgYXRcbiAgLy8gaHR0cDovL3d3dy53aGF0d2cub3JnL3NwZWNzL3dlYi1hcHBzL2N1cnJlbnQtd29yay9tdWx0aXBhZ2Uvc3ludGF4Lmh0bWxcbiAgLy8gaHR0cDovL3d3dy53aGF0d2cub3JnL3NwZWNzL3dlYi1hcHBzL2N1cnJlbnQtd29yay9tdWx0aXBhZ2UvcGFyc2luZy5odG1sXG4gIC8vIGh0dHA6Ly93d3cud2hhdHdnLm9yZy9zcGVjcy93ZWItYXBwcy9jdXJyZW50LXdvcmsvbXVsdGlwYWdlL3Rva2VuaXphdGlvbi5odG1sXG4gIC8vIGh0dHA6Ly93d3cud2hhdHdnLm9yZy9zcGVjcy93ZWItYXBwcy9jdXJyZW50LXdvcmsvbXVsdGlwYWdlL3RyZWUtY29uc3RydWN0aW9uLmh0bWxcblxuICAvLyBXZSBpbml0aWFsbHkgc3BsaXQgaW5wdXQgc28gdGhhdCBwb3RlbnRpYWxseSBtZWFuaW5nZnVsIGNoYXJhY3RlcnNcbiAgLy8gbGlrZSAnPCcgYW5kICc+JyBhcmUgc2VwYXJhdGUgdG9rZW5zLCB1c2luZyBhIGZhc3QgZHVtYiBwcm9jZXNzIHRoYXRcbiAgLy8gaWdub3JlcyBxdW90aW5nLiAgVGhlbiB3ZSB3YWxrIHRoYXQgdG9rZW4gc3RyZWFtLCBhbmQgd2hlbiB3ZSBzZWUgYVxuICAvLyAnPCcgdGhhdCdzIHRoZSBzdGFydCBvZiBhIHRhZywgd2UgdXNlIEFUVFJfUkUgdG8gZXh0cmFjdCB0YWdcbiAgLy8gYXR0cmlidXRlcyBmcm9tIHRoZSBuZXh0IHRva2VuLiAgVGhhdCB0b2tlbiB3aWxsIG5ldmVyIGhhdmUgYSAnPidcbiAgLy8gY2hhcmFjdGVyLiAgSG93ZXZlciwgaXQgbWlnaHQgaGF2ZSBhbiB1bmJhbGFuY2VkIHF1b3RlIGNoYXJhY3RlciwgYW5kXG4gIC8vIHdoZW4gd2Ugc2VlIHRoYXQsIHdlIGNvbWJpbmUgYWRkaXRpb25hbCB0b2tlbnMgdG8gYmFsYW5jZSB0aGUgcXVvdGUuXG5cbiAgdmFyIEFUVFJfUkUgPSBuZXcgUmVnRXhwKFxuICAgICdeXFxcXHMqJyArXG4gICAgJyhbLS46XFxcXHddKyknICsgICAgICAgICAgICAgLy8gMSA9IEF0dHJpYnV0ZSBuYW1lXG4gICAgJyg/OicgKyAoXG4gICAgICAnXFxcXHMqKD0pXFxcXHMqJyArICAgICAgICAgICAvLyAyID0gSXMgdGhlcmUgYSB2YWx1ZT9cbiAgICAgICcoJyArICggICAgICAgICAgICAgICAgICAgLy8gMyA9IEF0dHJpYnV0ZSB2YWx1ZVxuICAgICAgICAvLyBUT0RPKGZlbGl4OGEpOiBtYXliZSB1c2UgYmFja3JlZiB0byBtYXRjaCBxdW90ZXNcbiAgICAgICAgJyhcXFwiKVteXFxcIl0qKFxcXCJ8JCknICsgICAgLy8gNCwgNSA9IERvdWJsZS1xdW90ZWQgc3RyaW5nXG4gICAgICAgICd8JyArXG4gICAgICAgICcoXFwnKVteXFwnXSooXFwnfCQpJyArICAgIC8vIDYsIDcgPSBTaW5nbGUtcXVvdGVkIHN0cmluZ1xuICAgICAgICAnfCcgK1xuICAgICAgICAvLyBQb3NpdGl2ZSBsb29rYWhlYWQgdG8gcHJldmVudCBpbnRlcnByZXRhdGlvbiBvZlxuICAgICAgICAvLyA8Zm9vIGE9IGI9Yz4gYXMgPGZvbyBhPSdiPWMnPlxuICAgICAgICAvLyBUT0RPKGZlbGl4OGEpOiBtaWdodCBiZSBhYmxlIHRvIGRyb3AgdGhpcyBjYXNlXG4gICAgICAgICcoPz1bYS16XVstXFxcXHddKlxcXFxzKj0pJyArXG4gICAgICAgICd8JyArXG4gICAgICAgIC8vIFVucXVvdGVkIHZhbHVlIHRoYXQgaXNuJ3QgYW4gYXR0cmlidXRlIG5hbWVcbiAgICAgICAgLy8gKHNpbmNlIHdlIGRpZG4ndCBtYXRjaCB0aGUgcG9zaXRpdmUgbG9va2FoZWFkIGFib3ZlKVxuICAgICAgICAnW15cXFwiXFwnXFxcXHNdKicgKSArXG4gICAgICAnKScgKSArXG4gICAgJyk/JyxcbiAgICAnaScpO1xuXG4gIC8vIGZhbHNlIG9uIElFPD04LCB0cnVlIG9uIG1vc3Qgb3RoZXIgYnJvd3NlcnNcbiAgdmFyIHNwbGl0V2lsbENhcHR1cmUgPSAoJ2EsYicuc3BsaXQoLygsKS8pLmxlbmd0aCA9PT0gMyk7XG5cbiAgLy8gYml0bWFzayBmb3IgdGFncyB3aXRoIHNwZWNpYWwgcGFyc2luZywgbGlrZSA8c2NyaXB0PiBhbmQgPHRleHRhcmVhPlxuICB2YXIgRUZMQUdTX1RFWFQgPSBodG1sNC5lZmxhZ3NbJ0NEQVRBJ10gfCBodG1sNC5lZmxhZ3NbJ1JDREFUQSddO1xuXG4gIC8qKlxuICAgKiBHaXZlbiBhIFNBWC1saWtlIGV2ZW50IGhhbmRsZXIsIHByb2R1Y2UgYSBmdW5jdGlvbiB0aGF0IGZlZWRzIHRob3NlXG4gICAqIGV2ZW50cyBhbmQgYSBwYXJhbWV0ZXIgdG8gdGhlIGV2ZW50IGhhbmRsZXIuXG4gICAqXG4gICAqIFRoZSBldmVudCBoYW5kbGVyIGhhcyB0aGUgZm9ybTp7QGNvZGVcbiAgICoge1xuICAgKiAgIC8vIE5hbWUgaXMgYW4gdXBwZXItY2FzZSBIVE1MIHRhZyBuYW1lLiAgQXR0cmlicyBpcyBhbiBhcnJheSBvZlxuICAgKiAgIC8vIGFsdGVybmF0aW5nIHVwcGVyLWNhc2UgYXR0cmlidXRlIG5hbWVzLCBhbmQgYXR0cmlidXRlIHZhbHVlcy4gIFRoZVxuICAgKiAgIC8vIGF0dHJpYnMgYXJyYXkgaXMgcmV1c2VkIGJ5IHRoZSBwYXJzZXIuICBQYXJhbSBpcyB0aGUgdmFsdWUgcGFzc2VkIHRvXG4gICAqICAgLy8gdGhlIHNheFBhcnNlci5cbiAgICogICBzdGFydFRhZzogZnVuY3Rpb24gKG5hbWUsIGF0dHJpYnMsIHBhcmFtKSB7IC4uLiB9LFxuICAgKiAgIGVuZFRhZzogICBmdW5jdGlvbiAobmFtZSwgcGFyYW0pIHsgLi4uIH0sXG4gICAqICAgcGNkYXRhOiAgIGZ1bmN0aW9uICh0ZXh0LCBwYXJhbSkgeyAuLi4gfSxcbiAgICogICByY2RhdGE6ICAgZnVuY3Rpb24gKHRleHQsIHBhcmFtKSB7IC4uLiB9LFxuICAgKiAgIGNkYXRhOiAgICBmdW5jdGlvbiAodGV4dCwgcGFyYW0pIHsgLi4uIH0sXG4gICAqICAgc3RhcnREb2M6IGZ1bmN0aW9uIChwYXJhbSkgeyAuLi4gfSxcbiAgICogICBlbmREb2M6ICAgZnVuY3Rpb24gKHBhcmFtKSB7IC4uLiB9XG4gICAqIH19XG4gICAqXG4gICAqIEBwYXJhbSB7T2JqZWN0fSBoYW5kbGVyIGEgcmVjb3JkIGNvbnRhaW5pbmcgZXZlbnQgaGFuZGxlcnMuXG4gICAqIEByZXR1cm4ge2Z1bmN0aW9uKHN0cmluZywgT2JqZWN0KX0gQSBmdW5jdGlvbiB0aGF0IHRha2VzIGEgY2h1bmsgb2YgSFRNTFxuICAgKiAgICAgYW5kIGEgcGFyYW1ldGVyLiAgVGhlIHBhcmFtZXRlciBpcyBwYXNzZWQgb24gdG8gdGhlIGhhbmRsZXIgbWV0aG9kcy5cbiAgICovXG4gIGZ1bmN0aW9uIG1ha2VTYXhQYXJzZXIoaGFuZGxlcikge1xuICAgIC8vIEFjY2VwdCBxdW90ZWQgb3IgdW5xdW90ZWQga2V5cyAoQ2xvc3VyZSBjb21wYXQpXG4gICAgdmFyIGhjb3B5ID0ge1xuICAgICAgY2RhdGE6IGhhbmRsZXIuY2RhdGEgfHwgaGFuZGxlclsnY2RhdGEnXSxcbiAgICAgIGNvbW1lbnQ6IGhhbmRsZXIuY29tbWVudCB8fCBoYW5kbGVyWydjb21tZW50J10sXG4gICAgICBlbmREb2M6IGhhbmRsZXIuZW5kRG9jIHx8IGhhbmRsZXJbJ2VuZERvYyddLFxuICAgICAgZW5kVGFnOiBoYW5kbGVyLmVuZFRhZyB8fCBoYW5kbGVyWydlbmRUYWcnXSxcbiAgICAgIHBjZGF0YTogaGFuZGxlci5wY2RhdGEgfHwgaGFuZGxlclsncGNkYXRhJ10sXG4gICAgICByY2RhdGE6IGhhbmRsZXIucmNkYXRhIHx8IGhhbmRsZXJbJ3JjZGF0YSddLFxuICAgICAgc3RhcnREb2M6IGhhbmRsZXIuc3RhcnREb2MgfHwgaGFuZGxlclsnc3RhcnREb2MnXSxcbiAgICAgIHN0YXJ0VGFnOiBoYW5kbGVyLnN0YXJ0VGFnIHx8IGhhbmRsZXJbJ3N0YXJ0VGFnJ11cbiAgICB9O1xuICAgIHJldHVybiBmdW5jdGlvbihodG1sVGV4dCwgcGFyYW0pIHtcbiAgICAgIHJldHVybiBwYXJzZShodG1sVGV4dCwgaGNvcHksIHBhcmFtKTtcbiAgICB9O1xuICB9XG5cbiAgLy8gUGFyc2luZyBzdHJhdGVneSBpcyB0byBzcGxpdCBpbnB1dCBpbnRvIHBhcnRzIHRoYXQgbWlnaHQgYmUgbGV4aWNhbGx5XG4gIC8vIG1lYW5pbmdmdWwgKGV2ZXJ5IFwiPlwiIGJlY29tZXMgYSBzZXBhcmF0ZSBwYXJ0KSwgYW5kIHRoZW4gcmVjb21iaW5lXG4gIC8vIHBhcnRzIGlmIHdlIGRpc2NvdmVyIHRoZXkncmUgaW4gYSBkaWZmZXJlbnQgY29udGV4dC5cblxuICAvLyBUT0RPKGZlbGl4OGEpOiBTaWduaWZpY2FudCBwZXJmb3JtYW5jZSByZWdyZXNzaW9ucyBmcm9tIC1sZWdhY3ksXG4gIC8vIHRlc3RlZCBvblxuICAvLyAgICBDaHJvbWUgMTguMFxuICAvLyAgICBGaXJlZm94IDExLjBcbiAgLy8gICAgSUUgNiwgNywgOCwgOVxuICAvLyAgICBPcGVyYSAxMS42MVxuICAvLyAgICBTYWZhcmkgNS4xLjNcbiAgLy8gTWFueSBvZiB0aGVzZSBhcmUgdW51c3VhbCBwYXR0ZXJucyB0aGF0IGFyZSBsaW5lYXJseSBzbG93ZXIgYW5kIHN0aWxsXG4gIC8vIHByZXR0eSBmYXN0IChlZyAxbXMgdG8gNW1zKSwgc28gbm90IG5lY2Vzc2FyaWx5IHdvcnRoIGZpeGluZy5cblxuICAvLyBUT0RPKGZlbGl4OGEpOiBcIjxzY3JpcHQ+ICYmICYmICYmIC4uLiA8XFwvc2NyaXB0PlwiIGlzIHNsb3dlciBvbiBhbGxcbiAgLy8gYnJvd3NlcnMuICBUaGUgaG90c3BvdCBpcyBodG1sU3BsaXQuXG5cbiAgLy8gVE9ETyhmZWxpeDhhKTogXCI8cCB0aXRsZT0nPj4+Pi4uLic+PFxcL3A+XCIgaXMgc2xvd2VyIG9uIGFsbCBicm93c2Vycy5cbiAgLy8gVGhpcyBpcyBwYXJ0bHkgaHRtbFNwbGl0LCBidXQgdGhlIGhvdHNwb3QgaXMgcGFyc2VUYWdBbmRBdHRycy5cblxuICAvLyBUT0RPKGZlbGl4OGEpOiBcIjxhPjxcXC9hPjxhPjxcXC9hPi4uLlwiIGlzIHNsb3dlciBvbiBJRTkuXG4gIC8vIFwiPGE+MTxcXC9hPjxhPjE8XFwvYT4uLi5cIiBpcyBmYXN0ZXIsIFwiPGE+PFxcL2E+MjxhPjxcXC9hPjIuLi5cIiBpcyBmYXN0ZXIuXG5cbiAgLy8gVE9ETyhmZWxpeDhhKTogXCI8cDxwPHAuLi5cIiBpcyBzbG93ZXIgb24gSUVbNi04XVxuXG4gIHZhciBjb250aW51YXRpb25NYXJrZXIgPSB7fTtcbiAgZnVuY3Rpb24gcGFyc2UoaHRtbFRleHQsIGhhbmRsZXIsIHBhcmFtKSB7XG4gICAgdmFyIG0sIHAsIHRhZ05hbWU7XG4gICAgdmFyIHBhcnRzID0gaHRtbFNwbGl0KGh0bWxUZXh0KTtcbiAgICB2YXIgc3RhdGUgPSB7XG4gICAgICBub01vcmVHVDogZmFsc2UsXG4gICAgICBub01vcmVFbmRDb21tZW50czogZmFsc2VcbiAgICB9O1xuICAgIHBhcnNlQ1BTKGhhbmRsZXIsIHBhcnRzLCAwLCBzdGF0ZSwgcGFyYW0pO1xuICB9XG5cbiAgZnVuY3Rpb24gY29udGludWF0aW9uTWFrZXIoaCwgcGFydHMsIGluaXRpYWwsIHN0YXRlLCBwYXJhbSkge1xuICAgIHJldHVybiBmdW5jdGlvbiAoKSB7XG4gICAgICBwYXJzZUNQUyhoLCBwYXJ0cywgaW5pdGlhbCwgc3RhdGUsIHBhcmFtKTtcbiAgICB9O1xuICB9XG5cbiAgZnVuY3Rpb24gcGFyc2VDUFMoaCwgcGFydHMsIGluaXRpYWwsIHN0YXRlLCBwYXJhbSkge1xuICAgIHRyeSB7XG4gICAgICBpZiAoaC5zdGFydERvYyAmJiBpbml0aWFsID09IDApIHsgaC5zdGFydERvYyhwYXJhbSk7IH1cbiAgICAgIHZhciBtLCBwLCB0YWdOYW1lO1xuICAgICAgZm9yICh2YXIgcG9zID0gaW5pdGlhbCwgZW5kID0gcGFydHMubGVuZ3RoOyBwb3MgPCBlbmQ7KSB7XG4gICAgICAgIHZhciBjdXJyZW50ID0gcGFydHNbcG9zKytdO1xuICAgICAgICB2YXIgbmV4dCA9IHBhcnRzW3Bvc107XG4gICAgICAgIHN3aXRjaCAoY3VycmVudCkge1xuICAgICAgICBjYXNlICcmJzpcbiAgICAgICAgICBpZiAoRU5USVRZX1JFXzIudGVzdChuZXh0KSkge1xuICAgICAgICAgICAgaWYgKGgucGNkYXRhKSB7XG4gICAgICAgICAgICAgIGgucGNkYXRhKCcmJyArIG5leHQsIHBhcmFtLCBjb250aW51YXRpb25NYXJrZXIsXG4gICAgICAgICAgICAgICAgY29udGludWF0aW9uTWFrZXIoaCwgcGFydHMsIHBvcywgc3RhdGUsIHBhcmFtKSk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBwb3MrKztcbiAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgaWYgKGgucGNkYXRhKSB7IGgucGNkYXRhKFwiJmFtcDtcIiwgcGFyYW0sIGNvbnRpbnVhdGlvbk1hcmtlcixcbiAgICAgICAgICAgICAgICBjb250aW51YXRpb25NYWtlcihoLCBwYXJ0cywgcG9zLCBzdGF0ZSwgcGFyYW0pKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICB9XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIGNhc2UgJzxcXC8nOlxuICAgICAgICAgIGlmIChtID0gL14oWy1cXHc6XSspW15cXCdcXFwiXSovLmV4ZWMobmV4dCkpIHtcbiAgICAgICAgICAgIGlmIChtWzBdLmxlbmd0aCA9PT0gbmV4dC5sZW5ndGggJiYgcGFydHNbcG9zICsgMV0gPT09ICc+Jykge1xuICAgICAgICAgICAgICAvLyBmYXN0IGNhc2UsIG5vIGF0dHJpYnV0ZSBwYXJzaW5nIG5lZWRlZFxuICAgICAgICAgICAgICBwb3MgKz0gMjtcbiAgICAgICAgICAgICAgdGFnTmFtZSA9IG1bMV0udG9Mb3dlckNhc2UoKTtcbiAgICAgICAgICAgICAgaWYgKGguZW5kVGFnKSB7XG4gICAgICAgICAgICAgICAgaC5lbmRUYWcodGFnTmFtZSwgcGFyYW0sIGNvbnRpbnVhdGlvbk1hcmtlcixcbiAgICAgICAgICAgICAgICAgIGNvbnRpbnVhdGlvbk1ha2VyKGgsIHBhcnRzLCBwb3MsIHN0YXRlLCBwYXJhbSkpO1xuICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAvLyBzbG93IGNhc2UsIG5lZWQgdG8gcGFyc2UgYXR0cmlidXRlc1xuICAgICAgICAgICAgICAvLyBUT0RPKGZlbGl4OGEpOiBkbyB3ZSByZWFsbHkgY2FyZSBhYm91dCBtaXNwYXJzaW5nIHRoaXM/XG4gICAgICAgICAgICAgIHBvcyA9IHBhcnNlRW5kVGFnKFxuICAgICAgICAgICAgICAgIHBhcnRzLCBwb3MsIGgsIHBhcmFtLCBjb250aW51YXRpb25NYXJrZXIsIHN0YXRlKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgaWYgKGgucGNkYXRhKSB7XG4gICAgICAgICAgICAgIGgucGNkYXRhKCcmbHQ7LycsIHBhcmFtLCBjb250aW51YXRpb25NYXJrZXIsXG4gICAgICAgICAgICAgICAgY29udGludWF0aW9uTWFrZXIoaCwgcGFydHMsIHBvcywgc3RhdGUsIHBhcmFtKSk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgfVxuICAgICAgICAgIGJyZWFrO1xuICAgICAgICBjYXNlICc8JzpcbiAgICAgICAgICBpZiAobSA9IC9eKFstXFx3Ol0rKVxccypcXC8/Ly5leGVjKG5leHQpKSB7XG4gICAgICAgICAgICBpZiAobVswXS5sZW5ndGggPT09IG5leHQubGVuZ3RoICYmIHBhcnRzW3BvcyArIDFdID09PSAnPicpIHtcbiAgICAgICAgICAgICAgLy8gZmFzdCBjYXNlLCBubyBhdHRyaWJ1dGUgcGFyc2luZyBuZWVkZWRcbiAgICAgICAgICAgICAgcG9zICs9IDI7XG4gICAgICAgICAgICAgIHRhZ05hbWUgPSBtWzFdLnRvTG93ZXJDYXNlKCk7XG4gICAgICAgICAgICAgIGlmIChoLnN0YXJ0VGFnKSB7XG4gICAgICAgICAgICAgICAgaC5zdGFydFRhZyh0YWdOYW1lLCBbXSwgcGFyYW0sIGNvbnRpbnVhdGlvbk1hcmtlcixcbiAgICAgICAgICAgICAgICAgIGNvbnRpbnVhdGlvbk1ha2VyKGgsIHBhcnRzLCBwb3MsIHN0YXRlLCBwYXJhbSkpO1xuICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgIC8vIHRhZ3MgbGlrZSA8c2NyaXB0PiBhbmQgPHRleHRhcmVhPiBoYXZlIHNwZWNpYWwgcGFyc2luZ1xuICAgICAgICAgICAgICB2YXIgZWZsYWdzID0gaHRtbDQuRUxFTUVOVFNbdGFnTmFtZV07XG4gICAgICAgICAgICAgIGlmIChlZmxhZ3MgJiBFRkxBR1NfVEVYVCkge1xuICAgICAgICAgICAgICAgIHZhciB0YWcgPSB7IG5hbWU6IHRhZ05hbWUsIG5leHQ6IHBvcywgZWZsYWdzOiBlZmxhZ3MgfTtcbiAgICAgICAgICAgICAgICBwb3MgPSBwYXJzZVRleHQoXG4gICAgICAgICAgICAgICAgICBwYXJ0cywgdGFnLCBoLCBwYXJhbSwgY29udGludWF0aW9uTWFya2VyLCBzdGF0ZSk7XG4gICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgIC8vIHNsb3cgY2FzZSwgbmVlZCB0byBwYXJzZSBhdHRyaWJ1dGVzXG4gICAgICAgICAgICAgIHBvcyA9IHBhcnNlU3RhcnRUYWcoXG4gICAgICAgICAgICAgICAgcGFydHMsIHBvcywgaCwgcGFyYW0sIGNvbnRpbnVhdGlvbk1hcmtlciwgc3RhdGUpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICBpZiAoaC5wY2RhdGEpIHtcbiAgICAgICAgICAgICAgaC5wY2RhdGEoJyZsdDsnLCBwYXJhbSwgY29udGludWF0aW9uTWFya2VyLFxuICAgICAgICAgICAgICAgIGNvbnRpbnVhdGlvbk1ha2VyKGgsIHBhcnRzLCBwb3MsIHN0YXRlLCBwYXJhbSkpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgIH1cbiAgICAgICAgICBicmVhaztcbiAgICAgICAgY2FzZSAnPFxcIS0tJzpcbiAgICAgICAgICAvLyBUaGUgcGF0aG9sb2dpY2FsIGNhc2UgaXMgbiBjb3BpZXMgb2YgJzxcXCEtLScgd2l0aG91dCAnLS0+JywgYW5kXG4gICAgICAgICAgLy8gcmVwZWF0ZWQgZmFpbHVyZSB0byBmaW5kICctLT4nIGlzIHF1YWRyYXRpYy4gIFdlIGF2b2lkIHRoYXQgYnlcbiAgICAgICAgICAvLyByZW1lbWJlcmluZyB3aGVuIHNlYXJjaCBmb3IgJy0tPicgZmFpbHMuXG4gICAgICAgICAgaWYgKCFzdGF0ZS5ub01vcmVFbmRDb21tZW50cykge1xuICAgICAgICAgICAgLy8gQSBjb21tZW50IDxcXCEtLXgtLT4gaXMgc3BsaXQgaW50byB0aHJlZSB0b2tlbnM6XG4gICAgICAgICAgICAvLyAgICc8XFwhLS0nLCAneC0tJywgJz4nXG4gICAgICAgICAgICAvLyBXZSB3YW50IHRvIGZpbmQgdGhlIG5leHQgJz4nIHRva2VuIHRoYXQgaGFzIGEgcHJlY2VkaW5nICctLScuXG4gICAgICAgICAgICAvLyBwb3MgaXMgYXQgdGhlICd4LS0nLlxuICAgICAgICAgICAgZm9yIChwID0gcG9zICsgMTsgcCA8IGVuZDsgcCsrKSB7XG4gICAgICAgICAgICAgIGlmIChwYXJ0c1twXSA9PT0gJz4nICYmIC8tLSQvLnRlc3QocGFydHNbcCAtIDFdKSkgeyBicmVhazsgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWYgKHAgPCBlbmQpIHtcbiAgICAgICAgICAgICAgaWYgKGguY29tbWVudCkge1xuICAgICAgICAgICAgICAgIHZhciBjb21tZW50ID0gcGFydHMuc2xpY2UocG9zLCBwKS5qb2luKCcnKTtcbiAgICAgICAgICAgICAgICBoLmNvbW1lbnQoXG4gICAgICAgICAgICAgICAgICBjb21tZW50LnN1YnN0cigwLCBjb21tZW50Lmxlbmd0aCAtIDIpLCBwYXJhbSxcbiAgICAgICAgICAgICAgICAgIGNvbnRpbnVhdGlvbk1hcmtlcixcbiAgICAgICAgICAgICAgICAgIGNvbnRpbnVhdGlvbk1ha2VyKGgsIHBhcnRzLCBwICsgMSwgc3RhdGUsIHBhcmFtKSk7XG4gICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgcG9zID0gcCArIDE7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICBzdGF0ZS5ub01vcmVFbmRDb21tZW50cyA9IHRydWU7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgfVxuICAgICAgICAgIGlmIChzdGF0ZS5ub01vcmVFbmRDb21tZW50cykge1xuICAgICAgICAgICAgaWYgKGgucGNkYXRhKSB7XG4gICAgICAgICAgICAgIGgucGNkYXRhKCcmbHQ7IS0tJywgcGFyYW0sIGNvbnRpbnVhdGlvbk1hcmtlcixcbiAgICAgICAgICAgICAgICBjb250aW51YXRpb25NYWtlcihoLCBwYXJ0cywgcG9zLCBzdGF0ZSwgcGFyYW0pKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICB9XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIGNhc2UgJzxcXCEnOlxuICAgICAgICAgIGlmICghL15cXHcvLnRlc3QobmV4dCkpIHtcbiAgICAgICAgICAgIGlmIChoLnBjZGF0YSkge1xuICAgICAgICAgICAgICBoLnBjZGF0YSgnJmx0OyEnLCBwYXJhbSwgY29udGludWF0aW9uTWFya2VyLFxuICAgICAgICAgICAgICAgIGNvbnRpbnVhdGlvbk1ha2VyKGgsIHBhcnRzLCBwb3MsIHN0YXRlLCBwYXJhbSkpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAvLyBzaW1pbGFyIHRvIG5vTW9yZUVuZENvbW1lbnQgbG9naWNcbiAgICAgICAgICAgIGlmICghc3RhdGUubm9Nb3JlR1QpIHtcbiAgICAgICAgICAgICAgZm9yIChwID0gcG9zICsgMTsgcCA8IGVuZDsgcCsrKSB7XG4gICAgICAgICAgICAgICAgaWYgKHBhcnRzW3BdID09PSAnPicpIHsgYnJlYWs7IH1cbiAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICBpZiAocCA8IGVuZCkge1xuICAgICAgICAgICAgICAgIHBvcyA9IHAgKyAxO1xuICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIHN0YXRlLm5vTW9yZUdUID0gdHJ1ZTtcbiAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWYgKHN0YXRlLm5vTW9yZUdUKSB7XG4gICAgICAgICAgICAgIGlmIChoLnBjZGF0YSkge1xuICAgICAgICAgICAgICAgIGgucGNkYXRhKCcmbHQ7IScsIHBhcmFtLCBjb250aW51YXRpb25NYXJrZXIsXG4gICAgICAgICAgICAgICAgICBjb250aW51YXRpb25NYWtlcihoLCBwYXJ0cywgcG9zLCBzdGF0ZSwgcGFyYW0pKTtcbiAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgIH1cbiAgICAgICAgICBicmVhaztcbiAgICAgICAgY2FzZSAnPD8nOlxuICAgICAgICAgIC8vIHNpbWlsYXIgdG8gbm9Nb3JlRW5kQ29tbWVudCBsb2dpY1xuICAgICAgICAgIGlmICghc3RhdGUubm9Nb3JlR1QpIHtcbiAgICAgICAgICAgIGZvciAocCA9IHBvcyArIDE7IHAgPCBlbmQ7IHArKykge1xuICAgICAgICAgICAgICBpZiAocGFydHNbcF0gPT09ICc+JykgeyBicmVhazsgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgaWYgKHAgPCBlbmQpIHtcbiAgICAgICAgICAgICAgcG9zID0gcCArIDE7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICBzdGF0ZS5ub01vcmVHVCA9IHRydWU7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgfVxuICAgICAgICAgIGlmIChzdGF0ZS5ub01vcmVHVCkge1xuICAgICAgICAgICAgaWYgKGgucGNkYXRhKSB7XG4gICAgICAgICAgICAgIGgucGNkYXRhKCcmbHQ7PycsIHBhcmFtLCBjb250aW51YXRpb25NYXJrZXIsXG4gICAgICAgICAgICAgICAgY29udGludWF0aW9uTWFrZXIoaCwgcGFydHMsIHBvcywgc3RhdGUsIHBhcmFtKSk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgfVxuICAgICAgICAgIGJyZWFrO1xuICAgICAgICBjYXNlICc+JzpcbiAgICAgICAgICBpZiAoaC5wY2RhdGEpIHtcbiAgICAgICAgICAgIGgucGNkYXRhKFwiJmd0O1wiLCBwYXJhbSwgY29udGludWF0aW9uTWFya2VyLFxuICAgICAgICAgICAgICBjb250aW51YXRpb25NYWtlcihoLCBwYXJ0cywgcG9zLCBzdGF0ZSwgcGFyYW0pKTtcbiAgICAgICAgICB9XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIGNhc2UgJyc6XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIGRlZmF1bHQ6XG4gICAgICAgICAgaWYgKGgucGNkYXRhKSB7XG4gICAgICAgICAgICBoLnBjZGF0YShjdXJyZW50LCBwYXJhbSwgY29udGludWF0aW9uTWFya2VyLFxuICAgICAgICAgICAgICBjb250aW51YXRpb25NYWtlcihoLCBwYXJ0cywgcG9zLCBzdGF0ZSwgcGFyYW0pKTtcbiAgICAgICAgICB9XG4gICAgICAgICAgYnJlYWs7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICAgIGlmIChoLmVuZERvYykgeyBoLmVuZERvYyhwYXJhbSk7IH1cbiAgICB9IGNhdGNoIChlKSB7XG4gICAgICBpZiAoZSAhPT0gY29udGludWF0aW9uTWFya2VyKSB7IHRocm93IGU7IH1cbiAgICB9XG4gIH1cblxuICAvLyBTcGxpdCBzdHIgaW50byBwYXJ0cyBmb3IgdGhlIGh0bWwgcGFyc2VyLlxuICBmdW5jdGlvbiBodG1sU3BsaXQoc3RyKSB7XG4gICAgLy8gY2FuJ3QgaG9pc3QgdGhpcyBvdXQgb2YgdGhlIGZ1bmN0aW9uIGJlY2F1c2Ugb2YgdGhlIHJlLmV4ZWMgbG9vcC5cbiAgICB2YXIgcmUgPSAvKDxcXC98PFxcIS0tfDxbIT9dfFsmPD5dKS9nO1xuICAgIHN0ciArPSAnJztcbiAgICBpZiAoc3BsaXRXaWxsQ2FwdHVyZSkge1xuICAgICAgcmV0dXJuIHN0ci5zcGxpdChyZSk7XG4gICAgfSBlbHNlIHtcbiAgICAgIHZhciBwYXJ0cyA9IFtdO1xuICAgICAgdmFyIGxhc3RQb3MgPSAwO1xuICAgICAgdmFyIG07XG4gICAgICB3aGlsZSAoKG0gPSByZS5leGVjKHN0cikpICE9PSBudWxsKSB7XG4gICAgICAgIHBhcnRzLnB1c2goc3RyLnN1YnN0cmluZyhsYXN0UG9zLCBtLmluZGV4KSk7XG4gICAgICAgIHBhcnRzLnB1c2gobVswXSk7XG4gICAgICAgIGxhc3RQb3MgPSBtLmluZGV4ICsgbVswXS5sZW5ndGg7XG4gICAgICB9XG4gICAgICBwYXJ0cy5wdXNoKHN0ci5zdWJzdHJpbmcobGFzdFBvcykpO1xuICAgICAgcmV0dXJuIHBhcnRzO1xuICAgIH1cbiAgfVxuXG4gIGZ1bmN0aW9uIHBhcnNlRW5kVGFnKHBhcnRzLCBwb3MsIGgsIHBhcmFtLCBjb250aW51YXRpb25NYXJrZXIsIHN0YXRlKSB7XG4gICAgdmFyIHRhZyA9IHBhcnNlVGFnQW5kQXR0cnMocGFydHMsIHBvcyk7XG4gICAgLy8gZHJvcCB1bmNsb3NlZCB0YWdzXG4gICAgaWYgKCF0YWcpIHsgcmV0dXJuIHBhcnRzLmxlbmd0aDsgfVxuICAgIGlmIChoLmVuZFRhZykge1xuICAgICAgaC5lbmRUYWcodGFnLm5hbWUsIHBhcmFtLCBjb250aW51YXRpb25NYXJrZXIsXG4gICAgICAgIGNvbnRpbnVhdGlvbk1ha2VyKGgsIHBhcnRzLCBwb3MsIHN0YXRlLCBwYXJhbSkpO1xuICAgIH1cbiAgICByZXR1cm4gdGFnLm5leHQ7XG4gIH1cblxuICBmdW5jdGlvbiBwYXJzZVN0YXJ0VGFnKHBhcnRzLCBwb3MsIGgsIHBhcmFtLCBjb250aW51YXRpb25NYXJrZXIsIHN0YXRlKSB7XG4gICAgdmFyIHRhZyA9IHBhcnNlVGFnQW5kQXR0cnMocGFydHMsIHBvcyk7XG4gICAgLy8gZHJvcCB1bmNsb3NlZCB0YWdzXG4gICAgaWYgKCF0YWcpIHsgcmV0dXJuIHBhcnRzLmxlbmd0aDsgfVxuICAgIGlmIChoLnN0YXJ0VGFnKSB7XG4gICAgICBoLnN0YXJ0VGFnKHRhZy5uYW1lLCB0YWcuYXR0cnMsIHBhcmFtLCBjb250aW51YXRpb25NYXJrZXIsXG4gICAgICAgIGNvbnRpbnVhdGlvbk1ha2VyKGgsIHBhcnRzLCB0YWcubmV4dCwgc3RhdGUsIHBhcmFtKSk7XG4gICAgfVxuICAgIC8vIHRhZ3MgbGlrZSA8c2NyaXB0PiBhbmQgPHRleHRhcmVhPiBoYXZlIHNwZWNpYWwgcGFyc2luZ1xuICAgIGlmICh0YWcuZWZsYWdzICYgRUZMQUdTX1RFWFQpIHtcbiAgICAgIHJldHVybiBwYXJzZVRleHQocGFydHMsIHRhZywgaCwgcGFyYW0sIGNvbnRpbnVhdGlvbk1hcmtlciwgc3RhdGUpO1xuICAgIH0gZWxzZSB7XG4gICAgICByZXR1cm4gdGFnLm5leHQ7XG4gICAgfVxuICB9XG5cbiAgdmFyIGVuZFRhZ1JlID0ge307XG5cbiAgLy8gVGFncyBsaWtlIDxzY3JpcHQ+IGFuZCA8dGV4dGFyZWE+IGFyZSBmbGFnZ2VkIGFzIENEQVRBIG9yIFJDREFUQSxcbiAgLy8gd2hpY2ggbWVhbnMgZXZlcnl0aGluZyBpcyB0ZXh0IHVudGlsIHdlIHNlZSB0aGUgY29ycmVjdCBjbG9zaW5nIHRhZy5cbiAgZnVuY3Rpb24gcGFyc2VUZXh0KHBhcnRzLCB0YWcsIGgsIHBhcmFtLCBjb250aW51YXRpb25NYXJrZXIsIHN0YXRlKSB7XG4gICAgdmFyIGVuZCA9IHBhcnRzLmxlbmd0aDtcbiAgICBpZiAoIWVuZFRhZ1JlLmhhc093blByb3BlcnR5KHRhZy5uYW1lKSkge1xuICAgICAgZW5kVGFnUmVbdGFnLm5hbWVdID0gbmV3IFJlZ0V4cCgnXicgKyB0YWcubmFtZSArICcoPzpbXFxcXHNcXFxcL118JCknLCAnaScpO1xuICAgIH1cbiAgICB2YXIgcmUgPSBlbmRUYWdSZVt0YWcubmFtZV07XG4gICAgdmFyIGZpcnN0ID0gdGFnLm5leHQ7XG4gICAgdmFyIHAgPSB0YWcubmV4dCArIDE7XG4gICAgZm9yICg7IHAgPCBlbmQ7IHArKykge1xuICAgICAgaWYgKHBhcnRzW3AgLSAxXSA9PT0gJzxcXC8nICYmIHJlLnRlc3QocGFydHNbcF0pKSB7IGJyZWFrOyB9XG4gICAgfVxuICAgIGlmIChwIDwgZW5kKSB7IHAgLT0gMTsgfVxuICAgIHZhciBidWYgPSBwYXJ0cy5zbGljZShmaXJzdCwgcCkuam9pbignJyk7XG4gICAgaWYgKHRhZy5lZmxhZ3MgJiBodG1sNC5lZmxhZ3NbJ0NEQVRBJ10pIHtcbiAgICAgIGlmIChoLmNkYXRhKSB7XG4gICAgICAgIGguY2RhdGEoYnVmLCBwYXJhbSwgY29udGludWF0aW9uTWFya2VyLFxuICAgICAgICAgIGNvbnRpbnVhdGlvbk1ha2VyKGgsIHBhcnRzLCBwLCBzdGF0ZSwgcGFyYW0pKTtcbiAgICAgIH1cbiAgICB9IGVsc2UgaWYgKHRhZy5lZmxhZ3MgJiBodG1sNC5lZmxhZ3NbJ1JDREFUQSddKSB7XG4gICAgICBpZiAoaC5yY2RhdGEpIHtcbiAgICAgICAgaC5yY2RhdGEobm9ybWFsaXplUkNEYXRhKGJ1ZiksIHBhcmFtLCBjb250aW51YXRpb25NYXJrZXIsXG4gICAgICAgICAgY29udGludWF0aW9uTWFrZXIoaCwgcGFydHMsIHAsIHN0YXRlLCBwYXJhbSkpO1xuICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICB0aHJvdyBuZXcgRXJyb3IoJ2J1ZycpO1xuICAgIH1cbiAgICByZXR1cm4gcDtcbiAgfVxuXG4gIC8vIGF0IHRoaXMgcG9pbnQsIHBhcnRzW3Bvcy0xXSBpcyBlaXRoZXIgXCI8XCIgb3IgXCI8XFwvXCIuXG4gIGZ1bmN0aW9uIHBhcnNlVGFnQW5kQXR0cnMocGFydHMsIHBvcykge1xuICAgIHZhciBtID0gL14oWy1cXHc6XSspLy5leGVjKHBhcnRzW3Bvc10pO1xuICAgIHZhciB0YWcgPSB7fTtcbiAgICB0YWcubmFtZSA9IG1bMV0udG9Mb3dlckNhc2UoKTtcbiAgICB0YWcuZWZsYWdzID0gaHRtbDQuRUxFTUVOVFNbdGFnLm5hbWVdO1xuICAgIHZhciBidWYgPSBwYXJ0c1twb3NdLnN1YnN0cihtWzBdLmxlbmd0aCk7XG4gICAgLy8gRmluZCB0aGUgbmV4dCAnPicuICBXZSBvcHRpbWlzdGljYWxseSBhc3N1bWUgdGhpcyAnPicgaXMgbm90IGluIGFcbiAgICAvLyBxdW90ZWQgY29udGV4dCwgYW5kIGZ1cnRoZXIgZG93biB3ZSBmaXggdGhpbmdzIHVwIGlmIGl0IHR1cm5zIG91dCB0b1xuICAgIC8vIGJlIHF1b3RlZC5cbiAgICB2YXIgcCA9IHBvcyArIDE7XG4gICAgdmFyIGVuZCA9IHBhcnRzLmxlbmd0aDtcbiAgICBmb3IgKDsgcCA8IGVuZDsgcCsrKSB7XG4gICAgICBpZiAocGFydHNbcF0gPT09ICc+JykgeyBicmVhazsgfVxuICAgICAgYnVmICs9IHBhcnRzW3BdO1xuICAgIH1cbiAgICBpZiAoZW5kIDw9IHApIHsgcmV0dXJuIHZvaWQgMDsgfVxuICAgIHZhciBhdHRycyA9IFtdO1xuICAgIHdoaWxlIChidWYgIT09ICcnKSB7XG4gICAgICBtID0gQVRUUl9SRS5leGVjKGJ1Zik7XG4gICAgICBpZiAoIW0pIHtcbiAgICAgICAgLy8gTm8gYXR0cmlidXRlIGZvdW5kOiBza2lwIGdhcmJhZ2VcbiAgICAgICAgYnVmID0gYnVmLnJlcGxhY2UoL15bXFxzXFxTXVteYS16XFxzXSovLCAnJyk7XG5cbiAgICAgIH0gZWxzZSBpZiAoKG1bNF0gJiYgIW1bNV0pIHx8IChtWzZdICYmICFtWzddKSkge1xuICAgICAgICAvLyBVbnRlcm1pbmF0ZWQgcXVvdGU6IHNsdXJwIHRvIHRoZSBuZXh0IHVucXVvdGVkICc+J1xuICAgICAgICB2YXIgcXVvdGUgPSBtWzRdIHx8IG1bNl07XG4gICAgICAgIHZhciBzYXdRdW90ZSA9IGZhbHNlO1xuICAgICAgICB2YXIgYWJ1ZiA9IFtidWYsIHBhcnRzW3ArK11dO1xuICAgICAgICBmb3IgKDsgcCA8IGVuZDsgcCsrKSB7XG4gICAgICAgICAgaWYgKHNhd1F1b3RlKSB7XG4gICAgICAgICAgICBpZiAocGFydHNbcF0gPT09ICc+JykgeyBicmVhazsgfVxuICAgICAgICAgIH0gZWxzZSBpZiAoMCA8PSBwYXJ0c1twXS5pbmRleE9mKHF1b3RlKSkge1xuICAgICAgICAgICAgc2F3UXVvdGUgPSB0cnVlO1xuICAgICAgICAgIH1cbiAgICAgICAgICBhYnVmLnB1c2gocGFydHNbcF0pO1xuICAgICAgICB9XG4gICAgICAgIC8vIFNsdXJwIGZhaWxlZDogbG9zZSB0aGUgZ2FyYmFnZVxuICAgICAgICBpZiAoZW5kIDw9IHApIHsgYnJlYWs7IH1cbiAgICAgICAgLy8gT3RoZXJ3aXNlIHJldHJ5IGF0dHJpYnV0ZSBwYXJzaW5nXG4gICAgICAgIGJ1ZiA9IGFidWYuam9pbignJyk7XG4gICAgICAgIGNvbnRpbnVlO1xuXG4gICAgICB9IGVsc2Uge1xuICAgICAgICAvLyBXZSBoYXZlIGFuIGF0dHJpYnV0ZVxuICAgICAgICB2YXIgYU5hbWUgPSBtWzFdLnRvTG93ZXJDYXNlKCk7XG4gICAgICAgIHZhciBhVmFsdWUgPSBtWzJdID8gZGVjb2RlVmFsdWUobVszXSkgOiAnJztcbiAgICAgICAgYXR0cnMucHVzaChhTmFtZSwgYVZhbHVlKTtcbiAgICAgICAgYnVmID0gYnVmLnN1YnN0cihtWzBdLmxlbmd0aCk7XG4gICAgICB9XG4gICAgfVxuICAgIHRhZy5hdHRycyA9IGF0dHJzO1xuICAgIHRhZy5uZXh0ID0gcCArIDE7XG4gICAgcmV0dXJuIHRhZztcbiAgfVxuXG4gIGZ1bmN0aW9uIGRlY29kZVZhbHVlKHYpIHtcbiAgICB2YXIgcSA9IHYuY2hhckNvZGVBdCgwKTtcbiAgICBpZiAocSA9PT0gMHgyMiB8fCBxID09PSAweDI3KSB7IC8vIFwiIG9yICdcbiAgICAgIHYgPSB2LnN1YnN0cigxLCB2Lmxlbmd0aCAtIDIpO1xuICAgIH1cbiAgICByZXR1cm4gdW5lc2NhcGVFbnRpdGllcyhzdHJpcE5VTHModikpO1xuICB9XG5cbiAgLyoqXG4gICAqIFJldHVybnMgYSBmdW5jdGlvbiB0aGF0IHN0cmlwcyB1bnNhZmUgdGFncyBhbmQgYXR0cmlidXRlcyBmcm9tIGh0bWwuXG4gICAqIEBwYXJhbSB7ZnVuY3Rpb24oc3RyaW5nLCBBcnJheS48c3RyaW5nPik6ID9BcnJheS48c3RyaW5nPn0gdGFnUG9saWN5XG4gICAqICAgICBBIGZ1bmN0aW9uIHRoYXQgdGFrZXMgKHRhZ05hbWUsIGF0dHJpYnNbXSksIHdoZXJlIHRhZ05hbWUgaXMgYSBrZXkgaW5cbiAgICogICAgIGh0bWw0LkVMRU1FTlRTIGFuZCBhdHRyaWJzIGlzIGFuIGFycmF5IG9mIGFsdGVybmF0aW5nIGF0dHJpYnV0ZSBuYW1lc1xuICAgKiAgICAgYW5kIHZhbHVlcy4gIEl0IHNob3VsZCByZXR1cm4gYSByZWNvcmQgKGFzIGZvbGxvd3MpLCBvciBudWxsIHRvIGRlbGV0ZVxuICAgKiAgICAgdGhlIGVsZW1lbnQuICBJdCdzIG9rYXkgZm9yIHRhZ1BvbGljeSB0byBtb2RpZnkgdGhlIGF0dHJpYnMgYXJyYXksXG4gICAqICAgICBidXQgdGhlIHNhbWUgYXJyYXkgaXMgcmV1c2VkLCBzbyBpdCBzaG91bGQgbm90IGJlIGhlbGQgYmV0d2VlbiBjYWxscy5cbiAgICogICAgIFJlY29yZCBrZXlzOlxuICAgKiAgICAgICAgYXR0cmliczogKHJlcXVpcmVkKSBTYW5pdGl6ZWQgYXR0cmlidXRlcyBhcnJheS5cbiAgICogICAgICAgIHRhZ05hbWU6IFJlcGxhY2VtZW50IHRhZyBuYW1lLlxuICAgKiBAcmV0dXJuIHtmdW5jdGlvbihzdHJpbmcsIEFycmF5KX0gQSBmdW5jdGlvbiB0aGF0IHNhbml0aXplcyBhIHN0cmluZyBvZlxuICAgKiAgICAgSFRNTCBhbmQgYXBwZW5kcyByZXN1bHQgc3RyaW5ncyB0byB0aGUgc2Vjb25kIGFyZ3VtZW50LCBhbiBhcnJheS5cbiAgICovXG4gIGZ1bmN0aW9uIG1ha2VIdG1sU2FuaXRpemVyKHRhZ1BvbGljeSkge1xuICAgIHZhciBzdGFjaztcbiAgICB2YXIgaWdub3Jpbmc7XG4gICAgdmFyIGVtaXQgPSBmdW5jdGlvbiAodGV4dCwgb3V0KSB7XG4gICAgICBpZiAoIWlnbm9yaW5nKSB7IG91dC5wdXNoKHRleHQpOyB9XG4gICAgfTtcbiAgICByZXR1cm4gbWFrZVNheFBhcnNlcih7XG4gICAgICAnc3RhcnREb2MnOiBmdW5jdGlvbihfKSB7XG4gICAgICAgIHN0YWNrID0gW107XG4gICAgICAgIGlnbm9yaW5nID0gZmFsc2U7XG4gICAgICB9LFxuICAgICAgJ3N0YXJ0VGFnJzogZnVuY3Rpb24odGFnTmFtZU9yaWcsIGF0dHJpYnMsIG91dCkge1xuICAgICAgICBpZiAoaWdub3JpbmcpIHsgcmV0dXJuOyB9XG4gICAgICAgIGlmICghaHRtbDQuRUxFTUVOVFMuaGFzT3duUHJvcGVydHkodGFnTmFtZU9yaWcpKSB7IHJldHVybjsgfVxuICAgICAgICB2YXIgZWZsYWdzT3JpZyA9IGh0bWw0LkVMRU1FTlRTW3RhZ05hbWVPcmlnXTtcbiAgICAgICAgaWYgKGVmbGFnc09yaWcgJiBodG1sNC5lZmxhZ3NbJ0ZPTERBQkxFJ10pIHtcbiAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICB2YXIgZGVjaXNpb24gPSB0YWdQb2xpY3kodGFnTmFtZU9yaWcsIGF0dHJpYnMpO1xuICAgICAgICBpZiAoIWRlY2lzaW9uKSB7XG4gICAgICAgICAgaWdub3JpbmcgPSAhKGVmbGFnc09yaWcgJiBodG1sNC5lZmxhZ3NbJ0VNUFRZJ10pO1xuICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfSBlbHNlIGlmICh0eXBlb2YgZGVjaXNpb24gIT09ICdvYmplY3QnKSB7XG4gICAgICAgICAgdGhyb3cgbmV3IEVycm9yKCd0YWdQb2xpY3kgZGlkIG5vdCByZXR1cm4gb2JqZWN0IChvbGQgQVBJPyknKTtcbiAgICAgICAgfVxuICAgICAgICBpZiAoJ2F0dHJpYnMnIGluIGRlY2lzaW9uKSB7XG4gICAgICAgICAgYXR0cmlicyA9IGRlY2lzaW9uWydhdHRyaWJzJ107XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgdGhyb3cgbmV3IEVycm9yKCd0YWdQb2xpY3kgZ2F2ZSBubyBhdHRyaWJzJyk7XG4gICAgICAgIH1cbiAgICAgICAgdmFyIGVmbGFnc1JlcDtcbiAgICAgICAgdmFyIHRhZ05hbWVSZXA7XG4gICAgICAgIGlmICgndGFnTmFtZScgaW4gZGVjaXNpb24pIHtcbiAgICAgICAgICB0YWdOYW1lUmVwID0gZGVjaXNpb25bJ3RhZ05hbWUnXTtcbiAgICAgICAgICBlZmxhZ3NSZXAgPSBodG1sNC5FTEVNRU5UU1t0YWdOYW1lUmVwXTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICB0YWdOYW1lUmVwID0gdGFnTmFtZU9yaWc7XG4gICAgICAgICAgZWZsYWdzUmVwID0gZWZsYWdzT3JpZztcbiAgICAgICAgfVxuICAgICAgICAvLyBUT0RPKG1pa2VzYW11ZWwpOiByZWx5aW5nIG9uIHRhZ1BvbGljeSBub3QgdG8gaW5zZXJ0IHVuc2FmZVxuICAgICAgICAvLyBhdHRyaWJ1dGUgbmFtZXMuXG5cbiAgICAgICAgLy8gSWYgdGhpcyBpcyBhbiBvcHRpb25hbC1lbmQtdGFnIGVsZW1lbnQgYW5kIGVpdGhlciB0aGlzIGVsZW1lbnQgb3IgaXRzXG4gICAgICAgIC8vIHByZXZpb3VzIGxpa2Ugc2libGluZyB3YXMgcmV3cml0dGVuLCB0aGVuIGluc2VydCBhIGNsb3NlIHRhZyB0b1xuICAgICAgICAvLyBwcmVzZXJ2ZSBzdHJ1Y3R1cmUuXG4gICAgICAgIGlmIChlZmxhZ3NPcmlnICYgaHRtbDQuZWZsYWdzWydPUFRJT05BTF9FTkRUQUcnXSkge1xuICAgICAgICAgIHZhciBvblN0YWNrID0gc3RhY2tbc3RhY2subGVuZ3RoIC0gMV07XG4gICAgICAgICAgaWYgKG9uU3RhY2sgJiYgb25TdGFjay5vcmlnID09PSB0YWdOYW1lT3JpZyAmJlxuICAgICAgICAgICAgICAob25TdGFjay5yZXAgIT09IHRhZ05hbWVSZXAgfHwgdGFnTmFtZU9yaWcgIT09IHRhZ05hbWVSZXApKSB7XG4gICAgICAgICAgICAgICAgb3V0LnB1c2goJzxcXC8nLCBvblN0YWNrLnJlcCwgJz4nKTtcbiAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoIShlZmxhZ3NPcmlnICYgaHRtbDQuZWZsYWdzWydFTVBUWSddKSkge1xuICAgICAgICAgIHN0YWNrLnB1c2goe29yaWc6IHRhZ05hbWVPcmlnLCByZXA6IHRhZ05hbWVSZXB9KTtcbiAgICAgICAgfVxuXG4gICAgICAgIG91dC5wdXNoKCc8JywgdGFnTmFtZVJlcCk7XG4gICAgICAgIGZvciAodmFyIGkgPSAwLCBuID0gYXR0cmlicy5sZW5ndGg7IGkgPCBuOyBpICs9IDIpIHtcbiAgICAgICAgICB2YXIgYXR0cmliTmFtZSA9IGF0dHJpYnNbaV0sXG4gICAgICAgICAgICAgIHZhbHVlID0gYXR0cmlic1tpICsgMV07XG4gICAgICAgICAgaWYgKHZhbHVlICE9PSBudWxsICYmIHZhbHVlICE9PSB2b2lkIDApIHtcbiAgICAgICAgICAgIG91dC5wdXNoKCcgJywgYXR0cmliTmFtZSwgJz1cIicsIGVzY2FwZUF0dHJpYih2YWx1ZSksICdcIicpO1xuICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICBvdXQucHVzaCgnPicpO1xuXG4gICAgICAgIGlmICgoZWZsYWdzT3JpZyAmIGh0bWw0LmVmbGFnc1snRU1QVFknXSlcbiAgICAgICAgICAgICYmICEoZWZsYWdzUmVwICYgaHRtbDQuZWZsYWdzWydFTVBUWSddKSkge1xuICAgICAgICAgIC8vIHJlcGxhY2VtZW50IGlzIG5vbi1lbXB0eSwgc3ludGhlc2l6ZSBlbmQgdGFnXG4gICAgICAgICAgb3V0LnB1c2goJzxcXC8nLCB0YWdOYW1lUmVwLCAnPicpO1xuICAgICAgICB9XG4gICAgICB9LFxuICAgICAgJ2VuZFRhZyc6IGZ1bmN0aW9uKHRhZ05hbWUsIG91dCkge1xuICAgICAgICBpZiAoaWdub3JpbmcpIHtcbiAgICAgICAgICBpZ25vcmluZyA9IGZhbHNlO1xuICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuICAgICAgICBpZiAoIWh0bWw0LkVMRU1FTlRTLmhhc093blByb3BlcnR5KHRhZ05hbWUpKSB7IHJldHVybjsgfVxuICAgICAgICB2YXIgZWZsYWdzID0gaHRtbDQuRUxFTUVOVFNbdGFnTmFtZV07XG4gICAgICAgIGlmICghKGVmbGFncyAmIChodG1sNC5lZmxhZ3NbJ0VNUFRZJ10gfCBodG1sNC5lZmxhZ3NbJ0ZPTERBQkxFJ10pKSkge1xuICAgICAgICAgIHZhciBpbmRleDtcbiAgICAgICAgICBpZiAoZWZsYWdzICYgaHRtbDQuZWZsYWdzWydPUFRJT05BTF9FTkRUQUcnXSkge1xuICAgICAgICAgICAgZm9yIChpbmRleCA9IHN0YWNrLmxlbmd0aDsgLS1pbmRleCA+PSAwOykge1xuICAgICAgICAgICAgICB2YXIgc3RhY2tFbE9yaWdUYWcgPSBzdGFja1tpbmRleF0ub3JpZztcbiAgICAgICAgICAgICAgaWYgKHN0YWNrRWxPcmlnVGFnID09PSB0YWdOYW1lKSB7IGJyZWFrOyB9XG4gICAgICAgICAgICAgIGlmICghKGh0bWw0LkVMRU1FTlRTW3N0YWNrRWxPcmlnVGFnXSAmXG4gICAgICAgICAgICAgICAgICAgIGh0bWw0LmVmbGFnc1snT1BUSU9OQUxfRU5EVEFHJ10pKSB7XG4gICAgICAgICAgICAgICAgLy8gRG9uJ3QgcG9wIG5vbiBvcHRpb25hbCBlbmQgdGFncyBsb29raW5nIGZvciBhIG1hdGNoLlxuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICBmb3IgKGluZGV4ID0gc3RhY2subGVuZ3RoOyAtLWluZGV4ID49IDA7KSB7XG4gICAgICAgICAgICAgIGlmIChzdGFja1tpbmRleF0ub3JpZyA9PT0gdGFnTmFtZSkgeyBicmVhazsgfVxuICAgICAgICAgICAgfVxuICAgICAgICAgIH1cbiAgICAgICAgICBpZiAoaW5kZXggPCAwKSB7IHJldHVybjsgfSAgLy8gTm90IG9wZW5lZC5cbiAgICAgICAgICBmb3IgKHZhciBpID0gc3RhY2subGVuZ3RoOyAtLWkgPiBpbmRleDspIHtcbiAgICAgICAgICAgIHZhciBzdGFja0VsUmVwVGFnID0gc3RhY2tbaV0ucmVwO1xuICAgICAgICAgICAgaWYgKCEoaHRtbDQuRUxFTUVOVFNbc3RhY2tFbFJlcFRhZ10gJlxuICAgICAgICAgICAgICAgICAgaHRtbDQuZWZsYWdzWydPUFRJT05BTF9FTkRUQUcnXSkpIHtcbiAgICAgICAgICAgICAgb3V0LnB1c2goJzxcXC8nLCBzdGFja0VsUmVwVGFnLCAnPicpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgIH1cbiAgICAgICAgICBpZiAoaW5kZXggPCBzdGFjay5sZW5ndGgpIHtcbiAgICAgICAgICAgIHRhZ05hbWUgPSBzdGFja1tpbmRleF0ucmVwO1xuICAgICAgICAgIH1cbiAgICAgICAgICBzdGFjay5sZW5ndGggPSBpbmRleDtcbiAgICAgICAgICBvdXQucHVzaCgnPFxcLycsIHRhZ05hbWUsICc+Jyk7XG4gICAgICAgIH1cbiAgICAgIH0sXG4gICAgICAncGNkYXRhJzogZW1pdCxcbiAgICAgICdyY2RhdGEnOiBlbWl0LFxuICAgICAgJ2NkYXRhJzogZW1pdCxcbiAgICAgICdlbmREb2MnOiBmdW5jdGlvbihvdXQpIHtcbiAgICAgICAgZm9yICg7IHN0YWNrLmxlbmd0aDsgc3RhY2subGVuZ3RoLS0pIHtcbiAgICAgICAgICBvdXQucHVzaCgnPFxcLycsIHN0YWNrW3N0YWNrLmxlbmd0aCAtIDFdLnJlcCwgJz4nKTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH0pO1xuICB9XG5cbiAgdmFyIEFMTE9XRURfVVJJX1NDSEVNRVMgPSAvXig/Omh0dHBzP3xtYWlsdG98ZGF0YSkkL2k7XG5cbiAgZnVuY3Rpb24gc2FmZVVyaSh1cmksIGVmZmVjdCwgbHR5cGUsIGhpbnRzLCBuYWl2ZVVyaVJld3JpdGVyKSB7XG4gICAgaWYgKCFuYWl2ZVVyaVJld3JpdGVyKSB7IHJldHVybiBudWxsOyB9XG4gICAgdHJ5IHtcbiAgICAgIHZhciBwYXJzZWQgPSBVUkkucGFyc2UoJycgKyB1cmkpO1xuICAgICAgaWYgKHBhcnNlZCkge1xuICAgICAgICBpZiAoIXBhcnNlZC5oYXNTY2hlbWUoKSB8fFxuICAgICAgICAgICAgQUxMT1dFRF9VUklfU0NIRU1FUy50ZXN0KHBhcnNlZC5nZXRTY2hlbWUoKSkpIHtcbiAgICAgICAgICB2YXIgc2FmZSA9IG5haXZlVXJpUmV3cml0ZXIocGFyc2VkLCBlZmZlY3QsIGx0eXBlLCBoaW50cyk7XG4gICAgICAgICAgcmV0dXJuIHNhZmUgPyBzYWZlLnRvU3RyaW5nKCkgOiBudWxsO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgfSBjYXRjaCAoZSkge1xuICAgICAgcmV0dXJuIG51bGw7XG4gICAgfVxuICAgIHJldHVybiBudWxsO1xuICB9XG5cbiAgZnVuY3Rpb24gbG9nKGxvZ2dlciwgdGFnTmFtZSwgYXR0cmliTmFtZSwgb2xkVmFsdWUsIG5ld1ZhbHVlKSB7XG4gICAgaWYgKCFhdHRyaWJOYW1lKSB7XG4gICAgICBsb2dnZXIodGFnTmFtZSArIFwiIHJlbW92ZWRcIiwge1xuICAgICAgICBjaGFuZ2U6IFwicmVtb3ZlZFwiLFxuICAgICAgICB0YWdOYW1lOiB0YWdOYW1lXG4gICAgICB9KTtcbiAgICB9XG4gICAgaWYgKG9sZFZhbHVlICE9PSBuZXdWYWx1ZSkge1xuICAgICAgdmFyIGNoYW5nZWQgPSBcImNoYW5nZWRcIjtcbiAgICAgIGlmIChvbGRWYWx1ZSAmJiAhbmV3VmFsdWUpIHtcbiAgICAgICAgY2hhbmdlZCA9IFwicmVtb3ZlZFwiO1xuICAgICAgfSBlbHNlIGlmICghb2xkVmFsdWUgJiYgbmV3VmFsdWUpICB7XG4gICAgICAgIGNoYW5nZWQgPSBcImFkZGVkXCI7XG4gICAgICB9XG4gICAgICBsb2dnZXIodGFnTmFtZSArIFwiLlwiICsgYXR0cmliTmFtZSArIFwiIFwiICsgY2hhbmdlZCwge1xuICAgICAgICBjaGFuZ2U6IGNoYW5nZWQsXG4gICAgICAgIHRhZ05hbWU6IHRhZ05hbWUsXG4gICAgICAgIGF0dHJpYk5hbWU6IGF0dHJpYk5hbWUsXG4gICAgICAgIG9sZFZhbHVlOiBvbGRWYWx1ZSxcbiAgICAgICAgbmV3VmFsdWU6IG5ld1ZhbHVlXG4gICAgICB9KTtcbiAgICB9XG4gIH1cblxuICBmdW5jdGlvbiBsb29rdXBBdHRyaWJ1dGUobWFwLCB0YWdOYW1lLCBhdHRyaWJOYW1lKSB7XG4gICAgdmFyIGF0dHJpYktleTtcbiAgICBhdHRyaWJLZXkgPSB0YWdOYW1lICsgJzo6JyArIGF0dHJpYk5hbWU7XG4gICAgaWYgKG1hcC5oYXNPd25Qcm9wZXJ0eShhdHRyaWJLZXkpKSB7XG4gICAgICByZXR1cm4gbWFwW2F0dHJpYktleV07XG4gICAgfVxuICAgIGF0dHJpYktleSA9ICcqOjonICsgYXR0cmliTmFtZTtcbiAgICBpZiAobWFwLmhhc093blByb3BlcnR5KGF0dHJpYktleSkpIHtcbiAgICAgIHJldHVybiBtYXBbYXR0cmliS2V5XTtcbiAgICB9XG4gICAgcmV0dXJuIHZvaWQgMDtcbiAgfVxuICBmdW5jdGlvbiBnZXRBdHRyaWJ1dGVUeXBlKHRhZ05hbWUsIGF0dHJpYk5hbWUpIHtcbiAgICByZXR1cm4gbG9va3VwQXR0cmlidXRlKGh0bWw0LkFUVFJJQlMsIHRhZ05hbWUsIGF0dHJpYk5hbWUpO1xuICB9XG4gIGZ1bmN0aW9uIGdldExvYWRlclR5cGUodGFnTmFtZSwgYXR0cmliTmFtZSkge1xuICAgIHJldHVybiBsb29rdXBBdHRyaWJ1dGUoaHRtbDQuTE9BREVSVFlQRVMsIHRhZ05hbWUsIGF0dHJpYk5hbWUpO1xuICB9XG4gIGZ1bmN0aW9uIGdldFVyaUVmZmVjdCh0YWdOYW1lLCBhdHRyaWJOYW1lKSB7XG4gICAgcmV0dXJuIGxvb2t1cEF0dHJpYnV0ZShodG1sNC5VUklFRkZFQ1RTLCB0YWdOYW1lLCBhdHRyaWJOYW1lKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBTYW5pdGl6ZXMgYXR0cmlidXRlcyBvbiBhbiBIVE1MIHRhZy5cbiAgICogQHBhcmFtIHtzdHJpbmd9IHRhZ05hbWUgQW4gSFRNTCB0YWcgbmFtZSBpbiBsb3dlcmNhc2UuXG4gICAqIEBwYXJhbSB7QXJyYXkuPD9zdHJpbmc+fSBhdHRyaWJzIEFuIGFycmF5IG9mIGFsdGVybmF0aW5nIG5hbWVzIGFuZCB2YWx1ZXMuXG4gICAqIEBwYXJhbSB7P2Z1bmN0aW9uKD9zdHJpbmcpOiA/c3RyaW5nfSBvcHRfbmFpdmVVcmlSZXdyaXRlciBBIHRyYW5zZm9ybSB0b1xuICAgKiAgICAgYXBwbHkgdG8gVVJJIGF0dHJpYnV0ZXM7IGl0IGNhbiByZXR1cm4gYSBuZXcgc3RyaW5nIHZhbHVlLCBvciBudWxsIHRvXG4gICAqICAgICBkZWxldGUgdGhlIGF0dHJpYnV0ZS4gIElmIHVuc3BlY2lmaWVkLCBVUkkgYXR0cmlidXRlcyBhcmUgZGVsZXRlZC5cbiAgICogQHBhcmFtIHtmdW5jdGlvbig/c3RyaW5nKTogP3N0cmluZ30gb3B0X25tVG9rZW5Qb2xpY3kgQSB0cmFuc2Zvcm0gdG8gYXBwbHlcbiAgICogICAgIHRvIGF0dHJpYnV0ZXMgY29udGFpbmluZyBIVE1MIG5hbWVzLCBlbGVtZW50IElEcywgYW5kIHNwYWNlLXNlcGFyYXRlZFxuICAgKiAgICAgbGlzdHMgb2YgY2xhc3NlczsgaXQgY2FuIHJldHVybiBhIG5ldyBzdHJpbmcgdmFsdWUsIG9yIG51bGwgdG8gZGVsZXRlXG4gICAqICAgICB0aGUgYXR0cmlidXRlLiAgSWYgdW5zcGVjaWZpZWQsIHRoZXNlIGF0dHJpYnV0ZXMgYXJlIGtlcHQgdW5jaGFuZ2VkLlxuICAgKiBAcmV0dXJuIHtBcnJheS48P3N0cmluZz59IFRoZSBzYW5pdGl6ZWQgYXR0cmlidXRlcyBhcyBhIGxpc3Qgb2YgYWx0ZXJuYXRpbmdcbiAgICogICAgIG5hbWVzIGFuZCB2YWx1ZXMsIHdoZXJlIGEgbnVsbCB2YWx1ZSBtZWFucyB0byBvbWl0IHRoZSBhdHRyaWJ1dGUuXG4gICAqL1xuICBmdW5jdGlvbiBzYW5pdGl6ZUF0dHJpYnModGFnTmFtZSwgYXR0cmlicyxcbiAgICBvcHRfbmFpdmVVcmlSZXdyaXRlciwgb3B0X25tVG9rZW5Qb2xpY3ksIG9wdF9sb2dnZXIpIHtcbiAgICAvLyBUT0RPKGZlbGl4OGEpOiBpdCdzIG9ibm94aW91cyB0aGF0IGRvbWFkbyBkdXBsaWNhdGVzIG11Y2ggb2YgdGhpc1xuICAgIC8vIFRPRE8oZmVsaXg4YSk6IG1heWJlIGNvbnNpc3RlbnRseSBlbmZvcmNlIGNvbnN0cmFpbnRzIGxpa2UgdGFyZ2V0PVxuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgYXR0cmlicy5sZW5ndGg7IGkgKz0gMikge1xuICAgICAgdmFyIGF0dHJpYk5hbWUgPSBhdHRyaWJzW2ldO1xuICAgICAgdmFyIHZhbHVlID0gYXR0cmlic1tpICsgMV07XG4gICAgICB2YXIgb2xkVmFsdWUgPSB2YWx1ZTtcbiAgICAgIHZhciBhdHlwZSA9IG51bGwsIGF0dHJpYktleTtcbiAgICAgIGlmICgoYXR0cmliS2V5ID0gdGFnTmFtZSArICc6OicgKyBhdHRyaWJOYW1lLFxuICAgICAgICAgICBodG1sNC5BVFRSSUJTLmhhc093blByb3BlcnR5KGF0dHJpYktleSkpIHx8XG4gICAgICAgICAgKGF0dHJpYktleSA9ICcqOjonICsgYXR0cmliTmFtZSxcbiAgICAgICAgICAgaHRtbDQuQVRUUklCUy5oYXNPd25Qcm9wZXJ0eShhdHRyaWJLZXkpKSkge1xuICAgICAgICBhdHlwZSA9IGh0bWw0LkFUVFJJQlNbYXR0cmliS2V5XTtcbiAgICAgIH1cbiAgICAgIGlmIChhdHlwZSAhPT0gbnVsbCkge1xuICAgICAgICBzd2l0Y2ggKGF0eXBlKSB7XG4gICAgICAgICAgY2FzZSBodG1sNC5hdHlwZVsnTk9ORSddOiBicmVhaztcbiAgICAgICAgICBjYXNlIGh0bWw0LmF0eXBlWydTQ1JJUFQnXTpcbiAgICAgICAgICAgIHZhbHVlID0gbnVsbDtcbiAgICAgICAgICAgIGlmIChvcHRfbG9nZ2VyKSB7XG4gICAgICAgICAgICAgIGxvZyhvcHRfbG9nZ2VyLCB0YWdOYW1lLCBhdHRyaWJOYW1lLCBvbGRWYWx1ZSwgdmFsdWUpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgY2FzZSBodG1sNC5hdHlwZVsnU1RZTEUnXTpcbiAgICAgICAgICAgIGlmICgndW5kZWZpbmVkJyA9PT0gdHlwZW9mIHBhcnNlQ3NzRGVjbGFyYXRpb25zKSB7XG4gICAgICAgICAgICAgIHZhbHVlID0gbnVsbDtcbiAgICAgICAgICAgICAgaWYgKG9wdF9sb2dnZXIpIHtcbiAgICAgICAgICAgICAgICBsb2cob3B0X2xvZ2dlciwgdGFnTmFtZSwgYXR0cmliTmFtZSwgb2xkVmFsdWUsIHZhbHVlKTtcblx0ICAgICAgfVxuICAgICAgICAgICAgICBicmVhaztcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHZhciBzYW5pdGl6ZWREZWNsYXJhdGlvbnMgPSBbXTtcbiAgICAgICAgICAgIHBhcnNlQ3NzRGVjbGFyYXRpb25zKFxuICAgICAgICAgICAgICAgIHZhbHVlLFxuICAgICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICAgIGRlY2xhcmF0aW9uOiBmdW5jdGlvbiAocHJvcGVydHksIHRva2Vucykge1xuICAgICAgICAgICAgICAgICAgICB2YXIgbm9ybVByb3AgPSBwcm9wZXJ0eS50b0xvd2VyQ2FzZSgpO1xuICAgICAgICAgICAgICAgICAgICB2YXIgc2NoZW1hID0gY3NzU2NoZW1hW25vcm1Qcm9wXTtcbiAgICAgICAgICAgICAgICAgICAgaWYgKCFzY2hlbWEpIHtcbiAgICAgICAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgc2FuaXRpemVDc3NQcm9wZXJ0eShcbiAgICAgICAgICAgICAgICAgICAgICAgIG5vcm1Qcm9wLCBzY2hlbWEsIHRva2VucyxcbiAgICAgICAgICAgICAgICAgICAgICAgIG9wdF9uYWl2ZVVyaVJld3JpdGVyXG4gICAgICAgICAgICAgICAgICAgICAgICA/IGZ1bmN0aW9uICh1cmwpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4gc2FmZVVyaShcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdXJsLCBodG1sNC51ZWZmZWN0cy5TQU1FX0RPQ1VNRU5ULFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBodG1sNC5sdHlwZXMuU0FOREJPWEVELFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJUWVBFXCI6IFwiQ1NTXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJDU1NfUFJPUFwiOiBub3JtUHJvcFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9LCBvcHRfbmFpdmVVcmlSZXdyaXRlcik7XG4gICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgIDogbnVsbCk7XG4gICAgICAgICAgICAgICAgICAgIHNhbml0aXplZERlY2xhcmF0aW9ucy5wdXNoKHByb3BlcnR5ICsgJzogJyArIHRva2Vucy5qb2luKCcgJykpO1xuICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgdmFsdWUgPSBzYW5pdGl6ZWREZWNsYXJhdGlvbnMubGVuZ3RoID4gMCA/XG4gICAgICAgICAgICAgIHNhbml0aXplZERlY2xhcmF0aW9ucy5qb2luKCcgOyAnKSA6IG51bGw7XG4gICAgICAgICAgICBpZiAob3B0X2xvZ2dlcikge1xuICAgICAgICAgICAgICBsb2cob3B0X2xvZ2dlciwgdGFnTmFtZSwgYXR0cmliTmFtZSwgb2xkVmFsdWUsIHZhbHVlKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGJyZWFrO1xuICAgICAgICAgIGNhc2UgaHRtbDQuYXR5cGVbJ0lEJ106XG4gICAgICAgICAgY2FzZSBodG1sNC5hdHlwZVsnSURSRUYnXTpcbiAgICAgICAgICBjYXNlIGh0bWw0LmF0eXBlWydJRFJFRlMnXTpcbiAgICAgICAgICBjYXNlIGh0bWw0LmF0eXBlWydHTE9CQUxfTkFNRSddOlxuICAgICAgICAgIGNhc2UgaHRtbDQuYXR5cGVbJ0xPQ0FMX05BTUUnXTpcbiAgICAgICAgICBjYXNlIGh0bWw0LmF0eXBlWydDTEFTU0VTJ106XG4gICAgICAgICAgICB2YWx1ZSA9IG9wdF9ubVRva2VuUG9saWN5ID8gb3B0X25tVG9rZW5Qb2xpY3kodmFsdWUpIDogdmFsdWU7XG4gICAgICAgICAgICBpZiAob3B0X2xvZ2dlcikge1xuICAgICAgICAgICAgICBsb2cob3B0X2xvZ2dlciwgdGFnTmFtZSwgYXR0cmliTmFtZSwgb2xkVmFsdWUsIHZhbHVlKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGJyZWFrO1xuICAgICAgICAgIGNhc2UgaHRtbDQuYXR5cGVbJ1VSSSddOlxuICAgICAgICAgICAgdmFsdWUgPSBzYWZlVXJpKHZhbHVlLFxuICAgICAgICAgICAgICBnZXRVcmlFZmZlY3QodGFnTmFtZSwgYXR0cmliTmFtZSksXG4gICAgICAgICAgICAgIGdldExvYWRlclR5cGUodGFnTmFtZSwgYXR0cmliTmFtZSksXG4gICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICBcIlRZUEVcIjogXCJNQVJLVVBcIixcbiAgICAgICAgICAgICAgICBcIlhNTF9BVFRSXCI6IGF0dHJpYk5hbWUsXG4gICAgICAgICAgICAgICAgXCJYTUxfVEFHXCI6IHRhZ05hbWVcbiAgICAgICAgICAgICAgfSwgb3B0X25haXZlVXJpUmV3cml0ZXIpO1xuICAgICAgICAgICAgICBpZiAob3B0X2xvZ2dlcikge1xuICAgICAgICAgICAgICBsb2cob3B0X2xvZ2dlciwgdGFnTmFtZSwgYXR0cmliTmFtZSwgb2xkVmFsdWUsIHZhbHVlKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGJyZWFrO1xuICAgICAgICAgIGNhc2UgaHRtbDQuYXR5cGVbJ1VSSV9GUkFHTUVOVCddOlxuICAgICAgICAgICAgaWYgKHZhbHVlICYmICcjJyA9PT0gdmFsdWUuY2hhckF0KDApKSB7XG4gICAgICAgICAgICAgIHZhbHVlID0gdmFsdWUuc3Vic3RyaW5nKDEpOyAgLy8gcmVtb3ZlIHRoZSBsZWFkaW5nICcjJ1xuICAgICAgICAgICAgICB2YWx1ZSA9IG9wdF9ubVRva2VuUG9saWN5ID8gb3B0X25tVG9rZW5Qb2xpY3kodmFsdWUpIDogdmFsdWU7XG4gICAgICAgICAgICAgIGlmICh2YWx1ZSAhPT0gbnVsbCAmJiB2YWx1ZSAhPT0gdm9pZCAwKSB7XG4gICAgICAgICAgICAgICAgdmFsdWUgPSAnIycgKyB2YWx1ZTsgIC8vIHJlc3RvcmUgdGhlIGxlYWRpbmcgJyMnXG4gICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgIHZhbHVlID0gbnVsbDtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGlmIChvcHRfbG9nZ2VyKSB7XG4gICAgICAgICAgICAgIGxvZyhvcHRfbG9nZ2VyLCB0YWdOYW1lLCBhdHRyaWJOYW1lLCBvbGRWYWx1ZSwgdmFsdWUpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgZGVmYXVsdDpcbiAgICAgICAgICAgIHZhbHVlID0gbnVsbDtcbiAgICAgICAgICAgIGlmIChvcHRfbG9nZ2VyKSB7XG4gICAgICAgICAgICAgIGxvZyhvcHRfbG9nZ2VyLCB0YWdOYW1lLCBhdHRyaWJOYW1lLCBvbGRWYWx1ZSwgdmFsdWUpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgIH1cbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIHZhbHVlID0gbnVsbDtcbiAgICAgICAgaWYgKG9wdF9sb2dnZXIpIHtcbiAgICAgICAgICBsb2cob3B0X2xvZ2dlciwgdGFnTmFtZSwgYXR0cmliTmFtZSwgb2xkVmFsdWUsIHZhbHVlKTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgICAgYXR0cmlic1tpICsgMV0gPSB2YWx1ZTtcbiAgICB9XG4gICAgcmV0dXJuIGF0dHJpYnM7XG4gIH1cblxuICAvKipcbiAgICogQ3JlYXRlcyBhIHRhZyBwb2xpY3kgdGhhdCBvbWl0cyBhbGwgdGFncyBtYXJrZWQgVU5TQUZFIGluIGh0bWw0LWRlZnMuanNcbiAgICogYW5kIGFwcGxpZXMgdGhlIGRlZmF1bHQgYXR0cmlidXRlIHNhbml0aXplciB3aXRoIHRoZSBzdXBwbGllZCBwb2xpY3kgZm9yXG4gICAqIFVSSSBhdHRyaWJ1dGVzIGFuZCBOTVRPS0VOIGF0dHJpYnV0ZXMuXG4gICAqIEBwYXJhbSB7P2Z1bmN0aW9uKD9zdHJpbmcpOiA/c3RyaW5nfSBvcHRfbmFpdmVVcmlSZXdyaXRlciBBIHRyYW5zZm9ybSB0b1xuICAgKiAgICAgYXBwbHkgdG8gVVJJIGF0dHJpYnV0ZXMuICBJZiBub3QgZ2l2ZW4sIFVSSSBhdHRyaWJ1dGVzIGFyZSBkZWxldGVkLlxuICAgKiBAcGFyYW0ge2Z1bmN0aW9uKD9zdHJpbmcpOiA/c3RyaW5nfSBvcHRfbm1Ub2tlblBvbGljeSBBIHRyYW5zZm9ybSB0byBhcHBseVxuICAgKiAgICAgdG8gYXR0cmlidXRlcyBjb250YWluaW5nIEhUTUwgbmFtZXMsIGVsZW1lbnQgSURzLCBhbmQgc3BhY2Utc2VwYXJhdGVkXG4gICAqICAgICBsaXN0cyBvZiBjbGFzc2VzLiAgSWYgbm90IGdpdmVuLCBzdWNoIGF0dHJpYnV0ZXMgYXJlIGxlZnQgdW5jaGFuZ2VkLlxuICAgKiBAcmV0dXJuIHtmdW5jdGlvbihzdHJpbmcsIEFycmF5Ljw/c3RyaW5nPil9IEEgdGFnUG9saWN5IHN1aXRhYmxlIGZvclxuICAgKiAgICAgcGFzc2luZyB0byBodG1sLnNhbml0aXplLlxuICAgKi9cbiAgZnVuY3Rpb24gbWFrZVRhZ1BvbGljeShcbiAgICBvcHRfbmFpdmVVcmlSZXdyaXRlciwgb3B0X25tVG9rZW5Qb2xpY3ksIG9wdF9sb2dnZXIpIHtcbiAgICByZXR1cm4gZnVuY3Rpb24odGFnTmFtZSwgYXR0cmlicykge1xuICAgICAgaWYgKCEoaHRtbDQuRUxFTUVOVFNbdGFnTmFtZV0gJiBodG1sNC5lZmxhZ3NbJ1VOU0FGRSddKSkge1xuICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICdhdHRyaWJzJzogc2FuaXRpemVBdHRyaWJzKHRhZ05hbWUsIGF0dHJpYnMsXG4gICAgICAgICAgICBvcHRfbmFpdmVVcmlSZXdyaXRlciwgb3B0X25tVG9rZW5Qb2xpY3ksIG9wdF9sb2dnZXIpXG4gICAgICAgIH07XG4gICAgICB9IGVsc2Uge1xuICAgICAgICBpZiAob3B0X2xvZ2dlcikge1xuICAgICAgICAgIGxvZyhvcHRfbG9nZ2VyLCB0YWdOYW1lLCB1bmRlZmluZWQsIHVuZGVmaW5lZCwgdW5kZWZpbmVkKTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH07XG4gIH1cblxuICAvKipcbiAgICogU2FuaXRpemVzIEhUTUwgdGFncyBhbmQgYXR0cmlidXRlcyBhY2NvcmRpbmcgdG8gYSBnaXZlbiBwb2xpY3kuXG4gICAqIEBwYXJhbSB7c3RyaW5nfSBpbnB1dEh0bWwgVGhlIEhUTUwgdG8gc2FuaXRpemUuXG4gICAqIEBwYXJhbSB7ZnVuY3Rpb24oc3RyaW5nLCBBcnJheS48P3N0cmluZz4pfSB0YWdQb2xpY3kgQSBmdW5jdGlvbiB0aGF0XG4gICAqICAgICBkZWNpZGVzIHdoaWNoIHRhZ3MgdG8gYWNjZXB0IGFuZCBzYW5pdGl6ZXMgdGhlaXIgYXR0cmlidXRlcyAoc2VlXG4gICAqICAgICBtYWtlSHRtbFNhbml0aXplciBhYm92ZSBmb3IgZGV0YWlscykuXG4gICAqIEByZXR1cm4ge3N0cmluZ30gVGhlIHNhbml0aXplZCBIVE1MLlxuICAgKi9cbiAgZnVuY3Rpb24gc2FuaXRpemVXaXRoUG9saWN5KGlucHV0SHRtbCwgdGFnUG9saWN5KSB7XG4gICAgdmFyIG91dHB1dEFycmF5ID0gW107XG4gICAgbWFrZUh0bWxTYW5pdGl6ZXIodGFnUG9saWN5KShpbnB1dEh0bWwsIG91dHB1dEFycmF5KTtcbiAgICByZXR1cm4gb3V0cHV0QXJyYXkuam9pbignJyk7XG4gIH1cblxuICAvKipcbiAgICogU3RyaXBzIHVuc2FmZSB0YWdzIGFuZCBhdHRyaWJ1dGVzIGZyb20gSFRNTC5cbiAgICogQHBhcmFtIHtzdHJpbmd9IGlucHV0SHRtbCBUaGUgSFRNTCB0byBzYW5pdGl6ZS5cbiAgICogQHBhcmFtIHs/ZnVuY3Rpb24oP3N0cmluZyk6ID9zdHJpbmd9IG9wdF9uYWl2ZVVyaVJld3JpdGVyIEEgdHJhbnNmb3JtIHRvXG4gICAqICAgICBhcHBseSB0byBVUkkgYXR0cmlidXRlcy4gIElmIG5vdCBnaXZlbiwgVVJJIGF0dHJpYnV0ZXMgYXJlIGRlbGV0ZWQuXG4gICAqIEBwYXJhbSB7ZnVuY3Rpb24oP3N0cmluZyk6ID9zdHJpbmd9IG9wdF9ubVRva2VuUG9saWN5IEEgdHJhbnNmb3JtIHRvIGFwcGx5XG4gICAqICAgICB0byBhdHRyaWJ1dGVzIGNvbnRhaW5pbmcgSFRNTCBuYW1lcywgZWxlbWVudCBJRHMsIGFuZCBzcGFjZS1zZXBhcmF0ZWRcbiAgICogICAgIGxpc3RzIG9mIGNsYXNzZXMuICBJZiBub3QgZ2l2ZW4sIHN1Y2ggYXR0cmlidXRlcyBhcmUgbGVmdCB1bmNoYW5nZWQuXG4gICAqL1xuICBmdW5jdGlvbiBzYW5pdGl6ZShpbnB1dEh0bWwsXG4gICAgb3B0X25haXZlVXJpUmV3cml0ZXIsIG9wdF9ubVRva2VuUG9saWN5LCBvcHRfbG9nZ2VyKSB7XG4gICAgdmFyIHRhZ1BvbGljeSA9IG1ha2VUYWdQb2xpY3koXG4gICAgICBvcHRfbmFpdmVVcmlSZXdyaXRlciwgb3B0X25tVG9rZW5Qb2xpY3ksIG9wdF9sb2dnZXIpO1xuICAgIHJldHVybiBzYW5pdGl6ZVdpdGhQb2xpY3koaW5wdXRIdG1sLCB0YWdQb2xpY3kpO1xuICB9XG5cbiAgLy8gRXhwb3J0IGJvdGggcXVvdGVkIGFuZCB1bnF1b3RlZCBuYW1lcyBmb3IgQ2xvc3VyZSBsaW5rYWdlLlxuICB2YXIgaHRtbCA9IHt9O1xuICBodG1sLmVzY2FwZUF0dHJpYiA9IGh0bWxbJ2VzY2FwZUF0dHJpYiddID0gZXNjYXBlQXR0cmliO1xuICBodG1sLm1ha2VIdG1sU2FuaXRpemVyID0gaHRtbFsnbWFrZUh0bWxTYW5pdGl6ZXInXSA9IG1ha2VIdG1sU2FuaXRpemVyO1xuICBodG1sLm1ha2VTYXhQYXJzZXIgPSBodG1sWydtYWtlU2F4UGFyc2VyJ10gPSBtYWtlU2F4UGFyc2VyO1xuICBodG1sLm1ha2VUYWdQb2xpY3kgPSBodG1sWydtYWtlVGFnUG9saWN5J10gPSBtYWtlVGFnUG9saWN5O1xuICBodG1sLm5vcm1hbGl6ZVJDRGF0YSA9IGh0bWxbJ25vcm1hbGl6ZVJDRGF0YSddID0gbm9ybWFsaXplUkNEYXRhO1xuICBodG1sLnNhbml0aXplID0gaHRtbFsnc2FuaXRpemUnXSA9IHNhbml0aXplO1xuICBodG1sLnNhbml0aXplQXR0cmlicyA9IGh0bWxbJ3Nhbml0aXplQXR0cmlicyddID0gc2FuaXRpemVBdHRyaWJzO1xuICBodG1sLnNhbml0aXplV2l0aFBvbGljeSA9IGh0bWxbJ3Nhbml0aXplV2l0aFBvbGljeSddID0gc2FuaXRpemVXaXRoUG9saWN5O1xuICBodG1sLnVuZXNjYXBlRW50aXRpZXMgPSBodG1sWyd1bmVzY2FwZUVudGl0aWVzJ10gPSB1bmVzY2FwZUVudGl0aWVzO1xuICByZXR1cm4gaHRtbDtcbn0pKGh0bWw0KTtcblxudmFyIGh0bWxfc2FuaXRpemUgPSBodG1sWydzYW5pdGl6ZSddO1xuXG4vLyBMb29zZW4gcmVzdHJpY3Rpb25zIG9mIENhamEnc1xuLy8gaHRtbC1zYW5pdGl6ZXIgdG8gYWxsb3cgZm9yIHN0eWxpbmdcbmh0bWw0LkFUVFJJQlNbJyo6OnN0eWxlJ10gPSAwO1xuaHRtbDQuRUxFTUVOVFNbJ3N0eWxlJ10gPSAwO1xuaHRtbDQuQVRUUklCU1snYTo6dGFyZ2V0J10gPSAwO1xuaHRtbDQuRUxFTUVOVFNbJ3ZpZGVvJ10gPSAwO1xuaHRtbDQuQVRUUklCU1sndmlkZW86OnNyYyddID0gMDtcbmh0bWw0LkFUVFJJQlNbJ3ZpZGVvOjpwb3N0ZXInXSA9IDA7XG5odG1sNC5BVFRSSUJTWyd2aWRlbzo6Y29udHJvbHMnXSA9IDA7XG5odG1sNC5FTEVNRU5UU1snYXVkaW8nXSA9IDA7XG5odG1sNC5BVFRSSUJTWydhdWRpbzo6c3JjJ10gPSAwO1xuaHRtbDQuQVRUUklCU1sndmlkZW86OmF1dG9wbGF5J10gPSAwO1xuaHRtbDQuQVRUUklCU1sndmlkZW86OmNvbnRyb2xzJ10gPSAwO1xuXG5pZiAodHlwZW9mIG1vZHVsZSAhPT0gJ3VuZGVmaW5lZCcpIHtcbiAgICBtb2R1bGUuZXhwb3J0cyA9IGh0bWxfc2FuaXRpemU7XG59XG4iLCJtb2R1bGUuZXhwb3J0cz17XG4gIFwiYXV0aG9yXCI6IFwiTWFwYm94XCIsXG4gIFwibmFtZVwiOiBcIm1hcGJveC5qc1wiLFxuICBcImRlc2NyaXB0aW9uXCI6IFwibWFwYm94IGphdmFzY3JpcHQgYXBpXCIsXG4gIFwidmVyc2lvblwiOiBcIjMuMS4xXCIsXG4gIFwiaG9tZXBhZ2VcIjogXCJodHRwOi8vbWFwYm94LmNvbS9cIixcbiAgXCJyZXBvc2l0b3J5XCI6IHtcbiAgICBcInR5cGVcIjogXCJnaXRcIixcbiAgICBcInVybFwiOiBcImdpdDovL2dpdGh1Yi5jb20vbWFwYm94L21hcGJveC5qcy5naXRcIlxuICB9LFxuICBcIm1haW5cIjogXCJzcmMvaW5kZXguanNcIixcbiAgXCJkZXBlbmRlbmNpZXNcIjoge1xuICAgIFwiY29yc2xpdGVcIjogXCIwLjAuNlwiLFxuICAgIFwiaXNhcnJheVwiOiBcIjAuMC4xXCIsXG4gICAgXCJsZWFmbGV0XCI6IFwiMS4wLjJcIixcbiAgICBcIm11c3RhY2hlXCI6IFwiMi4yLjFcIixcbiAgICBcInNhbml0aXplLWNhamFcIjogXCIwLjEuNFwiXG4gIH0sXG4gIFwic2NyaXB0c1wiOiB7XG4gICAgXCJ0ZXN0XCI6IFwiZXNsaW50IC0tbm8tZXNsaW50cmMgLWMgLmVzbGludHJjIHNyYyAmJiBwaGFudG9tanMgbm9kZV9tb2R1bGVzL21vY2hhLXBoYW50b21qcy1jb3JlL21vY2hhLXBoYW50b21qcy1jb3JlLmpzIHRlc3QvaW5kZXguaHRtbFwiXG4gIH0sXG4gIFwibGljZW5zZVwiOiBcIkJTRC0zLUNsYXVzZVwiLFxuICBcImRldkRlcGVuZGVuY2llc1wiOiB7XG4gICAgXCJicm93c2VyaWZ5XCI6IFwiXjEzLjAuMFwiLFxuICAgIFwiY2xlYW4tY3NzXCI6IFwifjIuMC43XCIsXG4gICAgXCJjei1jb252ZW50aW9uYWwtY2hhbmdlbG9nXCI6IFwiMS4yLjBcIixcbiAgICBcImVzbGludFwiOiBcIl4wLjIzLjBcIixcbiAgICBcImV4cGVjdC5qc1wiOiBcIjAuMy4xXCIsXG4gICAgXCJoYXBwZW5cIjogXCIwLjEuM1wiLFxuICAgIFwibGVhZmxldC1mdWxsc2NyZWVuXCI6IFwiMC4wLjRcIixcbiAgICBcImxlYWZsZXQtaGFzaFwiOiBcIjAuMi4xXCIsXG4gICAgXCJtYXJrZWRcIjogXCJ+MC4zLjBcIixcbiAgICBcIm1pbmlmeWlmeVwiOiBcIl42LjEuMFwiLFxuICAgIFwibWluaW1pc3RcIjogXCIwLjAuNVwiLFxuICAgIFwibW9jaGFcIjogXCIyLjQuNVwiLFxuICAgIFwibW9jaGEtcGhhbnRvbWpzLWNvcmVcIjogXCIyLjAuMVwiLFxuICAgIFwicGhhbnRvbWpzLXByZWJ1aWx0XCI6IFwiMi4xLjEyXCIsXG4gICAgXCJzaW5vblwiOiBcIjEuMTAuMlwiXG4gIH0sXG4gIFwib3B0aW9uYWxEZXBlbmRlbmNpZXNcIjoge30sXG4gIFwiZW5naW5lc1wiOiB7XG4gICAgXCJub2RlXCI6IFwiKlwiXG4gIH0sXG4gIFwiY29uZmlnXCI6IHtcbiAgICBcImNvbW1pdGl6ZW5cIjoge1xuICAgICAgXCJwYXRoXCI6IFwiLi9ub2RlX21vZHVsZXMvY3otY29udmVudGlvbmFsLWNoYW5nZWxvZ1wiXG4gICAgfVxuICB9XG59XG4iLCIndXNlIHN0cmljdCc7XG5cbm1vZHVsZS5leHBvcnRzID0ge1xuICAgIEhUVFBfVVJMOiAnaHR0cDovL2EudGlsZXMubWFwYm94LmNvbS92NCcsXG4gICAgSFRUUFNfVVJMOiAnaHR0cHM6Ly9hLnRpbGVzLm1hcGJveC5jb20vdjQnLFxuICAgIEZPUkNFX0hUVFBTOiBmYWxzZSxcbiAgICBSRVFVSVJFX0FDQ0VTU19UT0tFTjogdHJ1ZVxufTtcbiIsIid1c2Ugc3RyaWN0JztcblxudmFyIHV0aWwgPSByZXF1aXJlKCcuL3V0aWwnKSxcbiAgICBmb3JtYXRfdXJsID0gcmVxdWlyZSgnLi9mb3JtYXRfdXJsJyksXG4gICAgcmVxdWVzdCA9IHJlcXVpcmUoJy4vcmVxdWVzdCcpLFxuICAgIG1hcmtlciA9IHJlcXVpcmUoJy4vbWFya2VyJyksXG4gICAgc2ltcGxlc3R5bGUgPSByZXF1aXJlKCcuL3NpbXBsZXN0eWxlJyk7XG5cbi8vICMgZmVhdHVyZUxheWVyXG4vL1xuLy8gQSBsYXllciBvZiBmZWF0dXJlcywgbG9hZGVkIGZyb20gTWFwYm94IG9yIGVsc2UuIEFkZHMgdGhlIGFiaWxpdHlcbi8vIHRvIHJlc2V0IGZlYXR1cmVzLCBmaWx0ZXIgdGhlbSwgYW5kIGxvYWQgdGhlbSBmcm9tIGEgR2VvSlNPTiBVUkwuXG52YXIgRmVhdHVyZUxheWVyID0gTC5GZWF0dXJlR3JvdXAuZXh0ZW5kKHtcbiAgICBvcHRpb25zOiB7XG4gICAgICAgIGZpbHRlcjogZnVuY3Rpb24oKSB7IHJldHVybiB0cnVlOyB9LFxuICAgICAgICBzYW5pdGl6ZXI6IHJlcXVpcmUoJ3Nhbml0aXplLWNhamEnKSxcbiAgICAgICAgc3R5bGU6IHNpbXBsZXN0eWxlLnN0eWxlLFxuICAgICAgICBwb3B1cE9wdGlvbnM6IHsgY2xvc2VCdXR0b246IGZhbHNlIH1cbiAgICB9LFxuXG4gICAgaW5pdGlhbGl6ZTogZnVuY3Rpb24oXywgb3B0aW9ucykge1xuICAgICAgICBMLnNldE9wdGlvbnModGhpcywgb3B0aW9ucyk7XG5cbiAgICAgICAgdGhpcy5fbGF5ZXJzID0ge307XG5cbiAgICAgICAgaWYgKHR5cGVvZiBfID09PSAnc3RyaW5nJykge1xuICAgICAgICAgICAgdXRpbC5pZFVybChfLCB0aGlzKTtcbiAgICAgICAgLy8gamF2YXNjcmlwdCBvYmplY3Qgb2YgVGlsZUpTT04gZGF0YVxuICAgICAgICB9IGVsc2UgaWYgKF8gJiYgdHlwZW9mIF8gPT09ICdvYmplY3QnKSB7XG4gICAgICAgICAgICB0aGlzLnNldEdlb0pTT04oXyk7XG4gICAgICAgIH1cbiAgICB9LFxuXG4gICAgc2V0R2VvSlNPTjogZnVuY3Rpb24oXykge1xuICAgICAgICB0aGlzLl9nZW9qc29uID0gXztcbiAgICAgICAgdGhpcy5jbGVhckxheWVycygpO1xuICAgICAgICB0aGlzLl9pbml0aWFsaXplKF8pO1xuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9LFxuXG4gICAgZ2V0R2VvSlNPTjogZnVuY3Rpb24oKSB7XG4gICAgICAgIHJldHVybiB0aGlzLl9nZW9qc29uO1xuICAgIH0sXG5cbiAgICBsb2FkVVJMOiBmdW5jdGlvbih1cmwpIHtcbiAgICAgICAgaWYgKHRoaXMuX3JlcXVlc3QgJiYgJ2Fib3J0JyBpbiB0aGlzLl9yZXF1ZXN0KSB0aGlzLl9yZXF1ZXN0LmFib3J0KCk7XG4gICAgICAgIHRoaXMuX3JlcXVlc3QgPSByZXF1ZXN0KHVybCwgTC5iaW5kKGZ1bmN0aW9uKGVyciwganNvbikge1xuICAgICAgICAgICAgdGhpcy5fcmVxdWVzdCA9IG51bGw7XG4gICAgICAgICAgICBpZiAoZXJyICYmIGVyci50eXBlICE9PSAnYWJvcnQnKSB7XG4gICAgICAgICAgICAgICAgdXRpbC5sb2coJ2NvdWxkIG5vdCBsb2FkIGZlYXR1cmVzIGF0ICcgKyB1cmwpO1xuICAgICAgICAgICAgICAgIHRoaXMuZmlyZSgnZXJyb3InLCB7ZXJyb3I6IGVycn0pO1xuICAgICAgICAgICAgfSBlbHNlIGlmIChqc29uKSB7XG4gICAgICAgICAgICAgICAgdGhpcy5zZXRHZW9KU09OKGpzb24pO1xuICAgICAgICAgICAgICAgIHRoaXMuZmlyZSgncmVhZHknKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSwgdGhpcykpO1xuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9LFxuXG4gICAgbG9hZElEOiBmdW5jdGlvbihpZCkge1xuICAgICAgICByZXR1cm4gdGhpcy5sb2FkVVJMKGZvcm1hdF91cmwoJy92NC8nICsgaWQgKyAnL2ZlYXR1cmVzLmpzb24nLCB0aGlzLm9wdGlvbnMuYWNjZXNzVG9rZW4pKTtcbiAgICB9LFxuXG4gICAgc2V0RmlsdGVyOiBmdW5jdGlvbihfKSB7XG4gICAgICAgIHRoaXMub3B0aW9ucy5maWx0ZXIgPSBfO1xuICAgICAgICBpZiAodGhpcy5fZ2VvanNvbikge1xuICAgICAgICAgICAgdGhpcy5jbGVhckxheWVycygpO1xuICAgICAgICAgICAgdGhpcy5faW5pdGlhbGl6ZSh0aGlzLl9nZW9qc29uKTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9LFxuXG4gICAgZ2V0RmlsdGVyOiBmdW5jdGlvbigpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMub3B0aW9ucy5maWx0ZXI7XG4gICAgfSxcblxuICAgIF9pbml0aWFsaXplOiBmdW5jdGlvbihqc29uKSB7XG4gICAgICAgIHZhciBmZWF0dXJlcyA9IEwuVXRpbC5pc0FycmF5KGpzb24pID8ganNvbiA6IGpzb24uZmVhdHVyZXMsXG4gICAgICAgICAgICBpLCBsZW47XG5cbiAgICAgICAgaWYgKGZlYXR1cmVzKSB7XG4gICAgICAgICAgICBmb3IgKGkgPSAwLCBsZW4gPSBmZWF0dXJlcy5sZW5ndGg7IGkgPCBsZW47IGkrKykge1xuICAgICAgICAgICAgICAgIC8vIE9ubHkgYWRkIHRoaXMgaWYgZ2VvbWV0cnkgb3IgZ2VvbWV0cmllcyBhcmUgc2V0IGFuZCBub3QgbnVsbFxuICAgICAgICAgICAgICAgIGlmIChmZWF0dXJlc1tpXS5nZW9tZXRyaWVzIHx8IGZlYXR1cmVzW2ldLmdlb21ldHJ5IHx8IGZlYXR1cmVzW2ldLmZlYXR1cmVzKSB7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuX2luaXRpYWxpemUoZmVhdHVyZXNbaV0pO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgfSBlbHNlIGlmICh0aGlzLm9wdGlvbnMuZmlsdGVyKGpzb24pKSB7XG5cbiAgICAgICAgICAgIHZhciBvcHRzID0ge2FjY2Vzc1Rva2VuOiB0aGlzLm9wdGlvbnMuYWNjZXNzVG9rZW59LFxuICAgICAgICAgICAgICAgIHBvaW50VG9MYXllciA9IHRoaXMub3B0aW9ucy5wb2ludFRvTGF5ZXIgfHwgZnVuY3Rpb24oZmVhdHVyZSwgbGF0bG9uKSB7XG4gICAgICAgICAgICAgICAgICByZXR1cm4gbWFya2VyLnN0eWxlKGZlYXR1cmUsIGxhdGxvbiwgb3B0cyk7XG4gICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICBsYXllciA9IEwuR2VvSlNPTi5nZW9tZXRyeVRvTGF5ZXIoanNvbiwge1xuICAgICAgICAgICAgICAgICAgICBwb2ludFRvTGF5ZXI6IHBvaW50VG9MYXllclxuICAgICAgICAgICAgICAgIH0pLFxuICAgICAgICAgICAgICAgIHBvcHVwSHRtbCA9IG1hcmtlci5jcmVhdGVQb3B1cChqc29uLCB0aGlzLm9wdGlvbnMuc2FuaXRpemVyKSxcbiAgICAgICAgICAgICAgICBzdHlsZSA9IHRoaXMub3B0aW9ucy5zdHlsZSxcbiAgICAgICAgICAgICAgICBkZWZhdWx0U3R5bGUgPSBzdHlsZSA9PT0gc2ltcGxlc3R5bGUuc3R5bGU7XG5cbiAgICAgICAgICAgIGlmIChzdHlsZSAmJiAnc2V0U3R5bGUnIGluIGxheWVyICYmXG4gICAgICAgICAgICAgICAgLy8gaWYgdGhlIHN0eWxlIG1ldGhvZCBpcyB0aGUgc2ltcGxlc3R5bGUgZGVmYXVsdCwgdGhlblxuICAgICAgICAgICAgICAgIC8vIG5ldmVyIHN0eWxlIEwuQ2lyY2xlIG9yIEwuQ2lyY2xlTWFya2VyIGJlY2F1c2VcbiAgICAgICAgICAgICAgICAvLyBzaW1wbGVzdHlsZSBoYXMgbm8gcnVsZXMgb3ZlciB0aGVtLCBvbmx5IG92ZXIgZ2VvbWV0cnlcbiAgICAgICAgICAgICAgICAvLyBwcmltaXRpdmVzIGRpcmVjdGx5IGZyb20gR2VvSlNPTlxuICAgICAgICAgICAgICAgICghKGRlZmF1bHRTdHlsZSAmJiAobGF5ZXIgaW5zdGFuY2VvZiBMLkNpcmNsZSB8fFxuICAgICAgICAgICAgICAgICAgbGF5ZXIgaW5zdGFuY2VvZiBMLkNpcmNsZU1hcmtlcikpKSkge1xuICAgICAgICAgICAgICAgIGlmICh0eXBlb2Ygc3R5bGUgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICAgICAgICAgICAgc3R5bGUgPSBzdHlsZShqc29uKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgbGF5ZXIuc2V0U3R5bGUoc3R5bGUpO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBsYXllci5mZWF0dXJlID0ganNvbjtcblxuICAgICAgICAgICAgaWYgKHBvcHVwSHRtbCkge1xuICAgICAgICAgICAgICAgIGxheWVyLmJpbmRQb3B1cChwb3B1cEh0bWwsIHRoaXMub3B0aW9ucy5wb3B1cE9wdGlvbnMpO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICB0aGlzLmFkZExheWVyKGxheWVyKTtcbiAgICAgICAgfVxuICAgIH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cy5GZWF0dXJlTGF5ZXIgPSBGZWF0dXJlTGF5ZXI7XG5cbm1vZHVsZS5leHBvcnRzLmZlYXR1cmVMYXllciA9IGZ1bmN0aW9uKF8sIG9wdGlvbnMpIHtcbiAgICByZXR1cm4gbmV3IEZlYXR1cmVMYXllcihfLCBvcHRpb25zKTtcbn07XG4iLCIndXNlIHN0cmljdCc7XG5cbnZhciBGZWVkYmFjayA9IEwuQ2xhc3MuZXh0ZW5kKHtcbiAgICBpbmNsdWRlczogTC5NaXhpbi5FdmVudHMsXG4gICAgZGF0YToge30sXG4gICAgcmVjb3JkOiBmdW5jdGlvbihkYXRhKSB7XG4gICAgICAgIEwuZXh0ZW5kKHRoaXMuZGF0YSwgZGF0YSk7XG4gICAgICAgIHRoaXMuZmlyZSgnY2hhbmdlJyk7XG4gICAgfVxufSk7XG5cbm1vZHVsZS5leHBvcnRzID0gbmV3IEZlZWRiYWNrKCk7XG4iLCIndXNlIHN0cmljdCc7XG5cbnZhciBjb25maWcgPSByZXF1aXJlKCcuL2NvbmZpZycpLFxuICAgIHZlcnNpb24gPSByZXF1aXJlKCcuLi9wYWNrYWdlLmpzb24nKS52ZXJzaW9uO1xuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKHBhdGgsIGFjY2Vzc1Rva2VuKSB7XG4gICAgYWNjZXNzVG9rZW4gPSBhY2Nlc3NUb2tlbiB8fCBMLm1hcGJveC5hY2Nlc3NUb2tlbjtcblxuICAgIGlmICghYWNjZXNzVG9rZW4gJiYgY29uZmlnLlJFUVVJUkVfQUNDRVNTX1RPS0VOKSB7XG4gICAgICAgIHRocm93IG5ldyBFcnJvcignQW4gQVBJIGFjY2VzcyB0b2tlbiBpcyByZXF1aXJlZCB0byB1c2UgTWFwYm94LmpzLiAnICtcbiAgICAgICAgICAgICdTZWUgaHR0cHM6Ly93d3cubWFwYm94LmNvbS9tYXBib3guanMvYXBpL3YnICsgdmVyc2lvbiArICcvYXBpLWFjY2Vzcy10b2tlbnMvJyk7XG4gICAgfVxuXG4gICAgdmFyIHVybCA9IChkb2N1bWVudC5sb2NhdGlvbi5wcm90b2NvbCA9PT0gJ2h0dHBzOicgfHwgY29uZmlnLkZPUkNFX0hUVFBTKSA/IGNvbmZpZy5IVFRQU19VUkwgOiBjb25maWcuSFRUUF9VUkw7XG4gICAgdXJsID0gdXJsLnJlcGxhY2UoL1xcL3Y0JC8sICcnKTtcbiAgICB1cmwgKz0gcGF0aDtcblxuICAgIGlmIChjb25maWcuUkVRVUlSRV9BQ0NFU1NfVE9LRU4pIHtcbiAgICAgICAgaWYgKGFjY2Vzc1Rva2VuWzBdID09PSAncycpIHtcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcignVXNlIGEgcHVibGljIGFjY2VzcyB0b2tlbiAocGsuKikgd2l0aCBNYXBib3guanMsIG5vdCBhIHNlY3JldCBhY2Nlc3MgdG9rZW4gKHNrLiopLiAnICtcbiAgICAgICAgICAgICAgICAnU2VlIGh0dHBzOi8vd3d3Lm1hcGJveC5jb20vbWFwYm94LmpzL2FwaS92JyArIHZlcnNpb24gKyAnL2FwaS1hY2Nlc3MtdG9rZW5zLycpO1xuICAgICAgICB9XG5cbiAgICAgICAgdXJsICs9IHVybC5pbmRleE9mKCc/JykgIT09IC0xID8gJyZhY2Nlc3NfdG9rZW49JyA6ICc/YWNjZXNzX3Rva2VuPSc7XG4gICAgICAgIHVybCArPSBhY2Nlc3NUb2tlbjtcbiAgICB9XG5cbiAgICByZXR1cm4gdXJsO1xufTtcblxubW9kdWxlLmV4cG9ydHMudGlsZUpTT04gPSBmdW5jdGlvbih1cmxPck1hcElELCBhY2Nlc3NUb2tlbikge1xuXG4gICAgaWYgKHVybE9yTWFwSUQuaW5kZXhPZignbWFwYm94Oi8vc3R5bGVzJykgPT09IDApIHtcbiAgICAgICAgdGhyb3cgbmV3IEVycm9yKCdTdHlsZXMgY3JlYXRlZCB3aXRoIE1hcGJveCBTdHVkaW8gbmVlZCB0byBiZSB1c2VkIHdpdGggJyArXG4gICAgICAgICAgICAnTC5tYXBib3guc3R5bGVMYXllciwgbm90IEwubWFwYm94LnRpbGVMYXllcicpO1xuICAgIH1cblxuICAgIGlmICh1cmxPck1hcElELmluZGV4T2YoJy8nKSAhPT0gLTEpXG4gICAgICAgIHJldHVybiB1cmxPck1hcElEO1xuXG4gICAgdmFyIHVybCA9IG1vZHVsZS5leHBvcnRzKCcvdjQvJyArIHVybE9yTWFwSUQgKyAnLmpzb24nLCBhY2Nlc3NUb2tlbik7XG5cbiAgICAvLyBUaWxlSlNPTiByZXF1ZXN0cyBuZWVkIGEgc2VjdXJlIGZsYWcgYXBwZW5kZWQgdG8gdGhlaXIgVVJMcyBzb1xuICAgIC8vIHRoYXQgdGhlIHNlcnZlciBrbm93cyB0byBzZW5kIFNTTC1pZmllZCByZXNvdXJjZSByZWZlcmVuY2VzLlxuICAgIGlmICh1cmwuaW5kZXhPZignaHR0cHMnKSA9PT0gMClcbiAgICAgICAgdXJsICs9ICcmc2VjdXJlJztcblxuICAgIHJldHVybiB1cmw7XG59O1xuXG5cbm1vZHVsZS5leHBvcnRzLnN0eWxlID0gZnVuY3Rpb24oc3R5bGVVUkwsIGFjY2Vzc1Rva2VuKSB7XG4gICAgaWYgKHN0eWxlVVJMLmluZGV4T2YoJ21hcGJveDovL3N0eWxlcy8nKSA9PT0gLTEpIHRocm93IG5ldyBFcnJvcignSW5jb3JyZWN0bHkgZm9ybWF0dGVkIE1hcGJveCBzdHlsZSBhdCAnICsgc3R5bGVVUkwpO1xuXG4gICAgdmFyIG93bmVySURTdHlsZSA9IHN0eWxlVVJMLnNwbGl0KCdtYXBib3g6Ly9zdHlsZXMvJylbMV07XG4gICAgdmFyIHVybCA9IG1vZHVsZS5leHBvcnRzKCcvc3R5bGVzL3YxLycgKyBvd25lcklEU3R5bGUsIGFjY2Vzc1Rva2VuKVxuICAgICAgICAucmVwbGFjZSgnaHR0cDovLycsICdodHRwczovLycpO1xuXG4gICAgcmV0dXJuIHVybDtcbn07XG4iLCIndXNlIHN0cmljdCc7XG5cbnZhciBpc0FycmF5ID0gcmVxdWlyZSgnaXNhcnJheScpLFxuICAgIHV0aWwgPSByZXF1aXJlKCcuL3V0aWwnKSxcbiAgICBmb3JtYXRfdXJsID0gcmVxdWlyZSgnLi9mb3JtYXRfdXJsJyksXG4gICAgZmVlZGJhY2sgPSByZXF1aXJlKCcuL2ZlZWRiYWNrJyksXG4gICAgcmVxdWVzdCA9IHJlcXVpcmUoJy4vcmVxdWVzdCcpO1xuXG4vLyBMb3ctbGV2ZWwgZ2VvY29kaW5nIGludGVyZmFjZSAtIHdyYXBzIHNwZWNpZmljIEFQSSBjYWxscyBhbmQgdGhlaXJcbi8vIHJldHVybiB2YWx1ZXMuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKHVybCwgb3B0aW9ucykge1xuICAgIGlmICghb3B0aW9ucykgb3B0aW9ucyA9IHt9O1xuICAgIHZhciBnZW9jb2RlciA9IHt9O1xuXG4gICAgdXRpbC5zdHJpY3QodXJsLCAnc3RyaW5nJyk7XG5cbiAgICBpZiAodXJsLmluZGV4T2YoJy8nKSA9PT0gLTEpIHtcbiAgICAgICAgdXJsID0gZm9ybWF0X3VybCgnL2dlb2NvZGluZy92NS8nICsgdXJsICsgJy97cXVlcnl9Lmpzb24nLCBvcHRpb25zLmFjY2Vzc1Rva2VuLCA1KTtcbiAgICB9XG5cbiAgICBmdW5jdGlvbiByb3VuZFRvKGxhdExuZywgcHJlY2lzaW9uKSB7XG4gICAgICAgIHZhciBtdWx0ID0gTWF0aC5wb3coMTAsIHByZWNpc2lvbik7XG4gICAgICAgIGxhdExuZy5sYXQgPSBNYXRoLnJvdW5kKGxhdExuZy5sYXQgKiBtdWx0KSAvIG11bHQ7XG4gICAgICAgIGxhdExuZy5sbmcgPSBNYXRoLnJvdW5kKGxhdExuZy5sbmcgKiBtdWx0KSAvIG11bHQ7XG4gICAgICAgIHJldHVybiBsYXRMbmc7XG4gICAgfVxuXG4gICAgZ2VvY29kZXIuZ2V0VVJMID0gZnVuY3Rpb24oKSB7XG4gICAgICAgIHJldHVybiB1cmw7XG4gICAgfTtcblxuICAgIGdlb2NvZGVyLnF1ZXJ5VVJMID0gZnVuY3Rpb24oXykge1xuICAgICAgICB2YXIgaXNPYmplY3QgPSAhKGlzQXJyYXkoXykgfHwgdHlwZW9mIF8gPT09ICdzdHJpbmcnKSxcbiAgICAgICAgICAgIHF1ZXJ5ID0gaXNPYmplY3QgPyBfLnF1ZXJ5IDogXztcblxuICAgICAgICBpZiAoaXNBcnJheShxdWVyeSkpIHtcbiAgICAgICAgICAgIHZhciBwYXJ0cyA9IFtdO1xuICAgICAgICAgICAgZm9yICh2YXIgaSA9IDA7IGkgPCBxdWVyeS5sZW5ndGg7IGkrKykge1xuICAgICAgICAgICAgICAgIHBhcnRzW2ldID0gZW5jb2RlVVJJQ29tcG9uZW50KHF1ZXJ5W2ldKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHF1ZXJ5ID0gcGFydHMuam9pbignOycpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgcXVlcnkgPSBlbmNvZGVVUklDb21wb25lbnQocXVlcnkpO1xuICAgICAgICB9XG5cbiAgICAgICAgZmVlZGJhY2sucmVjb3JkKHsgZ2VvY29kaW5nOiBxdWVyeSB9KTtcblxuICAgICAgICB2YXIgdXJsID0gTC5VdGlsLnRlbXBsYXRlKGdlb2NvZGVyLmdldFVSTCgpLCB7cXVlcnk6IHF1ZXJ5fSk7XG5cbiAgICAgICAgaWYgKGlzT2JqZWN0KSB7XG4gICAgICAgICAgICBpZiAoXy50eXBlcykge1xuICAgICAgICAgICAgICAgIGlmIChpc0FycmF5KF8udHlwZXMpKSB7XG4gICAgICAgICAgICAgICAgICAgIHVybCArPSAnJnR5cGVzPScgKyBfLnR5cGVzLmpvaW4oKTtcbiAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICB1cmwgKz0gJyZ0eXBlcz0nICsgXy50eXBlcztcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGlmIChfLmNvdW50cnkpIHtcbiAgICAgICAgICAgICAgICBpZiAoaXNBcnJheShfLmNvdW50cnkpKSB7XG4gICAgICAgICAgICAgICAgICAgIHVybCArPSAnJmNvdW50cnk9JyArIF8uY291bnRyeS5qb2luKCk7XG4gICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgdXJsICs9ICcmY291bnRyeT0nICsgXy5jb3VudHJ5O1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgaWYgKF8uYmJveCkge1xuICAgICAgICAgICAgICAgIGlmIChpc0FycmF5KF8uYmJveCkpIHtcbiAgICAgICAgICAgICAgICAgICAgdXJsICs9ICcmYmJveD0nICsgXy5iYm94LmpvaW4oKTtcbiAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICB1cmwgKz0gJyZiYm94PScgKyBfLmJib3g7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBpZiAoXy5wcm94aW1pdHkpIHtcbiAgICAgICAgICAgICAgICB2YXIgcHJveGltaXR5ID0gcm91bmRUbyhMLmxhdExuZyhfLnByb3hpbWl0eSksIDMpO1xuICAgICAgICAgICAgICAgIHVybCArPSAnJnByb3hpbWl0eT0nICsgcHJveGltaXR5LmxuZyArICcsJyArIHByb3hpbWl0eS5sYXQ7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGlmICh0eXBlb2YgXy5hdXRvY29tcGxldGUgPT09ICdib29sZWFuJykge1xuICAgICAgICAgICAgICAgIHVybCArPSAnJmF1dG9jb21wbGV0ZT0nICsgXy5hdXRvY29tcGxldGU7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdXJsO1xuICAgIH07XG5cbiAgICBnZW9jb2Rlci5xdWVyeSA9IGZ1bmN0aW9uKF8sIGNhbGxiYWNrKSB7XG4gICAgICAgIHV0aWwuc3RyaWN0KGNhbGxiYWNrLCAnZnVuY3Rpb24nKTtcblxuICAgICAgICByZXF1ZXN0KGdlb2NvZGVyLnF1ZXJ5VVJMKF8pLCBmdW5jdGlvbihlcnIsIGpzb24pIHtcbiAgICAgICAgICAgIGlmIChqc29uICYmIChqc29uLmxlbmd0aCB8fCBqc29uLmZlYXR1cmVzKSkge1xuICAgICAgICAgICAgICAgIHZhciByZXMgPSB7XG4gICAgICAgICAgICAgICAgICAgIHJlc3VsdHM6IGpzb25cbiAgICAgICAgICAgICAgICB9O1xuICAgICAgICAgICAgICAgIGlmIChqc29uLmZlYXR1cmVzICYmIGpzb24uZmVhdHVyZXMubGVuZ3RoKSB7XG4gICAgICAgICAgICAgICAgICAgIHJlcy5sYXRsbmcgPSBbXG4gICAgICAgICAgICAgICAgICAgICAgICBqc29uLmZlYXR1cmVzWzBdLmNlbnRlclsxXSxcbiAgICAgICAgICAgICAgICAgICAgICAgIGpzb24uZmVhdHVyZXNbMF0uY2VudGVyWzBdXTtcblxuICAgICAgICAgICAgICAgICAgICBpZiAoanNvbi5mZWF0dXJlc1swXS5iYm94KSB7XG4gICAgICAgICAgICAgICAgICAgICAgICByZXMuYm91bmRzID0ganNvbi5mZWF0dXJlc1swXS5iYm94O1xuICAgICAgICAgICAgICAgICAgICAgICAgcmVzLmxib3VuZHMgPSB1dGlsLmxib3VuZHMocmVzLmJvdW5kcyk7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgY2FsbGJhY2sobnVsbCwgcmVzKTtcbiAgICAgICAgICAgIH0gZWxzZSBjYWxsYmFjayhlcnIgfHwgdHJ1ZSk7XG4gICAgICAgIH0pO1xuXG4gICAgICAgIHJldHVybiBnZW9jb2RlcjtcbiAgICB9O1xuXG4gICAgLy8gYSByZXZlcnNlIGdlb2NvZGU6XG4gICAgLy9cbiAgICAvLyAgZ2VvY29kZXIucmV2ZXJzZVF1ZXJ5KFs4MCwgMjBdKVxuICAgIGdlb2NvZGVyLnJldmVyc2VRdWVyeSA9IGZ1bmN0aW9uKF8sIGNhbGxiYWNrKSB7XG4gICAgICAgIHZhciBxID0gJyc7XG5cbiAgICAgICAgLy8gc29ydCB0aHJvdWdoIGRpZmZlcmVudCB3YXlzIHBlb3BsZSByZXByZXNlbnQgbGF0IGFuZCBsb24gcGFpcnNcbiAgICAgICAgZnVuY3Rpb24gbm9ybWFsaXplKHgpIHtcbiAgICAgICAgICAgIHZhciBsYXRMbmc7XG4gICAgICAgICAgICBpZiAoeC5sYXQgIT09IHVuZGVmaW5lZCAmJiB4LmxuZyAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgICAgICAgbGF0TG5nID0gTC5sYXRMbmcoeC5sYXQsIHgubG5nKTtcbiAgICAgICAgICAgIH0gZWxzZSBpZiAoeC5sYXQgIT09IHVuZGVmaW5lZCAmJiB4LmxvbiAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgICAgICAgbGF0TG5nID0gTC5sYXRMbmcoeC5sYXQsIHgubG9uKTtcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgbGF0TG5nID0gTC5sYXRMbmcoeFsxXSwgeFswXSk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBsYXRMbmcgPSByb3VuZFRvKGxhdExuZywgNSk7XG4gICAgICAgICAgICByZXR1cm4gbGF0TG5nLmxuZyArICcsJyArIGxhdExuZy5sYXQ7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoXy5sZW5ndGggJiYgX1swXS5sZW5ndGgpIHtcbiAgICAgICAgICAgIGZvciAodmFyIGkgPSAwLCBwdHMgPSBbXTsgaSA8IF8ubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgICAgICAgICBwdHMucHVzaChub3JtYWxpemUoX1tpXSkpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgcSA9IHB0cy5qb2luKCc7Jyk7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICBxID0gbm9ybWFsaXplKF8pO1xuICAgICAgICB9XG5cbiAgICAgICAgcmVxdWVzdChnZW9jb2Rlci5xdWVyeVVSTChxKSwgZnVuY3Rpb24oZXJyLCBqc29uKSB7XG4gICAgICAgICAgICBjYWxsYmFjayhlcnIsIGpzb24pO1xuICAgICAgICB9KTtcblxuICAgICAgICByZXR1cm4gZ2VvY29kZXI7XG4gICAgfTtcblxuICAgIHJldHVybiBnZW9jb2Rlcjtcbn07XG4iLCIndXNlIHN0cmljdCc7XG5cbnZhciBnZW9jb2RlciA9IHJlcXVpcmUoJy4vZ2VvY29kZXInKSxcbiAgICB1dGlsID0gcmVxdWlyZSgnLi91dGlsJyk7XG5cbnZhciBHZW9jb2RlckNvbnRyb2wgPSBMLkNvbnRyb2wuZXh0ZW5kKHtcbiAgICBpbmNsdWRlczogTC5NaXhpbi5FdmVudHMsXG5cbiAgICBvcHRpb25zOiB7XG4gICAgICAgIHByb3hpbWl0eTogdHJ1ZSxcbiAgICAgICAgcG9zaXRpb246ICd0b3BsZWZ0JyxcbiAgICAgICAgcG9pbnRab29tOiAxNixcbiAgICAgICAga2VlcE9wZW46IGZhbHNlLFxuICAgICAgICBhdXRvY29tcGxldGU6IGZhbHNlLFxuICAgICAgICBxdWVyeU9wdGlvbnM6IHt9XG4gICAgfSxcblxuICAgIGluaXRpYWxpemU6IGZ1bmN0aW9uKF8sIG9wdGlvbnMpIHtcbiAgICAgICAgTC5VdGlsLnNldE9wdGlvbnModGhpcywgb3B0aW9ucyk7XG4gICAgICAgIHRoaXMuc2V0VVJMKF8pO1xuICAgICAgICB0aGlzLl91cGRhdGVTdWJtaXQgPSBMLmJpbmQodGhpcy5fdXBkYXRlU3VibWl0LCB0aGlzKTtcbiAgICAgICAgdGhpcy5fdXBkYXRlQXV0b2NvbXBsZXRlID0gTC5iaW5kKHRoaXMuX3VwZGF0ZUF1dG9jb21wbGV0ZSwgdGhpcyk7XG4gICAgICAgIHRoaXMuX2Nob29zZVJlc3VsdCA9IEwuYmluZCh0aGlzLl9jaG9vc2VSZXN1bHQsIHRoaXMpO1xuICAgIH0sXG5cbiAgICBzZXRVUkw6IGZ1bmN0aW9uKF8pIHtcbiAgICAgICAgdGhpcy5nZW9jb2RlciA9IGdlb2NvZGVyKF8sIHtcbiAgICAgICAgICAgIGFjY2Vzc1Rva2VuOiB0aGlzLm9wdGlvbnMuYWNjZXNzVG9rZW5cbiAgICAgICAgfSk7XG4gICAgICAgIHJldHVybiB0aGlzO1xuICAgIH0sXG5cbiAgICBnZXRVUkw6IGZ1bmN0aW9uKCkge1xuICAgICAgICByZXR1cm4gdGhpcy5nZW9jb2Rlci5nZXRVUkwoKTtcbiAgICB9LFxuXG4gICAgc2V0SUQ6IGZ1bmN0aW9uKF8pIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuc2V0VVJMKF8pO1xuICAgIH0sXG5cbiAgICBzZXRUaWxlSlNPTjogZnVuY3Rpb24oXykge1xuICAgICAgICByZXR1cm4gdGhpcy5zZXRVUkwoXy5nZW9jb2Rlcik7XG4gICAgfSxcblxuICAgIF90b2dnbGU6IGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgaWYgKGUpIEwuRG9tRXZlbnQuc3RvcChlKTtcbiAgICAgICAgaWYgKEwuRG9tVXRpbC5oYXNDbGFzcyh0aGlzLl9jb250YWluZXIsICdhY3RpdmUnKSkge1xuICAgICAgICAgICAgTC5Eb21VdGlsLnJlbW92ZUNsYXNzKHRoaXMuX2NvbnRhaW5lciwgJ2FjdGl2ZScpO1xuICAgICAgICAgICAgdGhpcy5fcmVzdWx0cy5pbm5lckhUTUwgPSAnJztcbiAgICAgICAgICAgIHRoaXMuX2lucHV0LmJsdXIoKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIEwuRG9tVXRpbC5hZGRDbGFzcyh0aGlzLl9jb250YWluZXIsICdhY3RpdmUnKTtcbiAgICAgICAgICAgIHRoaXMuX2lucHV0LmZvY3VzKCk7XG4gICAgICAgICAgICB0aGlzLl9pbnB1dC5zZWxlY3QoKTtcbiAgICAgICAgfVxuICAgIH0sXG5cbiAgICBfY2xvc2VJZk9wZW46IGZ1bmN0aW9uKCkge1xuICAgICAgICBpZiAoTC5Eb21VdGlsLmhhc0NsYXNzKHRoaXMuX2NvbnRhaW5lciwgJ2FjdGl2ZScpICYmXG4gICAgICAgICAgICAhdGhpcy5vcHRpb25zLmtlZXBPcGVuKSB7XG4gICAgICAgICAgICBMLkRvbVV0aWwucmVtb3ZlQ2xhc3ModGhpcy5fY29udGFpbmVyLCAnYWN0aXZlJyk7XG4gICAgICAgICAgICB0aGlzLl9yZXN1bHRzLmlubmVySFRNTCA9ICcnO1xuICAgICAgICAgICAgdGhpcy5faW5wdXQuYmx1cigpO1xuICAgICAgICB9XG4gICAgfSxcblxuICAgIG9uQWRkOiBmdW5jdGlvbihtYXApIHtcblxuICAgICAgICB2YXIgY29udGFpbmVyID0gTC5Eb21VdGlsLmNyZWF0ZSgnZGl2JywgJ2xlYWZsZXQtY29udHJvbC1tYXBib3gtZ2VvY29kZXIgbGVhZmxldC1iYXIgbGVhZmxldC1jb250cm9sJyksXG4gICAgICAgICAgICBsaW5rID0gTC5Eb21VdGlsLmNyZWF0ZSgnYScsICdsZWFmbGV0LWNvbnRyb2wtbWFwYm94LWdlb2NvZGVyLXRvZ2dsZSBtYXBib3gtaWNvbiBtYXBib3gtaWNvbi1nZW9jb2RlcicsIGNvbnRhaW5lciksXG4gICAgICAgICAgICByZXN1bHRzID0gTC5Eb21VdGlsLmNyZWF0ZSgnZGl2JywgJ2xlYWZsZXQtY29udHJvbC1tYXBib3gtZ2VvY29kZXItcmVzdWx0cycsIGNvbnRhaW5lciksXG4gICAgICAgICAgICB3cmFwID0gTC5Eb21VdGlsLmNyZWF0ZSgnZGl2JywgJ2xlYWZsZXQtY29udHJvbC1tYXBib3gtZ2VvY29kZXItd3JhcCcsIGNvbnRhaW5lciksXG4gICAgICAgICAgICBmb3JtID0gTC5Eb21VdGlsLmNyZWF0ZSgnZm9ybScsICdsZWFmbGV0LWNvbnRyb2wtbWFwYm94LWdlb2NvZGVyLWZvcm0nLCB3cmFwKSxcbiAgICAgICAgICAgIGlucHV0ID0gTC5Eb21VdGlsLmNyZWF0ZSgnaW5wdXQnLCAnJywgZm9ybSk7XG5cbiAgICAgICAgbGluay5ocmVmID0gJyMnO1xuICAgICAgICBsaW5rLmlubmVySFRNTCA9ICcmbmJzcDsnO1xuXG4gICAgICAgIGlucHV0LnR5cGUgPSAndGV4dCc7XG4gICAgICAgIGlucHV0LnNldEF0dHJpYnV0ZSgncGxhY2Vob2xkZXInLCAnU2VhcmNoJyk7XG5cbiAgICAgICAgTC5Eb21FdmVudC5hZGRMaXN0ZW5lcihmb3JtLCAnc3VibWl0JywgdGhpcy5fZ2VvY29kZSwgdGhpcyk7XG4gICAgICAgIEwuRG9tRXZlbnQuYWRkTGlzdGVuZXIoaW5wdXQsICdrZXl1cCcsIHRoaXMuX2F1dG9jb21wbGV0ZSwgdGhpcyk7XG4gICAgICAgIEwuRG9tRXZlbnQuZGlzYWJsZUNsaWNrUHJvcGFnYXRpb24oY29udGFpbmVyKTtcblxuICAgICAgICB0aGlzLl9tYXAgPSBtYXA7XG4gICAgICAgIHRoaXMuX3Jlc3VsdHMgPSByZXN1bHRzO1xuICAgICAgICB0aGlzLl9pbnB1dCA9IGlucHV0O1xuICAgICAgICB0aGlzLl9mb3JtID0gZm9ybTtcblxuICAgICAgICBpZiAodGhpcy5vcHRpb25zLmtlZXBPcGVuKSB7XG4gICAgICAgICAgICBMLkRvbVV0aWwuYWRkQ2xhc3MoY29udGFpbmVyLCAnYWN0aXZlJyk7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICB0aGlzLl9tYXAub24oJ2NsaWNrJywgdGhpcy5fY2xvc2VJZk9wZW4sIHRoaXMpO1xuICAgICAgICAgICAgTC5Eb21FdmVudC5hZGRMaXN0ZW5lcihsaW5rLCAnY2xpY2snLCB0aGlzLl90b2dnbGUsIHRoaXMpO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIGNvbnRhaW5lcjtcbiAgICB9LFxuXG4gICAgX3VwZGF0ZVN1Ym1pdDogZnVuY3Rpb24oZXJyLCByZXNwKSB7XG4gICAgICAgIEwuRG9tVXRpbC5yZW1vdmVDbGFzcyh0aGlzLl9jb250YWluZXIsICdzZWFyY2hpbmcnKTtcbiAgICAgICAgdGhpcy5fcmVzdWx0cy5pbm5lckhUTUwgPSAnJztcbiAgICAgICAgaWYgKGVyciB8fCAhcmVzcCkge1xuICAgICAgICAgICAgdGhpcy5maXJlKCdlcnJvcicsIHtlcnJvcjogZXJyfSk7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICB2YXIgZmVhdHVyZXMgPSBbXTtcbiAgICAgICAgICAgIGlmIChyZXNwLnJlc3VsdHMgJiYgcmVzcC5yZXN1bHRzLmZlYXR1cmVzKSB7XG4gICAgICAgICAgICAgICAgZmVhdHVyZXMgPSByZXNwLnJlc3VsdHMuZmVhdHVyZXM7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBpZiAoZmVhdHVyZXMubGVuZ3RoID09PSAxKSB7XG4gICAgICAgICAgICAgICAgdGhpcy5maXJlKCdhdXRvc2VsZWN0JywgeyBmZWF0dXJlOiBmZWF0dXJlc1swXSB9KTtcbiAgICAgICAgICAgICAgICB0aGlzLmZpcmUoJ2ZvdW5kJywge3Jlc3VsdHM6IHJlc3AucmVzdWx0c30pO1xuICAgICAgICAgICAgICAgIHRoaXMuX2Nob29zZVJlc3VsdChmZWF0dXJlc1swXSk7XG4gICAgICAgICAgICAgICAgdGhpcy5fY2xvc2VJZk9wZW4oKTtcbiAgICAgICAgICAgIH0gZWxzZSBpZiAoZmVhdHVyZXMubGVuZ3RoID4gMSkge1xuICAgICAgICAgICAgICAgIHRoaXMuZmlyZSgnZm91bmQnLCB7cmVzdWx0czogcmVzcC5yZXN1bHRzfSk7XG4gICAgICAgICAgICAgICAgdGhpcy5fZGlzcGxheVJlc3VsdHMoZmVhdHVyZXMpO1xuICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICB0aGlzLmZpcmUoJ25vdGZvdW5kJyk7XG4gICAgICAgICAgICAgICAgdGhpcy5fZGlzcGxheVJlc3VsdHMoZmVhdHVyZXMpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfSxcblxuICAgIF91cGRhdGVBdXRvY29tcGxldGU6IGZ1bmN0aW9uKGVyciwgcmVzcCkge1xuICAgICAgICB0aGlzLl9yZXN1bHRzLmlubmVySFRNTCA9ICcnO1xuICAgICAgICBpZiAoZXJyIHx8ICFyZXNwKSB7XG4gICAgICAgICAgICB0aGlzLmZpcmUoJ2Vycm9yJywge2Vycm9yOiBlcnJ9KTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHZhciBmZWF0dXJlcyA9IFtdO1xuICAgICAgICAgICAgaWYgKHJlc3AucmVzdWx0cyAmJiByZXNwLnJlc3VsdHMuZmVhdHVyZXMpIHtcbiAgICAgICAgICAgICAgICBmZWF0dXJlcyA9IHJlc3AucmVzdWx0cy5mZWF0dXJlcztcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGlmIChmZWF0dXJlcy5sZW5ndGgpIHtcbiAgICAgICAgICAgICAgICB0aGlzLmZpcmUoJ2ZvdW5kJywge3Jlc3VsdHM6IHJlc3AucmVzdWx0c30pO1xuICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICB0aGlzLmZpcmUoJ25vdGZvdW5kJyk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICB0aGlzLl9kaXNwbGF5UmVzdWx0cyhmZWF0dXJlcyk7XG4gICAgICAgIH1cbiAgICB9LFxuXG4gICAgX2Rpc3BsYXlSZXN1bHRzOiBmdW5jdGlvbihmZWF0dXJlcykge1xuICAgICAgICBmb3IgKHZhciBpID0gMCwgbCA9IE1hdGgubWluKGZlYXR1cmVzLmxlbmd0aCwgNSk7IGkgPCBsOyBpKyspIHtcbiAgICAgICAgICAgIHZhciBmZWF0dXJlID0gZmVhdHVyZXNbaV07XG4gICAgICAgICAgICB2YXIgbmFtZSA9IGZlYXR1cmUucGxhY2VfbmFtZTtcbiAgICAgICAgICAgIGlmICghbmFtZS5sZW5ndGgpIGNvbnRpbnVlO1xuXG4gICAgICAgICAgICB2YXIgciA9IEwuRG9tVXRpbC5jcmVhdGUoJ2EnLCAnJywgdGhpcy5fcmVzdWx0cyk7XG4gICAgICAgICAgICB2YXIgdGV4dCA9ICgnaW5uZXJUZXh0JyBpbiByKSA/ICdpbm5lclRleHQnIDogJ3RleHRDb250ZW50JztcbiAgICAgICAgICAgIHJbdGV4dF0gPSBuYW1lO1xuICAgICAgICAgICAgci5zZXRBdHRyaWJ1dGUoJ3RpdGxlJywgbmFtZSk7XG4gICAgICAgICAgICByLmhyZWYgPSAnIyc7XG5cbiAgICAgICAgICAgIChMLmJpbmQoZnVuY3Rpb24oZmVhdHVyZSkge1xuICAgICAgICAgICAgICAgIEwuRG9tRXZlbnQuYWRkTGlzdGVuZXIociwgJ2NsaWNrJywgZnVuY3Rpb24oZSkge1xuICAgICAgICAgICAgICAgICAgICB0aGlzLl9jaG9vc2VSZXN1bHQoZmVhdHVyZSk7XG4gICAgICAgICAgICAgICAgICAgIEwuRG9tRXZlbnQuc3RvcChlKTtcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5maXJlKCdzZWxlY3QnLCB7IGZlYXR1cmU6IGZlYXR1cmUgfSk7XG4gICAgICAgICAgICAgICAgfSwgdGhpcyk7XG4gICAgICAgICAgICB9LCB0aGlzKSkoZmVhdHVyZSk7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKGZlYXR1cmVzLmxlbmd0aCA+IDUpIHtcbiAgICAgICAgICAgIHZhciBvdXRvZiA9IEwuRG9tVXRpbC5jcmVhdGUoJ3NwYW4nLCAnJywgdGhpcy5fcmVzdWx0cyk7XG4gICAgICAgICAgICBvdXRvZi5pbm5lckhUTUwgPSAnVG9wIDUgb2YgJyArIGZlYXR1cmVzLmxlbmd0aCArICcgIHJlc3VsdHMnO1xuICAgICAgICB9XG4gICAgfSxcblxuICAgIF9jaG9vc2VSZXN1bHQ6IGZ1bmN0aW9uKHJlc3VsdCkge1xuICAgICAgICBpZiAocmVzdWx0LmJib3gpIHtcbiAgICAgICAgICAgIHRoaXMuX21hcC5maXRCb3VuZHModXRpbC5sYm91bmRzKHJlc3VsdC5iYm94KSk7XG4gICAgICAgIH0gZWxzZSBpZiAocmVzdWx0LmNlbnRlcikge1xuICAgICAgICAgICAgdGhpcy5fbWFwLnNldFZpZXcoW3Jlc3VsdC5jZW50ZXJbMV0sIHJlc3VsdC5jZW50ZXJbMF1dLCAodGhpcy5fbWFwLmdldFpvb20oKSA9PT0gdW5kZWZpbmVkKSA/XG4gICAgICAgICAgICAgICAgdGhpcy5vcHRpb25zLnBvaW50Wm9vbSA6XG4gICAgICAgICAgICAgICAgTWF0aC5tYXgodGhpcy5fbWFwLmdldFpvb20oKSwgdGhpcy5vcHRpb25zLnBvaW50Wm9vbSkpO1xuICAgICAgICB9XG4gICAgfSxcblxuICAgIF9nZW9jb2RlOiBmdW5jdGlvbihlKSB7XG4gICAgICAgIEwuRG9tRXZlbnQucHJldmVudERlZmF1bHQoZSk7XG4gICAgICAgIGlmICh0aGlzLl9pbnB1dC52YWx1ZSA9PT0gJycpIHJldHVybiB0aGlzLl91cGRhdGVTdWJtaXQoKTtcbiAgICAgICAgTC5Eb21VdGlsLmFkZENsYXNzKHRoaXMuX2NvbnRhaW5lciwgJ3NlYXJjaGluZycpO1xuICAgICAgICB0aGlzLmdlb2NvZGVyLnF1ZXJ5KEwuVXRpbC5leHRlbmQoe1xuICAgICAgICAgICAgcXVlcnk6IHRoaXMuX2lucHV0LnZhbHVlLFxuICAgICAgICAgICAgcHJveGltaXR5OiB0aGlzLm9wdGlvbnMucHJveGltaXR5ID8gdGhpcy5fbWFwLmdldENlbnRlcigpIDogZmFsc2VcbiAgICAgICAgfSwgdGhpcy5vcHRpb25zLnF1ZXJ5T3B0aW9ucyksIHRoaXMuX3VwZGF0ZVN1Ym1pdCk7XG4gICAgfSxcblxuICAgIF9hdXRvY29tcGxldGU6IGZ1bmN0aW9uKCkge1xuICAgICAgICBpZiAoIXRoaXMub3B0aW9ucy5hdXRvY29tcGxldGUpIHJldHVybjtcbiAgICAgICAgaWYgKHRoaXMuX2lucHV0LnZhbHVlID09PSAnJykgcmV0dXJuIHRoaXMuX3VwZGF0ZUF1dG9jb21wbGV0ZSgpO1xuICAgICAgICB0aGlzLmdlb2NvZGVyLnF1ZXJ5KEwuVXRpbC5leHRlbmQoe1xuICAgICAgICAgICAgcXVlcnk6IHRoaXMuX2lucHV0LnZhbHVlLFxuICAgICAgICAgICAgcHJveGltaXR5OiB0aGlzLm9wdGlvbnMucHJveGltaXR5ID8gdGhpcy5fbWFwLmdldENlbnRlcigpIDogZmFsc2VcbiAgICAgICAgfSwgdGhpcy5vcHRpb25zLnF1ZXJ5T3B0aW9ucyksIHRoaXMuX3VwZGF0ZUF1dG9jb21wbGV0ZSk7XG4gICAgfVxufSk7XG5cbm1vZHVsZS5leHBvcnRzLkdlb2NvZGVyQ29udHJvbCA9IEdlb2NvZGVyQ29udHJvbDtcblxubW9kdWxlLmV4cG9ydHMuZ2VvY29kZXJDb250cm9sID0gZnVuY3Rpb24oXywgb3B0aW9ucykge1xuICAgIHJldHVybiBuZXcgR2VvY29kZXJDb250cm9sKF8sIG9wdGlvbnMpO1xufTtcbiIsIid1c2Ugc3RyaWN0JztcblxuZnVuY3Rpb24gdXRmRGVjb2RlKGMpIHtcbiAgICBpZiAoYyA+PSA5MykgYy0tO1xuICAgIGlmIChjID49IDM1KSBjLS07XG4gICAgcmV0dXJuIGMgLSAzMjtcbn1cblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihkYXRhKSB7XG4gICAgcmV0dXJuIGZ1bmN0aW9uKHgsIHkpIHtcbiAgICAgICAgaWYgKCFkYXRhKSByZXR1cm47XG4gICAgICAgIHZhciBpZHggPSB1dGZEZWNvZGUoZGF0YS5ncmlkW3ldLmNoYXJDb2RlQXQoeCkpLFxuICAgICAgICAgICAga2V5ID0gZGF0YS5rZXlzW2lkeF07XG4gICAgICAgIHJldHVybiBkYXRhLmRhdGFba2V5XTtcbiAgICB9O1xufTtcbiIsIid1c2Ugc3RyaWN0JztcblxudmFyIHV0aWwgPSByZXF1aXJlKCcuL3V0aWwnKSxcbiAgICBNdXN0YWNoZSA9IHJlcXVpcmUoJ211c3RhY2hlJyk7XG5cbnZhciBHcmlkQ29udHJvbCA9IEwuQ29udHJvbC5leHRlbmQoe1xuXG4gICAgb3B0aW9uczoge1xuICAgICAgICBwaW5uYWJsZTogdHJ1ZSxcbiAgICAgICAgZm9sbG93OiBmYWxzZSxcbiAgICAgICAgc2FuaXRpemVyOiByZXF1aXJlKCdzYW5pdGl6ZS1jYWphJyksXG4gICAgICAgIHRvdWNoVGVhc2VyOiB0cnVlLFxuICAgICAgICBsb2NhdGlvbjogdHJ1ZVxuICAgIH0sXG5cbiAgICBfY3VycmVudENvbnRlbnQ6ICcnLFxuXG4gICAgLy8gcGlubmVkIG1lYW5zIHRoYXQgdGhpcyBjb250cm9sIGlzIG9uIGEgZmVhdHVyZSBhbmQgdGhlIHVzZXIgaGFzIGxpa2VseVxuICAgIC8vIGNsaWNrZWQuIHBpbm5lZCB3aWxsIG5vdCBiZWNvbWUgZmFsc2UgdW5sZXNzIHRoZSB1c2VyIGNsaWNrcyBvZmZcbiAgICAvLyBvZiB0aGUgZmVhdHVyZSBvbnRvIGFub3RoZXIgb3IgY2xpY2tzIHhcbiAgICBfcGlubmVkOiBmYWxzZSxcblxuICAgIGluaXRpYWxpemU6IGZ1bmN0aW9uKF8sIG9wdGlvbnMpIHtcbiAgICAgICAgTC5VdGlsLnNldE9wdGlvbnModGhpcywgb3B0aW9ucyk7XG4gICAgICAgIHV0aWwuc3RyaWN0X2luc3RhbmNlKF8sIEwuQ2xhc3MsICdMLm1hcGJveC5ncmlkTGF5ZXInKTtcbiAgICAgICAgdGhpcy5fbGF5ZXIgPSBfO1xuICAgIH0sXG5cbiAgICBzZXRUZW1wbGF0ZTogZnVuY3Rpb24odGVtcGxhdGUpIHtcbiAgICAgICAgdXRpbC5zdHJpY3QodGVtcGxhdGUsICdzdHJpbmcnKTtcbiAgICAgICAgdGhpcy5vcHRpb25zLnRlbXBsYXRlID0gdGVtcGxhdGU7XG4gICAgICAgIHJldHVybiB0aGlzO1xuICAgIH0sXG5cbiAgICBfdGVtcGxhdGU6IGZ1bmN0aW9uKGZvcm1hdCwgZGF0YSkge1xuICAgICAgICBpZiAoIWRhdGEpIHJldHVybjtcbiAgICAgICAgdmFyIHRlbXBsYXRlID0gdGhpcy5vcHRpb25zLnRlbXBsYXRlIHx8IHRoaXMuX2xheWVyLmdldFRpbGVKU09OKCkudGVtcGxhdGU7XG4gICAgICAgIGlmICh0ZW1wbGF0ZSkge1xuICAgICAgICAgICAgdmFyIGQgPSB7fTtcbiAgICAgICAgICAgIGRbJ19fJyArIGZvcm1hdCArICdfXyddID0gdHJ1ZTtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLm9wdGlvbnMuc2FuaXRpemVyKFxuICAgICAgICAgICAgICAgIE11c3RhY2hlLnRvX2h0bWwodGVtcGxhdGUsIEwuZXh0ZW5kKGQsIGRhdGEpKSk7XG4gICAgICAgIH1cbiAgICB9LFxuXG4gICAgLy8gY2hhbmdlIHRoZSBjb250ZW50IG9mIHRoZSB0b29sdGlwIEhUTUwgaWYgaXQgaGFzIGNoYW5nZWQsIG90aGVyd2lzZVxuICAgIC8vIG5vb3BcbiAgICBfc2hvdzogZnVuY3Rpb24oY29udGVudCwgbykge1xuICAgICAgICBpZiAoY29udGVudCA9PT0gdGhpcy5fY3VycmVudENvbnRlbnQpIHJldHVybjtcblxuICAgICAgICB0aGlzLl9jdXJyZW50Q29udGVudCA9IGNvbnRlbnQ7XG5cbiAgICAgICAgaWYgKHRoaXMub3B0aW9ucy5mb2xsb3cpIHtcbiAgICAgICAgICAgIHRoaXMuX3BvcHVwLnNldENvbnRlbnQoY29udGVudClcbiAgICAgICAgICAgICAgICAuc2V0TGF0TG5nKG8ubGF0TG5nKTtcbiAgICAgICAgICAgIGlmICh0aGlzLl9tYXAuX3BvcHVwICE9PSB0aGlzLl9wb3B1cCkgdGhpcy5fcG9wdXAub3Blbk9uKHRoaXMuX21hcCk7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICB0aGlzLl9jb250YWluZXIuc3R5bGUuZGlzcGxheSA9ICdibG9jayc7XG4gICAgICAgICAgICB0aGlzLl9jb250ZW50V3JhcHBlci5pbm5lckhUTUwgPSBjb250ZW50O1xuICAgICAgICB9XG4gICAgfSxcblxuICAgIGhpZGU6IGZ1bmN0aW9uKCkge1xuICAgICAgICB0aGlzLl9waW5uZWQgPSBmYWxzZTtcbiAgICAgICAgdGhpcy5fY3VycmVudENvbnRlbnQgPSAnJztcblxuICAgICAgICB0aGlzLl9tYXAuY2xvc2VQb3B1cCgpO1xuICAgICAgICB0aGlzLl9jb250YWluZXIuc3R5bGUuZGlzcGxheSA9ICdub25lJztcbiAgICAgICAgdGhpcy5fY29udGVudFdyYXBwZXIuaW5uZXJIVE1MID0gJyc7XG5cbiAgICAgICAgTC5Eb21VdGlsLnJlbW92ZUNsYXNzKHRoaXMuX2NvbnRhaW5lciwgJ2Nsb3NhYmxlJyk7XG5cbiAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfSxcblxuICAgIF9tb3VzZW92ZXI6IGZ1bmN0aW9uKG8pIHtcbiAgICAgICAgaWYgKG8uZGF0YSkge1xuICAgICAgICAgICAgTC5Eb21VdGlsLmFkZENsYXNzKHRoaXMuX21hcC5fY29udGFpbmVyLCAnbWFwLWNsaWNrYWJsZScpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgTC5Eb21VdGlsLnJlbW92ZUNsYXNzKHRoaXMuX21hcC5fY29udGFpbmVyLCAnbWFwLWNsaWNrYWJsZScpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHRoaXMuX3Bpbm5lZCkgcmV0dXJuO1xuXG4gICAgICAgIHZhciBjb250ZW50ID0gdGhpcy5fdGVtcGxhdGUoJ3RlYXNlcicsIG8uZGF0YSk7XG4gICAgICAgIGlmIChjb250ZW50KSB7XG4gICAgICAgICAgICB0aGlzLl9zaG93KGNvbnRlbnQsIG8pO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgdGhpcy5oaWRlKCk7XG4gICAgICAgIH1cbiAgICB9LFxuXG4gICAgX21vdXNlbW92ZTogZnVuY3Rpb24obykge1xuICAgICAgICBpZiAodGhpcy5fcGlubmVkKSByZXR1cm47XG4gICAgICAgIGlmICghdGhpcy5vcHRpb25zLmZvbGxvdykgcmV0dXJuO1xuXG4gICAgICAgIHRoaXMuX3BvcHVwLnNldExhdExuZyhvLmxhdExuZyk7XG4gICAgfSxcblxuICAgIF9uYXZpZ2F0ZVRvOiBmdW5jdGlvbih1cmwpIHtcbiAgICAgICAgd2luZG93LnRvcC5sb2NhdGlvbi5ocmVmID0gdXJsO1xuICAgIH0sXG5cbiAgICBfY2xpY2s6IGZ1bmN0aW9uKG8pIHtcblxuICAgICAgICB2YXIgbG9jYXRpb25fZm9ybWF0dGVkID0gdGhpcy5fdGVtcGxhdGUoJ2xvY2F0aW9uJywgby5kYXRhKTtcbiAgICAgICAgaWYgKHRoaXMub3B0aW9ucy5sb2NhdGlvbiAmJiBsb2NhdGlvbl9mb3JtYXR0ZWQgJiZcbiAgICAgICAgICAgIGxvY2F0aW9uX2Zvcm1hdHRlZC5zZWFyY2goL15odHRwcz86LykgPT09IDApIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLl9uYXZpZ2F0ZVRvKHRoaXMuX3RlbXBsYXRlKCdsb2NhdGlvbicsIG8uZGF0YSkpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCF0aGlzLm9wdGlvbnMucGlubmFibGUpIHJldHVybjtcblxuICAgICAgICB2YXIgY29udGVudCA9IHRoaXMuX3RlbXBsYXRlKCdmdWxsJywgby5kYXRhKTtcblxuICAgICAgICBpZiAoIWNvbnRlbnQgJiYgdGhpcy5vcHRpb25zLnRvdWNoVGVhc2VyICYmIEwuQnJvd3Nlci50b3VjaCkge1xuICAgICAgICAgICAgY29udGVudCA9IHRoaXMuX3RlbXBsYXRlKCd0ZWFzZXInLCBvLmRhdGEpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKGNvbnRlbnQpIHtcbiAgICAgICAgICAgIEwuRG9tVXRpbC5hZGRDbGFzcyh0aGlzLl9jb250YWluZXIsICdjbG9zYWJsZScpO1xuICAgICAgICAgICAgdGhpcy5fcGlubmVkID0gdHJ1ZTtcbiAgICAgICAgICAgIHRoaXMuX3Nob3coY29udGVudCwgbyk7XG4gICAgICAgIH0gZWxzZSBpZiAodGhpcy5fcGlubmVkKSB7XG4gICAgICAgICAgICBMLkRvbVV0aWwucmVtb3ZlQ2xhc3ModGhpcy5fY29udGFpbmVyLCAnY2xvc2FibGUnKTtcbiAgICAgICAgICAgIHRoaXMuX3Bpbm5lZCA9IGZhbHNlO1xuICAgICAgICAgICAgdGhpcy5oaWRlKCk7XG4gICAgICAgIH1cbiAgICB9LFxuXG4gICAgX29uUG9wdXBDbG9zZTogZnVuY3Rpb24oKSB7XG4gICAgICAgIHRoaXMuX2N1cnJlbnRDb250ZW50ID0gbnVsbDtcbiAgICAgICAgdGhpcy5fcGlubmVkID0gZmFsc2U7XG4gICAgfSxcblxuICAgIF9jcmVhdGVDbG9zZWJ1dHRvbjogZnVuY3Rpb24oY29udGFpbmVyLCBmbikge1xuICAgICAgICB2YXIgbGluayA9IEwuRG9tVXRpbC5jcmVhdGUoJ2EnLCAnY2xvc2UnLCBjb250YWluZXIpO1xuXG4gICAgICAgIGxpbmsuaW5uZXJIVE1MID0gJ2Nsb3NlJztcbiAgICAgICAgbGluay5ocmVmID0gJyMnO1xuICAgICAgICBsaW5rLnRpdGxlID0gJ2Nsb3NlJztcblxuICAgICAgICBMLkRvbUV2ZW50XG4gICAgICAgICAgICAub24obGluaywgJ2NsaWNrJywgTC5Eb21FdmVudC5zdG9wUHJvcGFnYXRpb24pXG4gICAgICAgICAgICAub24obGluaywgJ21vdXNlZG93bicsIEwuRG9tRXZlbnQuc3RvcFByb3BhZ2F0aW9uKVxuICAgICAgICAgICAgLm9uKGxpbmssICdkYmxjbGljaycsIEwuRG9tRXZlbnQuc3RvcFByb3BhZ2F0aW9uKVxuICAgICAgICAgICAgLm9uKGxpbmssICdjbGljaycsIEwuRG9tRXZlbnQucHJldmVudERlZmF1bHQpXG4gICAgICAgICAgICAub24obGluaywgJ2NsaWNrJywgZm4sIHRoaXMpO1xuXG4gICAgICAgIHJldHVybiBsaW5rO1xuICAgIH0sXG5cbiAgICBvbkFkZDogZnVuY3Rpb24obWFwKSB7XG4gICAgICAgIHRoaXMuX21hcCA9IG1hcDtcblxuICAgICAgICB2YXIgY2xhc3NOYW1lID0gJ2xlYWZsZXQtY29udHJvbC1ncmlkIG1hcC10b29sdGlwJyxcbiAgICAgICAgICAgIGNvbnRhaW5lciA9IEwuRG9tVXRpbC5jcmVhdGUoJ2RpdicsIGNsYXNzTmFtZSksXG4gICAgICAgICAgICBjb250ZW50V3JhcHBlciA9IEwuRG9tVXRpbC5jcmVhdGUoJ2RpdicsICdtYXAtdG9vbHRpcC1jb250ZW50Jyk7XG5cbiAgICAgICAgLy8gaGlkZSB0aGUgY29udGFpbmVyIGVsZW1lbnQgaW5pdGlhbGx5XG4gICAgICAgIGNvbnRhaW5lci5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnO1xuICAgICAgICB0aGlzLl9jcmVhdGVDbG9zZWJ1dHRvbihjb250YWluZXIsIHRoaXMuaGlkZSk7XG4gICAgICAgIGNvbnRhaW5lci5hcHBlbmRDaGlsZChjb250ZW50V3JhcHBlcik7XG5cbiAgICAgICAgdGhpcy5fY29udGVudFdyYXBwZXIgPSBjb250ZW50V3JhcHBlcjtcbiAgICAgICAgdGhpcy5fcG9wdXAgPSBuZXcgTC5Qb3B1cCh7IGF1dG9QYW46IGZhbHNlLCBjbG9zZU9uQ2xpY2s6IGZhbHNlIH0pO1xuXG4gICAgICAgIG1hcC5vbigncG9wdXBjbG9zZScsIHRoaXMuX29uUG9wdXBDbG9zZSwgdGhpcyk7XG5cbiAgICAgICAgTC5Eb21FdmVudFxuICAgICAgICAgICAgLmRpc2FibGVDbGlja1Byb3BhZ2F0aW9uKGNvbnRhaW5lcilcbiAgICAgICAgICAgIC8vIGFsbG93IHBlb3BsZSB0byBzY3JvbGwgdG9vbHRpcHMgd2l0aCBtb3VzZXdoZWVsXG4gICAgICAgICAgICAuYWRkTGlzdGVuZXIoY29udGFpbmVyLCAnbW91c2V3aGVlbCcsIEwuRG9tRXZlbnQuc3RvcFByb3BhZ2F0aW9uKTtcblxuICAgICAgICB0aGlzLl9sYXllclxuICAgICAgICAgICAgLm9uKCdtb3VzZW92ZXInLCB0aGlzLl9tb3VzZW92ZXIsIHRoaXMpXG4gICAgICAgICAgICAub24oJ21vdXNlbW92ZScsIHRoaXMuX21vdXNlbW92ZSwgdGhpcylcbiAgICAgICAgICAgIC5vbignY2xpY2snLCB0aGlzLl9jbGljaywgdGhpcyk7XG5cbiAgICAgICAgcmV0dXJuIGNvbnRhaW5lcjtcbiAgICB9LFxuXG4gICAgb25SZW1vdmU6IGZ1bmN0aW9uIChtYXApIHtcblxuICAgICAgICBtYXAub2ZmKCdwb3B1cGNsb3NlJywgdGhpcy5fb25Qb3B1cENsb3NlLCB0aGlzKTtcblxuICAgICAgICB0aGlzLl9sYXllclxuICAgICAgICAgICAgLm9mZignbW91c2VvdmVyJywgdGhpcy5fbW91c2VvdmVyLCB0aGlzKVxuICAgICAgICAgICAgLm9mZignbW91c2Vtb3ZlJywgdGhpcy5fbW91c2Vtb3ZlLCB0aGlzKVxuICAgICAgICAgICAgLm9mZignY2xpY2snLCB0aGlzLl9jbGljaywgdGhpcyk7XG4gICAgfVxufSk7XG5cbm1vZHVsZS5leHBvcnRzLkdyaWRDb250cm9sID0gR3JpZENvbnRyb2w7XG5cbm1vZHVsZS5leHBvcnRzLmdyaWRDb250cm9sID0gZnVuY3Rpb24oXywgb3B0aW9ucykge1xuICAgIHJldHVybiBuZXcgR3JpZENvbnRyb2woXywgb3B0aW9ucyk7XG59O1xuIiwiJ3VzZSBzdHJpY3QnO1xuXG52YXIgdXRpbCA9IHJlcXVpcmUoJy4vdXRpbCcpLFxuICAgIHJlcXVlc3QgPSByZXF1aXJlKCcuL3JlcXVlc3QnKSxcbiAgICBncmlkID0gcmVxdWlyZSgnLi9ncmlkJyk7XG5cbi8vIGZvcmtlZCBmcm9tIGRhbnplbC9MLlVURkdyaWRcbnZhciBHcmlkTGF5ZXIgPSBMLkxheWVyLmV4dGVuZCh7XG4gICAgaW5jbHVkZXM6IFtyZXF1aXJlKCcuL2xvYWRfdGlsZWpzb24nKV0sXG5cbiAgICBvcHRpb25zOiB7XG4gICAgICAgIHRlbXBsYXRlOiBmdW5jdGlvbigpIHsgcmV0dXJuICcnOyB9XG4gICAgfSxcblxuICAgIF9tb3VzZU9uOiBudWxsLFxuICAgIF90aWxlanNvbjoge30sXG4gICAgX2NhY2hlOiB7fSxcblxuICAgIGluaXRpYWxpemU6IGZ1bmN0aW9uKF8sIG9wdGlvbnMpIHtcbiAgICAgICAgTC5VdGlsLnNldE9wdGlvbnModGhpcywgb3B0aW9ucyk7XG4gICAgICAgIHRoaXMuX2xvYWRUaWxlSlNPTihfKTtcbiAgICB9LFxuXG4gICAgX3NldFRpbGVKU09OOiBmdW5jdGlvbihqc29uKSB7XG4gICAgICAgIHV0aWwuc3RyaWN0KGpzb24sICdvYmplY3QnKTtcblxuICAgICAgICBMLmV4dGVuZCh0aGlzLm9wdGlvbnMsIHtcbiAgICAgICAgICAgIGdyaWRzOiBqc29uLmdyaWRzLFxuICAgICAgICAgICAgbWluWm9vbToganNvbi5taW56b29tLFxuICAgICAgICAgICAgbWF4Wm9vbToganNvbi5tYXh6b29tLFxuICAgICAgICAgICAgYm91bmRzOiBqc29uLmJvdW5kcyAmJiB1dGlsLmxib3VuZHMoanNvbi5ib3VuZHMpXG4gICAgICAgIH0pO1xuXG4gICAgICAgIHRoaXMuX3RpbGVqc29uID0ganNvbjtcbiAgICAgICAgdGhpcy5fY2FjaGUgPSB7fTtcbiAgICAgICAgdGhpcy5fdXBkYXRlKCk7XG5cbiAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfSxcblxuICAgIGdldFRpbGVKU09OOiBmdW5jdGlvbigpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuX3RpbGVqc29uO1xuICAgIH0sXG5cbiAgICBhY3RpdmU6IGZ1bmN0aW9uKCkge1xuICAgICAgICByZXR1cm4gISEodGhpcy5fbWFwICYmIHRoaXMub3B0aW9ucy5ncmlkcyAmJiB0aGlzLm9wdGlvbnMuZ3JpZHMubGVuZ3RoKTtcbiAgICB9LFxuXG4gICAgb25BZGQ6IGZ1bmN0aW9uKG1hcCkge1xuICAgICAgICB0aGlzLl9tYXAgPSBtYXA7XG4gICAgICAgIHRoaXMuX3VwZGF0ZSgpO1xuXG4gICAgICAgIHRoaXMuX21hcFxuICAgICAgICAgICAgLm9uKCdjbGljaycsIHRoaXMuX2NsaWNrLCB0aGlzKVxuICAgICAgICAgICAgLm9uKCdtb3VzZW1vdmUnLCB0aGlzLl9tb3ZlLCB0aGlzKVxuICAgICAgICAgICAgLm9uKCdtb3ZlZW5kJywgdGhpcy5fdXBkYXRlLCB0aGlzKTtcbiAgICB9LFxuXG4gICAgb25SZW1vdmU6IGZ1bmN0aW9uKCkge1xuICAgICAgICB0aGlzLl9tYXBcbiAgICAgICAgICAgIC5vZmYoJ2NsaWNrJywgdGhpcy5fY2xpY2ssIHRoaXMpXG4gICAgICAgICAgICAub2ZmKCdtb3VzZW1vdmUnLCB0aGlzLl9tb3ZlLCB0aGlzKVxuICAgICAgICAgICAgLm9mZignbW92ZWVuZCcsIHRoaXMuX3VwZGF0ZSwgdGhpcyk7XG4gICAgfSxcblxuICAgIGdldERhdGE6IGZ1bmN0aW9uKGxhdGxuZywgY2FsbGJhY2spIHtcbiAgICAgICAgaWYgKCF0aGlzLmFjdGl2ZSgpKSByZXR1cm47XG5cbiAgICAgICAgdmFyIG1hcCA9IHRoaXMuX21hcCxcbiAgICAgICAgICAgIHBvaW50ID0gbWFwLnByb2plY3QobGF0bG5nLndyYXAoKSksXG4gICAgICAgICAgICB0aWxlU2l6ZSA9IDI1NixcbiAgICAgICAgICAgIHJlc29sdXRpb24gPSA0LFxuICAgICAgICAgICAgeCA9IE1hdGguZmxvb3IocG9pbnQueCAvIHRpbGVTaXplKSxcbiAgICAgICAgICAgIHkgPSBNYXRoLmZsb29yKHBvaW50LnkgLyB0aWxlU2l6ZSksXG4gICAgICAgICAgICBtYXggPSBtYXAub3B0aW9ucy5jcnMuc2NhbGUobWFwLmdldFpvb20oKSkgLyB0aWxlU2l6ZTtcblxuICAgICAgICB4ID0gKHggKyBtYXgpICUgbWF4O1xuICAgICAgICB5ID0gKHkgKyBtYXgpICUgbWF4O1xuXG4gICAgICAgIHRoaXMuX2dldFRpbGUobWFwLmdldFpvb20oKSwgeCwgeSwgZnVuY3Rpb24oZ3JpZCkge1xuICAgICAgICAgICAgdmFyIGdyaWRYID0gTWF0aC5mbG9vcigocG9pbnQueCAtICh4ICogdGlsZVNpemUpKSAvIHJlc29sdXRpb24pLFxuICAgICAgICAgICAgICAgIGdyaWRZID0gTWF0aC5mbG9vcigocG9pbnQueSAtICh5ICogdGlsZVNpemUpKSAvIHJlc29sdXRpb24pO1xuXG4gICAgICAgICAgICBjYWxsYmFjayhncmlkKGdyaWRYLCBncmlkWSkpO1xuICAgICAgICB9KTtcblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9LFxuXG4gICAgX2NsaWNrOiBmdW5jdGlvbihlKSB7XG4gICAgICAgIHRoaXMuZ2V0RGF0YShlLmxhdGxuZywgTC5iaW5kKGZ1bmN0aW9uKGRhdGEpIHtcbiAgICAgICAgICAgIHRoaXMuZmlyZSgnY2xpY2snLCB7XG4gICAgICAgICAgICAgICAgbGF0TG5nOiBlLmxhdGxuZyxcbiAgICAgICAgICAgICAgICBkYXRhOiBkYXRhXG4gICAgICAgICAgICB9KTtcbiAgICAgICAgfSwgdGhpcykpO1xuICAgIH0sXG5cbiAgICBfbW92ZTogZnVuY3Rpb24oZSkge1xuICAgICAgICB0aGlzLmdldERhdGEoZS5sYXRsbmcsIEwuYmluZChmdW5jdGlvbihkYXRhKSB7XG4gICAgICAgICAgICBpZiAoZGF0YSAhPT0gdGhpcy5fbW91c2VPbikge1xuICAgICAgICAgICAgICAgIGlmICh0aGlzLl9tb3VzZU9uKSB7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuZmlyZSgnbW91c2VvdXQnLCB7XG4gICAgICAgICAgICAgICAgICAgICAgICBsYXRMbmc6IGUubGF0bG5nLFxuICAgICAgICAgICAgICAgICAgICAgICAgZGF0YTogdGhpcy5fbW91c2VPblxuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICB0aGlzLmZpcmUoJ21vdXNlb3ZlcicsIHtcbiAgICAgICAgICAgICAgICAgICAgbGF0TG5nOiBlLmxhdGxuZyxcbiAgICAgICAgICAgICAgICAgICAgZGF0YTogZGF0YVxuICAgICAgICAgICAgICAgIH0pO1xuXG4gICAgICAgICAgICAgICAgdGhpcy5fbW91c2VPbiA9IGRhdGE7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIHRoaXMuZmlyZSgnbW91c2Vtb3ZlJywge1xuICAgICAgICAgICAgICAgICAgICBsYXRMbmc6IGUubGF0bG5nLFxuICAgICAgICAgICAgICAgICAgICBkYXRhOiBkYXRhXG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0sIHRoaXMpKTtcbiAgICB9LFxuXG4gICAgX2dldFRpbGVVUkw6IGZ1bmN0aW9uKHRpbGVQb2ludCkge1xuICAgICAgICB2YXIgdXJscyA9IHRoaXMub3B0aW9ucy5ncmlkcyxcbiAgICAgICAgICAgIGluZGV4ID0gKHRpbGVQb2ludC54ICsgdGlsZVBvaW50LnkpICUgdXJscy5sZW5ndGgsXG4gICAgICAgICAgICB1cmwgPSB1cmxzW2luZGV4XTtcblxuICAgICAgICByZXR1cm4gTC5VdGlsLnRlbXBsYXRlKHVybCwgdGlsZVBvaW50KTtcbiAgICB9LFxuXG4gICAgLy8gTG9hZCB1cCBhbGwgcmVxdWlyZWQganNvbiBncmlkIGZpbGVzXG4gICAgX3VwZGF0ZTogZnVuY3Rpb24oKSB7XG4gICAgICAgIGlmICghdGhpcy5hY3RpdmUoKSkgcmV0dXJuO1xuXG4gICAgICAgIHZhciBib3VuZHMgPSB0aGlzLl9tYXAuZ2V0UGl4ZWxCb3VuZHMoKSxcbiAgICAgICAgICAgIHogPSB0aGlzLl9tYXAuZ2V0Wm9vbSgpLFxuICAgICAgICAgICAgdGlsZVNpemUgPSAyNTY7XG5cbiAgICAgICAgaWYgKHogPiB0aGlzLm9wdGlvbnMubWF4Wm9vbSB8fCB6IDwgdGhpcy5vcHRpb25zLm1pblpvb20pIHJldHVybjtcblxuICAgICAgICB2YXIgdGlsZUJvdW5kcyA9IEwuYm91bmRzKFxuICAgICAgICAgICAgICAgIGJvdW5kcy5taW4uZGl2aWRlQnkodGlsZVNpemUpLl9mbG9vcigpLFxuICAgICAgICAgICAgICAgIGJvdW5kcy5tYXguZGl2aWRlQnkodGlsZVNpemUpLl9mbG9vcigpKSxcbiAgICAgICAgICAgIG1heCA9IHRoaXMuX21hcC5vcHRpb25zLmNycy5zY2FsZSh6KSAvIHRpbGVTaXplO1xuXG4gICAgICAgIGZvciAodmFyIHggPSB0aWxlQm91bmRzLm1pbi54OyB4IDw9IHRpbGVCb3VuZHMubWF4Lng7IHgrKykge1xuICAgICAgICAgICAgZm9yICh2YXIgeSA9IHRpbGVCb3VuZHMubWluLnk7IHkgPD0gdGlsZUJvdW5kcy5tYXgueTsgeSsrKSB7XG4gICAgICAgICAgICAgICAgLy8geCB3cmFwcGVkXG4gICAgICAgICAgICAgICAgdGhpcy5fZ2V0VGlsZSh6LCAoKHggJSBtYXgpICsgbWF4KSAlIG1heCwgKCh5ICUgbWF4KSArIG1heCkgJSBtYXgpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfSxcblxuICAgIF9nZXRUaWxlOiBmdW5jdGlvbih6LCB4LCB5LCBjYWxsYmFjaykge1xuICAgICAgICB2YXIga2V5ID0geiArICdfJyArIHggKyAnXycgKyB5LFxuICAgICAgICAgICAgdGlsZVBvaW50ID0gTC5wb2ludCh4LCB5KTtcblxuICAgICAgICB0aWxlUG9pbnQueiA9IHo7XG5cbiAgICAgICAgaWYgKCF0aGlzLl90aWxlU2hvdWxkQmVMb2FkZWQodGlsZVBvaW50KSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKGtleSBpbiB0aGlzLl9jYWNoZSkge1xuICAgICAgICAgICAgaWYgKCFjYWxsYmFjaykgcmV0dXJuO1xuXG4gICAgICAgICAgICBpZiAodHlwZW9mIHRoaXMuX2NhY2hlW2tleV0gPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICAgICAgICBjYWxsYmFjayh0aGlzLl9jYWNoZVtrZXldKTsgLy8gQWxyZWFkeSBsb2FkZWRcbiAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgdGhpcy5fY2FjaGVba2V5XS5wdXNoKGNhbGxiYWNrKTsgLy8gUGVuZGluZ1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLl9jYWNoZVtrZXldID0gW107XG5cbiAgICAgICAgaWYgKGNhbGxiYWNrKSB7XG4gICAgICAgICAgICB0aGlzLl9jYWNoZVtrZXldLnB1c2goY2FsbGJhY2spO1xuICAgICAgICB9XG5cbiAgICAgICAgcmVxdWVzdCh0aGlzLl9nZXRUaWxlVVJMKHRpbGVQb2ludCksIEwuYmluZChmdW5jdGlvbihlcnIsIGpzb24pIHtcbiAgICAgICAgICAgIHZhciBjYWxsYmFja3MgPSB0aGlzLl9jYWNoZVtrZXldO1xuICAgICAgICAgICAgdGhpcy5fY2FjaGVba2V5XSA9IGdyaWQoanNvbik7XG4gICAgICAgICAgICBmb3IgKHZhciBpID0gMDsgaSA8IGNhbGxiYWNrcy5sZW5ndGg7ICsraSkge1xuICAgICAgICAgICAgICAgIGNhbGxiYWNrc1tpXSh0aGlzLl9jYWNoZVtrZXldKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSwgdGhpcykpO1xuICAgIH0sXG5cbiAgICBfdGlsZVNob3VsZEJlTG9hZGVkOiBmdW5jdGlvbih0aWxlUG9pbnQpIHtcbiAgICAgICAgaWYgKHRpbGVQb2ludC56ID4gdGhpcy5vcHRpb25zLm1heFpvb20gfHwgdGlsZVBvaW50LnogPCB0aGlzLm9wdGlvbnMubWluWm9vbSkge1xuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHRoaXMub3B0aW9ucy5ib3VuZHMpIHtcbiAgICAgICAgICAgIHZhciB0aWxlU2l6ZSA9IDI1NixcbiAgICAgICAgICAgICAgICBud1BvaW50ID0gdGlsZVBvaW50Lm11bHRpcGx5QnkodGlsZVNpemUpLFxuICAgICAgICAgICAgICAgIHNlUG9pbnQgPSBud1BvaW50LmFkZChuZXcgTC5Qb2ludCh0aWxlU2l6ZSwgdGlsZVNpemUpKSxcbiAgICAgICAgICAgICAgICBudyA9IHRoaXMuX21hcC51bnByb2plY3QobndQb2ludCksXG4gICAgICAgICAgICAgICAgc2UgPSB0aGlzLl9tYXAudW5wcm9qZWN0KHNlUG9pbnQpLFxuICAgICAgICAgICAgICAgIGJvdW5kcyA9IG5ldyBMLkxhdExuZ0JvdW5kcyhbbncsIHNlXSk7XG5cbiAgICAgICAgICAgIGlmICghdGhpcy5vcHRpb25zLmJvdW5kcy5pbnRlcnNlY3RzKGJvdW5kcykpIHtcbiAgICAgICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICB9XG59KTtcblxubW9kdWxlLmV4cG9ydHMuR3JpZExheWVyID0gR3JpZExheWVyO1xuXG5tb2R1bGUuZXhwb3J0cy5ncmlkTGF5ZXIgPSBmdW5jdGlvbihfLCBvcHRpb25zKSB7XG4gICAgcmV0dXJuIG5ldyBHcmlkTGF5ZXIoXywgb3B0aW9ucyk7XG59O1xuIiwiJ3VzZSBzdHJpY3QnO1xuXG52YXIgTGVnZW5kQ29udHJvbCA9IEwuQ29udHJvbC5leHRlbmQoe1xuXG4gICAgb3B0aW9uczoge1xuICAgICAgICBwb3NpdGlvbjogJ2JvdHRvbXJpZ2h0JyxcbiAgICAgICAgc2FuaXRpemVyOiByZXF1aXJlKCdzYW5pdGl6ZS1jYWphJylcbiAgICB9LFxuXG4gICAgaW5pdGlhbGl6ZTogZnVuY3Rpb24ob3B0aW9ucykge1xuICAgICAgICBMLnNldE9wdGlvbnModGhpcywgb3B0aW9ucyk7XG4gICAgICAgIHRoaXMuX2xlZ2VuZHMgPSB7fTtcbiAgICB9LFxuXG4gICAgb25BZGQ6IGZ1bmN0aW9uKCkge1xuICAgICAgICB0aGlzLl9jb250YWluZXIgPSBMLkRvbVV0aWwuY3JlYXRlKCdkaXYnLCAnbWFwLWxlZ2VuZHMgd2F4LWxlZ2VuZHMnKTtcbiAgICAgICAgTC5Eb21FdmVudC5kaXNhYmxlQ2xpY2tQcm9wYWdhdGlvbih0aGlzLl9jb250YWluZXIpO1xuXG4gICAgICAgIHRoaXMuX3VwZGF0ZSgpO1xuXG4gICAgICAgIHJldHVybiB0aGlzLl9jb250YWluZXI7XG4gICAgfSxcblxuICAgIGFkZExlZ2VuZDogZnVuY3Rpb24odGV4dCkge1xuICAgICAgICBpZiAoIXRleHQpIHsgcmV0dXJuIHRoaXM7IH1cblxuICAgICAgICBpZiAoIXRoaXMuX2xlZ2VuZHNbdGV4dF0pIHtcbiAgICAgICAgICAgIHRoaXMuX2xlZ2VuZHNbdGV4dF0gPSAwO1xuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5fbGVnZW5kc1t0ZXh0XSsrO1xuICAgICAgICByZXR1cm4gdGhpcy5fdXBkYXRlKCk7XG4gICAgfSxcblxuICAgIHJlbW92ZUxlZ2VuZDogZnVuY3Rpb24odGV4dCkge1xuICAgICAgICBpZiAoIXRleHQpIHsgcmV0dXJuIHRoaXM7IH1cbiAgICAgICAgaWYgKHRoaXMuX2xlZ2VuZHNbdGV4dF0pIHRoaXMuX2xlZ2VuZHNbdGV4dF0tLTtcbiAgICAgICAgcmV0dXJuIHRoaXMuX3VwZGF0ZSgpO1xuICAgIH0sXG5cbiAgICBfdXBkYXRlOiBmdW5jdGlvbigpIHtcbiAgICAgICAgaWYgKCF0aGlzLl9tYXApIHsgcmV0dXJuIHRoaXM7IH1cblxuICAgICAgICB0aGlzLl9jb250YWluZXIuaW5uZXJIVE1MID0gJyc7XG4gICAgICAgIHZhciBoaWRlID0gJ25vbmUnO1xuXG4gICAgICAgIGZvciAodmFyIGkgaW4gdGhpcy5fbGVnZW5kcykge1xuICAgICAgICAgICAgaWYgKHRoaXMuX2xlZ2VuZHMuaGFzT3duUHJvcGVydHkoaSkgJiYgdGhpcy5fbGVnZW5kc1tpXSkge1xuICAgICAgICAgICAgICAgIHZhciBkaXYgPSBMLkRvbVV0aWwuY3JlYXRlKCdkaXYnLCAnbWFwLWxlZ2VuZCB3YXgtbGVnZW5kJywgdGhpcy5fY29udGFpbmVyKTtcbiAgICAgICAgICAgICAgICBkaXYuaW5uZXJIVE1MID0gdGhpcy5vcHRpb25zLnNhbml0aXplcihpKTtcbiAgICAgICAgICAgICAgICBoaWRlID0gJ2Jsb2NrJztcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGhpZGUgdGhlIGNvbnRyb2wgZW50aXJlbHkgdW5sZXNzIHRoZXJlIGlzIGF0IGxlYXN0IG9uZSBsZWdlbmQ7XG4gICAgICAgIC8vIG90aGVyd2lzZSB0aGVyZSB3aWxsIGJlIGEgc21hbGwgZ3JleSBibGVtaXNoIG9uIHRoZSBtYXAuXG4gICAgICAgIHRoaXMuX2NvbnRhaW5lci5zdHlsZS5kaXNwbGF5ID0gaGlkZTtcblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9XG59KTtcblxubW9kdWxlLmV4cG9ydHMuTGVnZW5kQ29udHJvbCA9IExlZ2VuZENvbnRyb2w7XG5cbm1vZHVsZS5leHBvcnRzLmxlZ2VuZENvbnRyb2wgPSBmdW5jdGlvbihvcHRpb25zKSB7XG4gICAgcmV0dXJuIG5ldyBMZWdlbmRDb250cm9sKG9wdGlvbnMpO1xufTtcbiIsIid1c2Ugc3RyaWN0JztcblxudmFyIHJlcXVlc3QgPSByZXF1aXJlKCcuL3JlcXVlc3QnKSxcbiAgICBmb3JtYXRfdXJsID0gcmVxdWlyZSgnLi9mb3JtYXRfdXJsJyksXG4gICAgdXRpbCA9IHJlcXVpcmUoJy4vdXRpbCcpO1xuXG5tb2R1bGUuZXhwb3J0cyA9IHtcbiAgICBfbG9hZFRpbGVKU09OOiBmdW5jdGlvbihfKSB7XG4gICAgICAgIGlmICh0eXBlb2YgXyA9PT0gJ3N0cmluZycpIHtcbiAgICAgICAgICAgIF8gPSBmb3JtYXRfdXJsLnRpbGVKU09OKF8sIHRoaXMub3B0aW9ucyAmJiB0aGlzLm9wdGlvbnMuYWNjZXNzVG9rZW4pO1xuICAgICAgICAgICAgcmVxdWVzdChfLCBMLmJpbmQoZnVuY3Rpb24oZXJyLCBqc29uKSB7XG4gICAgICAgICAgICAgICAgaWYgKGVycikge1xuICAgICAgICAgICAgICAgICAgICB1dGlsLmxvZygnY291bGQgbm90IGxvYWQgVGlsZUpTT04gYXQgJyArIF8pO1xuICAgICAgICAgICAgICAgICAgICB0aGlzLmZpcmUoJ2Vycm9yJywge2Vycm9yOiBlcnJ9KTtcbiAgICAgICAgICAgICAgICB9IGVsc2UgaWYgKGpzb24pIHtcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5fc2V0VGlsZUpTT04oanNvbik7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuZmlyZSgncmVhZHknKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9LCB0aGlzKSk7XG4gICAgICAgIH0gZWxzZSBpZiAoXyAmJiB0eXBlb2YgXyA9PT0gJ29iamVjdCcpIHtcbiAgICAgICAgICAgIHRoaXMuX3NldFRpbGVKU09OKF8pO1xuICAgICAgICB9XG4gICAgfVxufTtcbiIsIid1c2Ugc3RyaWN0JztcblxudmFyIHRpbGVMYXllciA9IHJlcXVpcmUoJy4vdGlsZV9sYXllcicpLnRpbGVMYXllcixcbiAgICBmZWF0dXJlTGF5ZXIgPSByZXF1aXJlKCcuL2ZlYXR1cmVfbGF5ZXInKS5mZWF0dXJlTGF5ZXIsXG4gICAgZ3JpZExheWVyID0gcmVxdWlyZSgnLi9ncmlkX2xheWVyJykuZ3JpZExheWVyLFxuICAgIGdyaWRDb250cm9sID0gcmVxdWlyZSgnLi9ncmlkX2NvbnRyb2wnKS5ncmlkQ29udHJvbCxcbiAgICBzaGFyZUNvbnRyb2wgPSByZXF1aXJlKCcuL3NoYXJlX2NvbnRyb2wnKS5zaGFyZUNvbnRyb2wsXG4gICAgbGVnZW5kQ29udHJvbCA9IHJlcXVpcmUoJy4vbGVnZW5kX2NvbnRyb2wnKS5sZWdlbmRDb250cm9sLFxuICAgIG1hcGJveExvZ29Db250cm9sID0gcmVxdWlyZSgnLi9tYXBib3hfbG9nbycpLm1hcGJveExvZ29Db250cm9sLFxuICAgIGZlZWRiYWNrID0gcmVxdWlyZSgnLi9mZWVkYmFjaycpO1xuXG5mdW5jdGlvbiB3aXRoQWNjZXNzVG9rZW4ob3B0aW9ucywgYWNjZXNzVG9rZW4pIHtcbiAgICBpZiAoIWFjY2Vzc1Rva2VuIHx8IG9wdGlvbnMuYWNjZXNzVG9rZW4pXG4gICAgICAgIHJldHVybiBvcHRpb25zO1xuICAgIHJldHVybiBMLmV4dGVuZCh7YWNjZXNzVG9rZW46IGFjY2Vzc1Rva2VufSwgb3B0aW9ucyk7XG59XG5cbnZhciBMTWFwID0gTC5NYXAuZXh0ZW5kKHtcbiAgICBpbmNsdWRlczogW3JlcXVpcmUoJy4vbG9hZF90aWxlanNvbicpXSxcblxuICAgIG9wdGlvbnM6IHtcbiAgICAgICAgdGlsZUxheWVyOiB7fSxcbiAgICAgICAgZmVhdHVyZUxheWVyOiB7fSxcbiAgICAgICAgZ3JpZExheWVyOiB7fSxcbiAgICAgICAgbGVnZW5kQ29udHJvbDoge30sXG4gICAgICAgIGdyaWRDb250cm9sOiB7fSxcbiAgICAgICAgc2hhcmVDb250cm9sOiBmYWxzZSxcbiAgICAgICAgc2FuaXRpemVyOiByZXF1aXJlKCdzYW5pdGl6ZS1jYWphJylcbiAgICB9LFxuXG4gICAgX3RpbGVqc29uOiB7fSxcblxuICAgIGluaXRpYWxpemU6IGZ1bmN0aW9uKGVsZW1lbnQsIF8sIG9wdGlvbnMpIHtcblxuICAgICAgICBMLk1hcC5wcm90b3R5cGUuaW5pdGlhbGl6ZS5jYWxsKHRoaXMsIGVsZW1lbnQsXG4gICAgICAgICAgICBMLmV4dGVuZCh7fSwgTC5NYXAucHJvdG90eXBlLm9wdGlvbnMsIG9wdGlvbnMpKTtcblxuICAgICAgICAvLyBEaXNhYmxlIHRoZSBkZWZhdWx0ICdMZWFmbGV0JyB0ZXh0XG4gICAgICAgIGlmICh0aGlzLmF0dHJpYnV0aW9uQ29udHJvbCkge1xuICAgICAgICAgICAgdGhpcy5hdHRyaWJ1dGlvbkNvbnRyb2wuc2V0UHJlZml4KCcnKTtcblxuICAgICAgICAgICAgdmFyIGNvbXBhY3QgPSB0aGlzLm9wdGlvbnMuYXR0cmlidXRpb25Db250cm9sLmNvbXBhY3Q7XG4gICAgICAgICAgICAvLyBTZXQgYSBjb21wYWN0IGRpc3BsYXkgaWYgbWFwIGNvbnRhaW5lciB3aWR0aCBpcyA8IDY0MCBvclxuICAgICAgICAgICAgLy8gY29tcGFjdCBpcyBzZXQgdG8gYHRydWVgIGluIGF0dHJpYnV0aW9uQ29udHJvbCBvcHRpb25zLlxuICAgICAgICAgICAgaWYgKGNvbXBhY3QgfHwgKGNvbXBhY3QgIT09IGZhbHNlICYmIHRoaXMuX2NvbnRhaW5lci5vZmZzZXRXaWR0aCA8PSA2NDApKSB7XG4gICAgICAgICAgICAgICAgTC5Eb21VdGlsLmFkZENsYXNzKHRoaXMuYXR0cmlidXRpb25Db250cm9sLl9jb250YWluZXIsICdsZWFmbGV0LWNvbXBhY3QtYXR0cmlidXRpb24nKTtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgaWYgKGNvbXBhY3QgPT09IHVuZGVmaW5lZCkge1xuICAgICAgICAgICAgICAgIHRoaXMub24oJ3Jlc2l6ZScsIGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgICAgICBpZiAodGhpcy5fY29udGFpbmVyLm9mZnNldFdpZHRoID4gNjQwKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBMLkRvbVV0aWwucmVtb3ZlQ2xhc3ModGhpcy5hdHRyaWJ1dGlvbkNvbnRyb2wuX2NvbnRhaW5lciwgJ2xlYWZsZXQtY29tcGFjdC1hdHRyaWJ1dGlvbicpO1xuICAgICAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICAgICAgTC5Eb21VdGlsLmFkZENsYXNzKHRoaXMuYXR0cmlidXRpb25Db250cm9sLl9jb250YWluZXIsICdsZWFmbGV0LWNvbXBhY3QtYXR0cmlidXRpb24nKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHRoaXMub3B0aW9ucy50aWxlTGF5ZXIpIHtcbiAgICAgICAgICAgIHRoaXMudGlsZUxheWVyID0gdGlsZUxheWVyKHVuZGVmaW5lZCxcbiAgICAgICAgICAgICAgICB3aXRoQWNjZXNzVG9rZW4odGhpcy5vcHRpb25zLnRpbGVMYXllciwgdGhpcy5vcHRpb25zLmFjY2Vzc1Rva2VuKSk7XG4gICAgICAgICAgICB0aGlzLmFkZExheWVyKHRoaXMudGlsZUxheWVyKTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmICh0aGlzLm9wdGlvbnMuZmVhdHVyZUxheWVyKSB7XG4gICAgICAgICAgICB0aGlzLmZlYXR1cmVMYXllciA9IGZlYXR1cmVMYXllcih1bmRlZmluZWQsXG4gICAgICAgICAgICAgICAgd2l0aEFjY2Vzc1Rva2VuKHRoaXMub3B0aW9ucy5mZWF0dXJlTGF5ZXIsIHRoaXMub3B0aW9ucy5hY2Nlc3NUb2tlbikpO1xuICAgICAgICAgICAgdGhpcy5hZGRMYXllcih0aGlzLmZlYXR1cmVMYXllcik7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAodGhpcy5vcHRpb25zLmdyaWRMYXllcikge1xuICAgICAgICAgICAgdGhpcy5ncmlkTGF5ZXIgPSBncmlkTGF5ZXIodW5kZWZpbmVkLFxuICAgICAgICAgICAgICAgIHdpdGhBY2Nlc3NUb2tlbih0aGlzLm9wdGlvbnMuZ3JpZExheWVyLCB0aGlzLm9wdGlvbnMuYWNjZXNzVG9rZW4pKTtcbiAgICAgICAgICAgIHRoaXMuYWRkTGF5ZXIodGhpcy5ncmlkTGF5ZXIpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHRoaXMub3B0aW9ucy5ncmlkTGF5ZXIgJiYgdGhpcy5vcHRpb25zLmdyaWRDb250cm9sKSB7XG4gICAgICAgICAgICB0aGlzLmdyaWRDb250cm9sID0gZ3JpZENvbnRyb2wodGhpcy5ncmlkTGF5ZXIsIHRoaXMub3B0aW9ucy5ncmlkQ29udHJvbCk7XG4gICAgICAgICAgICB0aGlzLmFkZENvbnRyb2wodGhpcy5ncmlkQ29udHJvbCk7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAodGhpcy5vcHRpb25zLmxlZ2VuZENvbnRyb2wpIHtcbiAgICAgICAgICAgIHRoaXMubGVnZW5kQ29udHJvbCA9IGxlZ2VuZENvbnRyb2wodGhpcy5vcHRpb25zLmxlZ2VuZENvbnRyb2wpO1xuICAgICAgICAgICAgdGhpcy5hZGRDb250cm9sKHRoaXMubGVnZW5kQ29udHJvbCk7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAodGhpcy5vcHRpb25zLnNoYXJlQ29udHJvbCkge1xuICAgICAgICAgICAgdGhpcy5zaGFyZUNvbnRyb2wgPSBzaGFyZUNvbnRyb2wodW5kZWZpbmVkLFxuICAgICAgICAgICAgICAgIHdpdGhBY2Nlc3NUb2tlbih0aGlzLm9wdGlvbnMuc2hhcmVDb250cm9sLCB0aGlzLm9wdGlvbnMuYWNjZXNzVG9rZW4pKTtcbiAgICAgICAgICAgIHRoaXMuYWRkQ29udHJvbCh0aGlzLnNoYXJlQ29udHJvbCk7XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLl9tYXBib3hMb2dvQ29udHJvbCA9IG1hcGJveExvZ29Db250cm9sKHRoaXMub3B0aW9ucy5tYXBib3hMb2dvQ29udHJvbCk7XG4gICAgICAgIHRoaXMuYWRkQ29udHJvbCh0aGlzLl9tYXBib3hMb2dvQ29udHJvbCk7XG5cbiAgICAgICAgdGhpcy5fbG9hZFRpbGVKU09OKF8pO1xuXG4gICAgICAgIHRoaXMub24oJ2xheWVyYWRkJywgdGhpcy5fb25MYXllckFkZCwgdGhpcylcbiAgICAgICAgICAgIC5vbignbGF5ZXJyZW1vdmUnLCB0aGlzLl9vbkxheWVyUmVtb3ZlLCB0aGlzKVxuICAgICAgICAgICAgLm9uKCdtb3ZlZW5kJywgdGhpcy5fdXBkYXRlTWFwRmVlZGJhY2tMaW5rLCB0aGlzKTtcblxuICAgICAgICB0aGlzLndoZW5SZWFkeShmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICBmZWVkYmFjay5vbignY2hhbmdlJywgdGhpcy5fdXBkYXRlTWFwRmVlZGJhY2tMaW5rLCB0aGlzKTtcbiAgICAgICAgfSk7XG5cbiAgICAgICAgdGhpcy5vbigndW5sb2FkJywgZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgZmVlZGJhY2sub2ZmKCdjaGFuZ2UnLCB0aGlzLl91cGRhdGVNYXBGZWVkYmFja0xpbmssIHRoaXMpO1xuICAgICAgICB9KTtcbiAgICB9LFxuXG4gICAgLy8gdXNlIGEgamF2YXNjcmlwdCBvYmplY3Qgb2YgdGlsZWpzb24gZGF0YSB0byBjb25maWd1cmUgdGhpcyBsYXllclxuICAgIF9zZXRUaWxlSlNPTjogZnVuY3Rpb24oXykge1xuICAgICAgICB0aGlzLl90aWxlanNvbiA9IF87XG4gICAgICAgIHRoaXMuX2luaXRpYWxpemUoXyk7XG4gICAgICAgIHJldHVybiB0aGlzO1xuICAgIH0sXG5cbiAgICBnZXRUaWxlSlNPTjogZnVuY3Rpb24oKSB7XG4gICAgICAgIHJldHVybiB0aGlzLl90aWxlanNvbjtcbiAgICB9LFxuXG4gICAgX2luaXRpYWxpemU6IGZ1bmN0aW9uKGpzb24pIHtcbiAgICAgICAgaWYgKHRoaXMudGlsZUxheWVyKSB7XG4gICAgICAgICAgICB0aGlzLnRpbGVMYXllci5fc2V0VGlsZUpTT04oanNvbik7XG4gICAgICAgICAgICB0aGlzLl91cGRhdGVMYXllcih0aGlzLnRpbGVMYXllcik7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAodGhpcy5mZWF0dXJlTGF5ZXIgJiYgIXRoaXMuZmVhdHVyZUxheWVyLmdldEdlb0pTT04oKSAmJiBqc29uLmRhdGEgJiYganNvbi5kYXRhWzBdKSB7XG4gICAgICAgICAgICB0aGlzLmZlYXR1cmVMYXllci5sb2FkVVJMKGpzb24uZGF0YVswXSk7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAodGhpcy5ncmlkTGF5ZXIpIHtcbiAgICAgICAgICAgIHRoaXMuZ3JpZExheWVyLl9zZXRUaWxlSlNPTihqc29uKTtcbiAgICAgICAgICAgIHRoaXMuX3VwZGF0ZUxheWVyKHRoaXMuZ3JpZExheWVyKTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmICh0aGlzLmxlZ2VuZENvbnRyb2wgJiYganNvbi5sZWdlbmQpIHtcbiAgICAgICAgICAgIHRoaXMubGVnZW5kQ29udHJvbC5hZGRMZWdlbmQoanNvbi5sZWdlbmQpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHRoaXMuc2hhcmVDb250cm9sKSB7XG4gICAgICAgICAgICB0aGlzLnNoYXJlQ29udHJvbC5fc2V0VGlsZUpTT04oanNvbik7XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLl9tYXBib3hMb2dvQ29udHJvbC5fc2V0VGlsZUpTT04oanNvbik7XG5cbiAgICAgICAgaWYgKCF0aGlzLl9sb2FkZWQgJiYganNvbi5jZW50ZXIpIHtcbiAgICAgICAgICAgIHZhciB6b29tID0gdGhpcy5nZXRab29tKCkgIT09IHVuZGVmaW5lZCA/IHRoaXMuZ2V0Wm9vbSgpIDoganNvbi5jZW50ZXJbMl0sXG4gICAgICAgICAgICAgICAgY2VudGVyID0gTC5sYXRMbmcoanNvbi5jZW50ZXJbMV0sIGpzb24uY2VudGVyWzBdKTtcblxuICAgICAgICAgICAgdGhpcy5zZXRWaWV3KGNlbnRlciwgem9vbSk7XG4gICAgICAgIH1cbiAgICB9LFxuXG4gICAgX3VwZGF0ZU1hcEZlZWRiYWNrTGluazogZnVuY3Rpb24oKSB7XG4gICAgICAgIGlmICghdGhpcy5fY29udHJvbENvbnRhaW5lci5nZXRFbGVtZW50c0J5Q2xhc3NOYW1lKSByZXR1cm47XG4gICAgICAgIHZhciBsaW5rID0gdGhpcy5fY29udHJvbENvbnRhaW5lci5nZXRFbGVtZW50c0J5Q2xhc3NOYW1lKCdtYXBib3gtaW1wcm92ZS1tYXAnKTtcbiAgICAgICAgaWYgKGxpbmsubGVuZ3RoICYmIHRoaXMuX2xvYWRlZCkge1xuICAgICAgICAgICAgdmFyIGNlbnRlciA9IHRoaXMuZ2V0Q2VudGVyKCkud3JhcCgpO1xuICAgICAgICAgICAgdmFyIHRpbGVqc29uID0gdGhpcy5fdGlsZWpzb24gfHwge307XG4gICAgICAgICAgICB2YXIgaWQgPSB0aWxlanNvbi5pZCB8fCAnJztcblxuICAgICAgICAgICAgdmFyIGhhc2ggPSAnIycgKyBpZCArICcvJyArXG4gICAgICAgICAgICAgICAgY2VudGVyLmxuZy50b0ZpeGVkKDMpICsgJy8nICtcbiAgICAgICAgICAgICAgICBjZW50ZXIubGF0LnRvRml4ZWQoMykgKyAnLycgK1xuICAgICAgICAgICAgICAgIHRoaXMuZ2V0Wm9vbSgpO1xuXG4gICAgICAgICAgICBmb3IgKHZhciBrZXkgaW4gZmVlZGJhY2suZGF0YSkge1xuICAgICAgICAgICAgICAgIGhhc2ggKz0gJy8nICsga2V5ICsgJz0nICsgZmVlZGJhY2suZGF0YVtrZXldO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBmb3IgKHZhciBpID0gMDsgaSA8IGxpbmsubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgICAgICAgICBsaW5rW2ldLmhhc2ggPSBoYXNoO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfSxcblxuICAgIF9vbkxheWVyQWRkOiBmdW5jdGlvbihlKSB7XG4gICAgICAgIGlmICgnb24nIGluIGUubGF5ZXIpIHtcbiAgICAgICAgICAgIGUubGF5ZXIub24oJ3JlYWR5JywgdGhpcy5fb25MYXllclJlYWR5LCB0aGlzKTtcbiAgICAgICAgfVxuICAgICAgICB3aW5kb3cuc2V0VGltZW91dChMLmJpbmQodGhpcy5fdXBkYXRlTWFwRmVlZGJhY2tMaW5rLCB0aGlzKSwgMCk7IC8vIFVwZGF0ZSBhZnRlciBhdHRyaWJ1dGlvbiBjb250cm9sIHJlc2V0cyB0aGUgSFRNTC5cbiAgICB9LFxuXG4gICAgX29uTGF5ZXJSZW1vdmU6IGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgaWYgKCdvbicgaW4gZS5sYXllcikge1xuICAgICAgICAgICAgZS5sYXllci5vZmYoJ3JlYWR5JywgdGhpcy5fb25MYXllclJlYWR5LCB0aGlzKTtcbiAgICAgICAgfVxuICAgICAgICB3aW5kb3cuc2V0VGltZW91dChMLmJpbmQodGhpcy5fdXBkYXRlTWFwRmVlZGJhY2tMaW5rLCB0aGlzKSwgMCk7IC8vIFVwZGF0ZSBhZnRlciBhdHRyaWJ1dGlvbiBjb250cm9sIHJlc2V0cyB0aGUgSFRNTC5cbiAgICB9LFxuXG4gICAgX29uTGF5ZXJSZWFkeTogZnVuY3Rpb24oZSkge1xuICAgICAgICB0aGlzLl91cGRhdGVMYXllcihlLnRhcmdldCk7XG4gICAgfSxcblxuICAgIF91cGRhdGVMYXllcjogZnVuY3Rpb24obGF5ZXIpIHtcbiAgICAgICAgaWYgKCFsYXllci5vcHRpb25zKSByZXR1cm47XG5cbiAgICAgICAgaWYgKHRoaXMuYXR0cmlidXRpb25Db250cm9sICYmIHRoaXMuX2xvYWRlZCAmJiBsYXllci5nZXRBdHRyaWJ1dGlvbikge1xuICAgICAgICAgICAgdGhpcy5hdHRyaWJ1dGlvbkNvbnRyb2wuYWRkQXR0cmlidXRpb24obGF5ZXIuZ2V0QXR0cmlidXRpb24oKSk7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoIShMLnN0YW1wKGxheWVyKSBpbiB0aGlzLl96b29tQm91bmRMYXllcnMpICYmXG4gICAgICAgICAgICAgICAgKGxheWVyLm9wdGlvbnMubWF4Wm9vbSB8fCBsYXllci5vcHRpb25zLm1pblpvb20pKSB7XG4gICAgICAgICAgICB0aGlzLl96b29tQm91bmRMYXllcnNbTC5zdGFtcChsYXllcildID0gbGF5ZXI7XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLl91cGRhdGVNYXBGZWVkYmFja0xpbmsoKTtcbiAgICAgICAgdGhpcy5fdXBkYXRlWm9vbUxldmVscygpO1xuICAgIH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cy5NYXAgPSBMTWFwO1xuXG5tb2R1bGUuZXhwb3J0cy5tYXAgPSBmdW5jdGlvbihlbGVtZW50LCBfLCBvcHRpb25zKSB7XG4gICAgcmV0dXJuIG5ldyBMTWFwKGVsZW1lbnQsIF8sIG9wdGlvbnMpO1xufTtcbiIsIid1c2Ugc3RyaWN0JztcblxudmFyIGdlb2NvZGVyQ29udHJvbCA9IHJlcXVpcmUoJy4vZ2VvY29kZXJfY29udHJvbCcpLFxuICAgIGdyaWRDb250cm9sID0gcmVxdWlyZSgnLi9ncmlkX2NvbnRyb2wnKSxcbiAgICBmZWF0dXJlTGF5ZXIgPSByZXF1aXJlKCcuL2ZlYXR1cmVfbGF5ZXInKSxcbiAgICBsZWdlbmRDb250cm9sID0gcmVxdWlyZSgnLi9sZWdlbmRfY29udHJvbCcpLFxuICAgIHNoYXJlQ29udHJvbCA9IHJlcXVpcmUoJy4vc2hhcmVfY29udHJvbCcpLFxuICAgIHRpbGVMYXllciA9IHJlcXVpcmUoJy4vdGlsZV9sYXllcicpLFxuICAgIG1hcCA9IHJlcXVpcmUoJy4vbWFwJyksXG4gICAgZ3JpZExheWVyID0gcmVxdWlyZSgnLi9ncmlkX2xheWVyJyksXG4gICAgc3R5bGVMYXllciA9IHJlcXVpcmUoJy4vc3R5bGVfbGF5ZXInKTtcblxuTC5tYXBib3ggPSBtb2R1bGUuZXhwb3J0cyA9IHtcbiAgICBWRVJTSU9OOiByZXF1aXJlKCcuLi9wYWNrYWdlLmpzb24nKS52ZXJzaW9uLFxuICAgIGdlb2NvZGVyOiByZXF1aXJlKCcuL2dlb2NvZGVyJyksXG4gICAgbWFya2VyOiByZXF1aXJlKCcuL21hcmtlcicpLFxuICAgIHNpbXBsZXN0eWxlOiByZXF1aXJlKCcuL3NpbXBsZXN0eWxlJyksXG4gICAgdGlsZUxheWVyOiB0aWxlTGF5ZXIudGlsZUxheWVyLFxuICAgIFRpbGVMYXllcjogdGlsZUxheWVyLlRpbGVMYXllcixcbiAgICBzdHlsZUxheWVyOiBzdHlsZUxheWVyLnN0eWxlTGF5ZXIsXG4gICAgU3R5bGVMYXllcjogc3R5bGVMYXllci5TdHlsZUxheWVyLFxuICAgIHNoYXJlQ29udHJvbDogc2hhcmVDb250cm9sLnNoYXJlQ29udHJvbCxcbiAgICBTaGFyZUNvbnRyb2w6IHNoYXJlQ29udHJvbC5TaGFyZUNvbnRyb2wsXG4gICAgbGVnZW5kQ29udHJvbDogbGVnZW5kQ29udHJvbC5sZWdlbmRDb250cm9sLFxuICAgIExlZ2VuZENvbnRyb2w6IGxlZ2VuZENvbnRyb2wuTGVnZW5kQ29udHJvbCxcbiAgICBnZW9jb2RlckNvbnRyb2w6IGdlb2NvZGVyQ29udHJvbC5nZW9jb2RlckNvbnRyb2wsXG4gICAgR2VvY29kZXJDb250cm9sOiBnZW9jb2RlckNvbnRyb2wuR2VvY29kZXJDb250cm9sLFxuICAgIGdyaWRDb250cm9sOiBncmlkQ29udHJvbC5ncmlkQ29udHJvbCxcbiAgICBHcmlkQ29udHJvbDogZ3JpZENvbnRyb2wuR3JpZENvbnRyb2wsXG4gICAgZ3JpZExheWVyOiBncmlkTGF5ZXIuZ3JpZExheWVyLFxuICAgIEdyaWRMYXllcjogZ3JpZExheWVyLkdyaWRMYXllcixcbiAgICBmZWF0dXJlTGF5ZXI6IGZlYXR1cmVMYXllci5mZWF0dXJlTGF5ZXIsXG4gICAgRmVhdHVyZUxheWVyOiBmZWF0dXJlTGF5ZXIuRmVhdHVyZUxheWVyLFxuICAgIG1hcDogbWFwLm1hcCxcbiAgICBNYXA6IG1hcC5NYXAsXG4gICAgY29uZmlnOiByZXF1aXJlKCcuL2NvbmZpZycpLFxuICAgIHNhbml0aXplOiByZXF1aXJlKCdzYW5pdGl6ZS1jYWphJyksXG4gICAgdGVtcGxhdGU6IHJlcXVpcmUoJ211c3RhY2hlJykudG9faHRtbCxcbiAgICBmZWVkYmFjazogcmVxdWlyZSgnLi9mZWVkYmFjaycpXG59O1xuXG5cbi8vIEhhcmRjb2RlIGltYWdlIHBhdGgsIGJlY2F1c2UgTGVhZmxldCdzIGF1dG9kZXRlY3Rpb25cbi8vIGZhaWxzLCBiZWNhdXNlIG1hcGJveC5qcyBpcyBub3QgbmFtZWQgbGVhZmxldC5qc1xud2luZG93LkwuSWNvbi5EZWZhdWx0LmltYWdlUGF0aCA9XG4gICAgLy8gRGV0ZWN0IGJhZC1uZXdzIHByb3RvY29scyBsaWtlIGZpbGU6Ly8gYW5kIGhhcmRjb2RlXG4gICAgLy8gdG8gaHR0cHMgaWYgdGhleSdyZSBkZXRlY3RlZC5cbiAgICAoKGRvY3VtZW50LmxvY2F0aW9uLnByb3RvY29sID09PSAnaHR0cHM6JyB8fFxuICAgIGRvY3VtZW50LmxvY2F0aW9uLnByb3RvY29sID09PSAnaHR0cDonKSA/ICcnIDogJ2h0dHBzOicpICtcbiAgICAnLy9hcGkudGlsZXMubWFwYm94LmNvbS9tYXBib3guanMvJyArICd2JyArXG4gICAgcmVxdWlyZSgnLi4vcGFja2FnZS5qc29uJykudmVyc2lvbiArICcvaW1hZ2VzLyc7XG4iLCIndXNlIHN0cmljdCc7XG5cbnZhciBNYXBib3hMb2dvQ29udHJvbCA9IEwuQ29udHJvbC5leHRlbmQoe1xuXG4gICAgb3B0aW9uczoge1xuICAgICAgICBwb3NpdGlvbjogJ2JvdHRvbWxlZnQnXG4gICAgfSxcblxuICAgIGluaXRpYWxpemU6IGZ1bmN0aW9uKG9wdGlvbnMpIHtcbiAgICAgICAgTC5zZXRPcHRpb25zKHRoaXMsIG9wdGlvbnMpO1xuICAgIH0sXG5cbiAgICBvbkFkZDogZnVuY3Rpb24oKSB7XG4gICAgICAgIHRoaXMuX2NvbnRhaW5lciA9IEwuRG9tVXRpbC5jcmVhdGUoJ2RpdicsICdtYXBib3gtbG9nbycpO1xuICAgICAgICByZXR1cm4gdGhpcy5fY29udGFpbmVyO1xuICAgIH0sXG5cbiAgICBfc2V0VGlsZUpTT046IGZ1bmN0aW9uKGpzb24pIHtcbiAgICAgICAgLy8gQ2hlY2sgaWYgYWNjb3VudCByZWZlcmVuY2VkIGJ5IHRoZSBhY2Nlc3NUb2tlblxuICAgICAgICAvLyBpcyBhc3Njb2NpYXRlZCB3aXRoIHRoZSBNYXBib3ggTG9nb1xuICAgICAgICAvLyBhcyBkZXRlcm1pbmVkIGJ5IG1hcGJveC1tYXBzLlxuICAgICAgICBpZiAoanNvbi5tYXBib3hfbG9nbykge1xuICAgICAgICAgICAgTC5Eb21VdGlsLmFkZENsYXNzKHRoaXMuX2NvbnRhaW5lciwgJ21hcGJveC1sb2dvLXRydWUnKTtcbiAgICAgICAgfVxuICAgIH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cy5NYXBib3hMb2dvQ29udHJvbCA9IE1hcGJveExvZ29Db250cm9sO1xuXG5tb2R1bGUuZXhwb3J0cy5tYXBib3hMb2dvQ29udHJvbCA9IGZ1bmN0aW9uKG9wdGlvbnMpIHtcbiAgICByZXR1cm4gbmV3IE1hcGJveExvZ29Db250cm9sKG9wdGlvbnMpO1xufTtcbiIsIid1c2Ugc3RyaWN0JztcblxudmFyIGZvcm1hdF91cmwgPSByZXF1aXJlKCcuL2Zvcm1hdF91cmwnKSxcbiAgICB1dGlsID0gcmVxdWlyZSgnLi91dGlsJyksXG4gICAgc2FuaXRpemUgPSByZXF1aXJlKCdzYW5pdGl6ZS1jYWphJyk7XG5cbi8vIG1hcGJveC1yZWxhdGVkIG1hcmtlcnMgZnVuY3Rpb25hbGl0eVxuLy8gcHJvdmlkZSBhbiBpY29uIGZyb20gbWFwYm94J3Mgc2ltcGxlLXN0eWxlIHNwZWMgYW5kIGhvc3RlZCBtYXJrZXJzXG4vLyBzZXJ2aWNlXG5mdW5jdGlvbiBpY29uKGZwLCBvcHRpb25zKSB7XG4gICAgZnAgPSBmcCB8fCB7fTtcblxuICAgIHZhciBzaXplcyA9IHtcbiAgICAgICAgICAgIHNtYWxsOiBbMjAsIDUwXSxcbiAgICAgICAgICAgIG1lZGl1bTogWzMwLCA3MF0sXG4gICAgICAgICAgICBsYXJnZTogWzM1LCA5MF1cbiAgICAgICAgfSxcbiAgICAgICAgc2l6ZSA9IGZwWydtYXJrZXItc2l6ZSddIHx8ICdtZWRpdW0nLFxuICAgICAgICBzeW1ib2wgPSAoJ21hcmtlci1zeW1ib2wnIGluIGZwICYmIGZwWydtYXJrZXItc3ltYm9sJ10gIT09ICcnKSA/ICctJyArIGZwWydtYXJrZXItc3ltYm9sJ10gOiAnJyxcbiAgICAgICAgY29sb3IgPSAoZnBbJ21hcmtlci1jb2xvciddIHx8ICc3ZTdlN2UnKS5yZXBsYWNlKCcjJywgJycpO1xuXG4gICAgcmV0dXJuIEwuaWNvbih7XG4gICAgICAgIGljb25Vcmw6IGZvcm1hdF91cmwoJy92NC9tYXJrZXIvJyArXG4gICAgICAgICAgICAncGluLScgKyBzaXplLmNoYXJBdCgwKSArIHN5bWJvbCArICcrJyArIGNvbG9yICtcbiAgICAgICAgICAgIC8vIGRldGVjdCBhbmQgdXNlIHJldGluYSBtYXJrZXJzLCB3aGljaCBhcmUgeDIgcmVzb2x1dGlvblxuICAgICAgICAgICAgKEwuQnJvd3Nlci5yZXRpbmEgPyAnQDJ4JyA6ICcnKSArICcucG5nJywgb3B0aW9ucyAmJiBvcHRpb25zLmFjY2Vzc1Rva2VuKSxcbiAgICAgICAgaWNvblNpemU6IHNpemVzW3NpemVdLFxuICAgICAgICBpY29uQW5jaG9yOiBbc2l6ZXNbc2l6ZV1bMF0gLyAyLCBzaXplc1tzaXplXVsxXSAvIDJdLFxuICAgICAgICBwb3B1cEFuY2hvcjogWzAsIC1zaXplc1tzaXplXVsxXSAvIDJdXG4gICAgfSk7XG59XG5cbi8vIGEgZmFjdG9yeSB0aGF0IHByb3ZpZGVzIG1hcmtlcnMgZm9yIExlYWZsZXQgZnJvbSBNYXBib3gnc1xuLy8gW3NpbXBsZS1zdHlsZSBzcGVjaWZpY2F0aW9uXShodHRwczovL2dpdGh1Yi5jb20vbWFwYm94L3NpbXBsZXN0eWxlLXNwZWMpXG4vLyBhbmQgW01hcmtlcnMgQVBJXShodHRwOi8vbWFwYm94LmNvbS9kZXZlbG9wZXJzL2FwaS8jbWFya2VycykuXG5mdW5jdGlvbiBzdHlsZShmLCBsYXRsb24sIG9wdGlvbnMpIHtcbiAgICByZXR1cm4gTC5tYXJrZXIobGF0bG9uLCB7XG4gICAgICAgIGljb246IGljb24oZi5wcm9wZXJ0aWVzLCBvcHRpb25zKSxcbiAgICAgICAgdGl0bGU6IHV0aWwuc3RyaXBfdGFncyhcbiAgICAgICAgICAgIHNhbml0aXplKChmLnByb3BlcnRpZXMgJiYgZi5wcm9wZXJ0aWVzLnRpdGxlKSB8fCAnJykpXG4gICAgfSk7XG59XG5cbi8vIFNhbml0aXplIGFuZCBmb3JtYXQgcHJvcGVydGllcyBvZiBhIEdlb0pTT04gRmVhdHVyZSBvYmplY3QgaW4gb3JkZXJcbi8vIHRvIGZvcm0gdGhlIEhUTUwgc3RyaW5nIHVzZWQgYXMgdGhlIGFyZ3VtZW50IGZvciBgTC5jcmVhdGVQb3B1cGBcbmZ1bmN0aW9uIGNyZWF0ZVBvcHVwKGYsIHNhbml0aXplcikge1xuICAgIGlmICghZiB8fCAhZi5wcm9wZXJ0aWVzKSByZXR1cm4gJyc7XG4gICAgdmFyIHBvcHVwID0gJyc7XG5cbiAgICBpZiAoZi5wcm9wZXJ0aWVzLnRpdGxlKSB7XG4gICAgICAgIHBvcHVwICs9ICc8ZGl2IGNsYXNzPVwibWFya2VyLXRpdGxlXCI+JyArIGYucHJvcGVydGllcy50aXRsZSArICc8L2Rpdj4nO1xuICAgIH1cblxuICAgIGlmIChmLnByb3BlcnRpZXMuZGVzY3JpcHRpb24pIHtcbiAgICAgICAgcG9wdXAgKz0gJzxkaXYgY2xhc3M9XCJtYXJrZXItZGVzY3JpcHRpb25cIj4nICsgZi5wcm9wZXJ0aWVzLmRlc2NyaXB0aW9uICsgJzwvZGl2Pic7XG4gICAgfVxuXG4gICAgcmV0dXJuIChzYW5pdGl6ZXIgfHwgc2FuaXRpemUpKHBvcHVwKTtcbn1cblxubW9kdWxlLmV4cG9ydHMgPSB7XG4gICAgaWNvbjogaWNvbixcbiAgICBzdHlsZTogc3R5bGUsXG4gICAgY3JlYXRlUG9wdXA6IGNyZWF0ZVBvcHVwXG59O1xuIiwiJ3VzZSBzdHJpY3QnO1xuXG52YXIgY29yc2xpdGUgPSByZXF1aXJlKCdjb3JzbGl0ZScpLFxuICAgIHN0cmljdCA9IHJlcXVpcmUoJy4vdXRpbCcpLnN0cmljdCxcbiAgICBjb25maWcgPSByZXF1aXJlKCcuL2NvbmZpZycpO1xuXG52YXIgcHJvdG9jb2wgPSAvXihodHRwcz86KT8oPz1cXC9cXC8oLnxhcGkpXFwudGlsZXNcXC5tYXBib3hcXC5jb21cXC8pLztcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbih1cmwsIGNhbGxiYWNrKSB7XG4gICAgc3RyaWN0KHVybCwgJ3N0cmluZycpO1xuICAgIHN0cmljdChjYWxsYmFjaywgJ2Z1bmN0aW9uJyk7XG5cbiAgICB1cmwgPSB1cmwucmVwbGFjZShwcm90b2NvbCwgZnVuY3Rpb24obWF0Y2gsIHByb3RvY29sKSB7XG4gICAgICAgIGlmICghKCd3aXRoQ3JlZGVudGlhbHMnIGluIG5ldyB3aW5kb3cuWE1MSHR0cFJlcXVlc3QoKSkpIHtcbiAgICAgICAgICAgIC8vIFhEb21haW5SZXF1ZXN0IGluIHVzZTsgZG9lc24ndCBzdXBwb3J0IGNyb3NzLXByb3RvY29sIHJlcXVlc3RzXG4gICAgICAgICAgICByZXR1cm4gZG9jdW1lbnQubG9jYXRpb24ucHJvdG9jb2w7XG4gICAgICAgIH0gZWxzZSBpZiAocHJvdG9jb2wgPT09ICdodHRwczonIHx8IGRvY3VtZW50LmxvY2F0aW9uLnByb3RvY29sID09PSAnaHR0cHM6JyB8fCBjb25maWcuRk9SQ0VfSFRUUFMpIHtcbiAgICAgICAgICAgIHJldHVybiAnaHR0cHM6JztcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHJldHVybiAnaHR0cDonO1xuICAgICAgICB9XG4gICAgfSk7XG5cbiAgICBmdW5jdGlvbiBvbmxvYWQoZXJyLCByZXNwKSB7XG4gICAgICAgIGlmICghZXJyICYmIHJlc3ApIHtcbiAgICAgICAgICAgIHJlc3AgPSBKU09OLnBhcnNlKHJlc3AucmVzcG9uc2VUZXh0KTtcbiAgICAgICAgfVxuICAgICAgICBjYWxsYmFjayhlcnIsIHJlc3ApO1xuICAgIH1cblxuICAgIHJldHVybiBjb3JzbGl0ZSh1cmwsIG9ubG9hZCk7XG59O1xuIiwiJ3VzZSBzdHJpY3QnO1xuXG52YXIgZm9ybWF0X3VybCA9IHJlcXVpcmUoJy4vZm9ybWF0X3VybCcpO1xuXG52YXIgU2hhcmVDb250cm9sID0gTC5Db250cm9sLmV4dGVuZCh7XG4gICAgaW5jbHVkZXM6IFtyZXF1aXJlKCcuL2xvYWRfdGlsZWpzb24nKV0sXG5cbiAgICBvcHRpb25zOiB7XG4gICAgICAgIHBvc2l0aW9uOiAndG9wbGVmdCcsXG4gICAgICAgIHVybDogJydcbiAgICB9LFxuXG4gICAgaW5pdGlhbGl6ZTogZnVuY3Rpb24oXywgb3B0aW9ucykge1xuICAgICAgICBMLnNldE9wdGlvbnModGhpcywgb3B0aW9ucyk7XG4gICAgICAgIHRoaXMuX2xvYWRUaWxlSlNPTihfKTtcbiAgICB9LFxuXG4gICAgX3NldFRpbGVKU09OOiBmdW5jdGlvbihqc29uKSB7XG4gICAgICAgIHRoaXMuX3RpbGVqc29uID0ganNvbjtcbiAgICB9LFxuXG4gICAgb25BZGQ6IGZ1bmN0aW9uKG1hcCkge1xuICAgICAgICB0aGlzLl9tYXAgPSBtYXA7XG5cbiAgICAgICAgdmFyIGNvbnRhaW5lciA9IEwuRG9tVXRpbC5jcmVhdGUoJ2RpdicsICdsZWFmbGV0LWNvbnRyb2wtbWFwYm94LXNoYXJlIGxlYWZsZXQtYmFyJyk7XG4gICAgICAgIHZhciBsaW5rID0gTC5Eb21VdGlsLmNyZWF0ZSgnYScsICdtYXBib3gtc2hhcmUgbWFwYm94LWljb24gbWFwYm94LWljb24tc2hhcmUnLCBjb250YWluZXIpO1xuICAgICAgICBsaW5rLmhyZWYgPSAnIyc7XG5cbiAgICAgICAgdGhpcy5fbW9kYWwgPSBMLkRvbVV0aWwuY3JlYXRlKCdkaXYnLCAnbWFwYm94LW1vZGFsJywgdGhpcy5fbWFwLl9jb250YWluZXIpO1xuICAgICAgICB0aGlzLl9tYXNrID0gTC5Eb21VdGlsLmNyZWF0ZSgnZGl2JywgJ21hcGJveC1tb2RhbC1tYXNrJywgdGhpcy5fbW9kYWwpO1xuICAgICAgICB0aGlzLl9jb250ZW50ID0gTC5Eb21VdGlsLmNyZWF0ZSgnZGl2JywgJ21hcGJveC1tb2RhbC1jb250ZW50JywgdGhpcy5fbW9kYWwpO1xuXG4gICAgICAgIEwuRG9tRXZlbnQuYWRkTGlzdGVuZXIobGluaywgJ2NsaWNrJywgdGhpcy5fc2hhcmVDbGljaywgdGhpcyk7XG4gICAgICAgIEwuRG9tRXZlbnQuZGlzYWJsZUNsaWNrUHJvcGFnYXRpb24oY29udGFpbmVyKTtcblxuICAgICAgICB0aGlzLl9tYXAub24oJ21vdXNlZG93bicsIHRoaXMuX2NsaWNrT3V0LCB0aGlzKTtcblxuICAgICAgICByZXR1cm4gY29udGFpbmVyO1xuICAgIH0sXG5cbiAgICBfY2xpY2tPdXQ6IGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgaWYgKHRoaXMuX3NoYXJpbmcpIHtcbiAgICAgICAgICAgIEwuRG9tRXZlbnQucHJldmVudERlZmF1bHQoZSk7XG4gICAgICAgICAgICBMLkRvbVV0aWwucmVtb3ZlQ2xhc3ModGhpcy5fbW9kYWwsICdhY3RpdmUnKTtcbiAgICAgICAgICAgIHRoaXMuX2NvbnRlbnQuaW5uZXJIVE1MID0gJyc7XG4gICAgICAgICAgICB0aGlzLl9zaGFyaW5nID0gbnVsbDtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuICAgIH0sXG5cbiAgICBfc2hhcmVDbGljazogZnVuY3Rpb24oZSkge1xuICAgICAgICBMLkRvbUV2ZW50LnN0b3AoZSk7XG4gICAgICAgIGlmICh0aGlzLl9zaGFyaW5nKSByZXR1cm4gdGhpcy5fY2xpY2tPdXQoZSk7XG5cbiAgICAgICAgdmFyIHRpbGVqc29uID0gdGhpcy5fdGlsZWpzb24gfHwgdGhpcy5fbWFwLl90aWxlanNvbiB8fCB7fSxcbiAgICAgICAgICAgIHVybCA9IGVuY29kZVVSSUNvbXBvbmVudCh0aGlzLm9wdGlvbnMudXJsIHx8IHRpbGVqc29uLndlYnBhZ2UgfHwgd2luZG93LmxvY2F0aW9uKSxcbiAgICAgICAgICAgIG5hbWUgPSBlbmNvZGVVUklDb21wb25lbnQodGlsZWpzb24ubmFtZSksXG4gICAgICAgICAgICBpbWFnZSA9IGZvcm1hdF91cmwoJy92NC8nICsgdGlsZWpzb24uaWQgKyAnLycgKyB0aGlzLl9tYXAuZ2V0Q2VudGVyKCkubG5nICsgJywnICsgdGhpcy5fbWFwLmdldENlbnRlcigpLmxhdCArICcsJyArIHRoaXMuX21hcC5nZXRab29tKCkgKyAnLzYwMHg2MDAucG5nJywgdGhpcy5vcHRpb25zLmFjY2Vzc1Rva2VuKSxcbiAgICAgICAgICAgIGVtYmVkID0gZm9ybWF0X3VybCgnL3Y0LycgKyB0aWxlanNvbi5pZCArICcuaHRtbCcsIHRoaXMub3B0aW9ucy5hY2Nlc3NUb2tlbiksXG4gICAgICAgICAgICB0d2l0dGVyVVJMID0gJy8vdHdpdHRlci5jb20vaW50ZW50L3R3ZWV0P3N0YXR1cz0nICsgbmFtZSArICcgJyArIHVybCxcbiAgICAgICAgICAgIGZhY2Vib29rVVJMID0gJy8vd3d3LmZhY2Vib29rLmNvbS9zaGFyZXIucGhwP3U9JyArIHVybCArICcmdD0nICsgbmFtZSxcbiAgICAgICAgICAgIHBpbnRlcmVzdFVSTCA9ICcvL3d3dy5waW50ZXJlc3QuY29tL3Bpbi9jcmVhdGUvYnV0dG9uLz91cmw9JyArIHVybCArICcmbWVkaWE9JyArIGltYWdlICsgJyZkZXNjcmlwdGlvbj0nICsgbmFtZSxcbiAgICAgICAgICAgIGVtYmVkVmFsdWUgPSAnPGlmcmFtZSB3aWR0aD1cIjEwMCVcIiBoZWlnaHQ9XCI1MDBweFwiIGZyYW1lQm9yZGVyPVwiMFwiIHNyYz1cIicgKyBlbWJlZCArICdcIj48L2lmcmFtZT4nLFxuICAgICAgICAgICAgZW1iZWRMYWJlbCA9ICdDb3B5IGFuZCBwYXN0ZSB0aGlzIDxzdHJvbmc+SFRNTCBjb2RlPC9zdHJvbmc+IGludG8gZG9jdW1lbnRzIHRvIGVtYmVkIHRoaXMgbWFwIG9uIHdlYiBwYWdlcy4nO1xuXG4gICAgICAgIGZ1bmN0aW9uIGNyZWF0ZVNoYXJlQnV0dG9uKGJ1dHRvbkNsYXNzLCBocmVmLCBzb2NpYWxNZWRpYU5hbWUpIHtcbiAgICAgICAgICAgIHZhciBlbGVtID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnYScpO1xuICAgICAgICAgICAgZWxlbS5zZXRBdHRyaWJ1dGUoJ2NsYXNzJywgYnV0dG9uQ2xhc3MpO1xuICAgICAgICAgICAgZWxlbS5zZXRBdHRyaWJ1dGUoJ2hyZWYnLCBocmVmKTtcbiAgICAgICAgICAgIGVsZW0uc2V0QXR0cmlidXRlKCd0YXJnZXQnLCAnX2JsYW5rJyk7XG4gICAgICAgICAgICBzb2NpYWxNZWRpYU5hbWUgPSBkb2N1bWVudC5jcmVhdGVUZXh0Tm9kZShzb2NpYWxNZWRpYU5hbWUpO1xuICAgICAgICAgICAgZWxlbS5hcHBlbmRDaGlsZChzb2NpYWxNZWRpYU5hbWUpO1xuXG4gICAgICAgICAgICByZXR1cm4gZWxlbTtcbiAgICAgICAgfVxuXG4gICAgICAgIEwuRG9tVXRpbC5hZGRDbGFzcyh0aGlzLl9tb2RhbCwgJ2FjdGl2ZScpO1xuXG4gICAgICAgIHRoaXMuX3NoYXJpbmcgPSBMLkRvbVV0aWwuY3JlYXRlKCdkaXYnLCAnbWFwYm94LW1vZGFsLWJvZHknLCB0aGlzLl9jb250ZW50KTtcblxuICAgICAgICB2YXIgdHdpdHRlckJ1dHRvbiA9IGNyZWF0ZVNoYXJlQnV0dG9uKCdtYXBib3gtYnV0dG9uIG1hcGJveC1idXR0b24taWNvbiBtYXBib3gtaWNvbi10d2l0dGVyJywgdHdpdHRlclVSTCwgJ1R3aXR0ZXInKTtcbiAgICAgICAgdmFyIGZhY2Vib29rQnV0dG9uID0gY3JlYXRlU2hhcmVCdXR0b24oJ21hcGJveC1idXR0b24gbWFwYm94LWJ1dHRvbi1pY29uIG1hcGJveC1pY29uLWZhY2Vib29rJywgZmFjZWJvb2tVUkwsICdGYWNlYm9vaycpO1xuICAgICAgICB2YXIgcGludGVyZXN0QnV0dG9uID0gY3JlYXRlU2hhcmVCdXR0b24oJ21hcGJveC1idXR0b24gbWFwYm94LWJ1dHRvbi1pY29uIG1hcGJveC1pY29uLXBpbnRlcmVzdCcsIHBpbnRlcmVzdFVSTCwgJ1BpbnRlcmVzdCcpO1xuXG4gICAgICAgIHZhciBzaGFyZUhlYWRlciA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2gzJyk7XG4gICAgICAgIHZhciBzaGFyZVRleHQgPSBkb2N1bWVudC5jcmVhdGVUZXh0Tm9kZSgnU2hhcmUgdGhpcyBtYXAnKTtcbiAgICAgICAgc2hhcmVIZWFkZXIuYXBwZW5kQ2hpbGQoc2hhcmVUZXh0KTtcblxuICAgICAgICB2YXIgc2hhcmVCdXR0b25zID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnZGl2Jyk7XG4gICAgICAgIHNoYXJlQnV0dG9ucy5zZXRBdHRyaWJ1dGUoJ2NsYXNzJywgJ21hcGJveC1zaGFyZS1idXR0b25zJyk7XG4gICAgICAgIHNoYXJlQnV0dG9ucy5hcHBlbmRDaGlsZChmYWNlYm9va0J1dHRvbik7XG4gICAgICAgIHNoYXJlQnV0dG9ucy5hcHBlbmRDaGlsZCh0d2l0dGVyQnV0dG9uKTtcbiAgICAgICAgc2hhcmVCdXR0b25zLmFwcGVuZENoaWxkKHBpbnRlcmVzdEJ1dHRvbik7XG5cbiAgICAgICAgdGhpcy5fc2hhcmluZy5hcHBlbmRDaGlsZChzaGFyZUhlYWRlcik7XG4gICAgICAgIHRoaXMuX3NoYXJpbmcuYXBwZW5kQ2hpbGQoc2hhcmVCdXR0b25zKTtcblxuICAgICAgICB2YXIgaW5wdXQgPSBMLkRvbVV0aWwuY3JlYXRlKCdpbnB1dCcsICdtYXBib3gtZW1iZWQnLCB0aGlzLl9zaGFyaW5nKTtcbiAgICAgICAgaW5wdXQudHlwZSA9ICd0ZXh0JztcbiAgICAgICAgaW5wdXQudmFsdWUgPSBlbWJlZFZhbHVlO1xuXG4gICAgICAgIHZhciBsYWJlbCA9IEwuRG9tVXRpbC5jcmVhdGUoJ2xhYmVsJywgJ21hcGJveC1lbWJlZC1kZXNjcmlwdGlvbicsIHRoaXMuX3NoYXJpbmcpO1xuICAgICAgICBsYWJlbC5pbm5lckhUTUwgPSBlbWJlZExhYmVsO1xuXG4gICAgICAgIHZhciBjbG9zZSA9IEwuRG9tVXRpbC5jcmVhdGUoJ2EnLCAnbGVhZmxldC1wb3B1cC1jbG9zZS1idXR0b24nLCB0aGlzLl9zaGFyaW5nKTtcbiAgICAgICAgY2xvc2UuaHJlZiA9ICcjJztcblxuICAgICAgICBMLkRvbUV2ZW50LmRpc2FibGVDbGlja1Byb3BhZ2F0aW9uKHRoaXMuX3NoYXJpbmcpO1xuICAgICAgICBMLkRvbUV2ZW50LmFkZExpc3RlbmVyKGNsb3NlLCAnY2xpY2snLCB0aGlzLl9jbGlja091dCwgdGhpcyk7XG4gICAgICAgIEwuRG9tRXZlbnQuYWRkTGlzdGVuZXIoaW5wdXQsICdjbGljaycsIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgICAgIGUudGFyZ2V0LmZvY3VzKCk7XG4gICAgICAgICAgICBlLnRhcmdldC5zZWxlY3QoKTtcbiAgICAgICAgfSk7XG4gICAgfVxufSk7XG5cbm1vZHVsZS5leHBvcnRzLlNoYXJlQ29udHJvbCA9IFNoYXJlQ29udHJvbDtcblxubW9kdWxlLmV4cG9ydHMuc2hhcmVDb250cm9sID0gZnVuY3Rpb24oXywgb3B0aW9ucykge1xuICAgIHJldHVybiBuZXcgU2hhcmVDb250cm9sKF8sIG9wdGlvbnMpO1xufTtcbiIsIid1c2Ugc3RyaWN0JztcblxuLy8gYW4gaW1wbGVtZW50YXRpb24gb2YgdGhlIHNpbXBsZXN0eWxlIHNwZWMgZm9yIHBvbHlnb24gYW5kIGxpbmVzdHJpbmcgZmVhdHVyZXNcbi8vIGh0dHBzOi8vZ2l0aHViLmNvbS9tYXBib3gvc2ltcGxlc3R5bGUtc3BlY1xudmFyIGRlZmF1bHRzID0ge1xuICAgIHN0cm9rZTogJyM1NTU1NTUnLFxuICAgICdzdHJva2Utd2lkdGgnOiAyLFxuICAgICdzdHJva2Utb3BhY2l0eSc6IDEsXG4gICAgZmlsbDogJyM1NTU1NTUnLFxuICAgICdmaWxsLW9wYWNpdHknOiAwLjVcbn07XG5cbnZhciBtYXBwaW5nID0gW1xuICAgIFsnc3Ryb2tlJywgJ2NvbG9yJ10sXG4gICAgWydzdHJva2Utd2lkdGgnLCAnd2VpZ2h0J10sXG4gICAgWydzdHJva2Utb3BhY2l0eScsICdvcGFjaXR5J10sXG4gICAgWydmaWxsJywgJ2ZpbGxDb2xvciddLFxuICAgIFsnZmlsbC1vcGFjaXR5JywgJ2ZpbGxPcGFjaXR5J11cbl07XG5cbmZ1bmN0aW9uIGZhbGxiYWNrKGEsIGIpIHtcbiAgICB2YXIgYyA9IHt9O1xuICAgIGZvciAodmFyIGsgaW4gYikge1xuICAgICAgICBpZiAoYVtrXSA9PT0gdW5kZWZpbmVkKSBjW2tdID0gYltrXTtcbiAgICAgICAgZWxzZSBjW2tdID0gYVtrXTtcbiAgICB9XG4gICAgcmV0dXJuIGM7XG59XG5cbmZ1bmN0aW9uIHJlbWFwKGEpIHtcbiAgICB2YXIgZCA9IHt9O1xuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgbWFwcGluZy5sZW5ndGg7IGkrKykge1xuICAgICAgICBkW21hcHBpbmdbaV1bMV1dID0gYVttYXBwaW5nW2ldWzBdXTtcbiAgICB9XG4gICAgcmV0dXJuIGQ7XG59XG5cbmZ1bmN0aW9uIHN0eWxlKGZlYXR1cmUpIHtcbiAgICByZXR1cm4gcmVtYXAoZmFsbGJhY2soZmVhdHVyZS5wcm9wZXJ0aWVzIHx8IHt9LCBkZWZhdWx0cykpO1xufVxuXG5tb2R1bGUuZXhwb3J0cyA9IHtcbiAgICBzdHlsZTogc3R5bGUsXG4gICAgZGVmYXVsdHM6IGRlZmF1bHRzXG59O1xuIiwiJ3VzZSBzdHJpY3QnO1xuXG52YXIgdXRpbCA9IHJlcXVpcmUoJy4vdXRpbCcpO1xudmFyIGZvcm1hdF91cmwgPSByZXF1aXJlKCcuL2Zvcm1hdF91cmwnKTtcbnZhciByZXF1ZXN0ID0gcmVxdWlyZSgnLi9yZXF1ZXN0Jyk7XG5cbnZhciBTdHlsZUxheWVyID0gTC5UaWxlTGF5ZXIuZXh0ZW5kKHtcblxuICAgIG9wdGlvbnM6IHtcbiAgICAgICAgc2FuaXRpemVyOiByZXF1aXJlKCdzYW5pdGl6ZS1jYWphJylcbiAgICB9LFxuXG4gICAgaW5pdGlhbGl6ZTogZnVuY3Rpb24oXywgb3B0aW9ucykge1xuICAgICAgICBMLlRpbGVMYXllci5wcm90b3R5cGUuaW5pdGlhbGl6ZS5jYWxsKHRoaXMsIHVuZGVmaW5lZCwgTC5leHRlbmQoe30sIG9wdGlvbnMsIHtcbiAgICAgICAgICAgIHRpbGVTaXplOiA1MTIsXG4gICAgICAgICAgICB6b29tT2Zmc2V0OiAtMSxcbiAgICAgICAgICAgIG1pbk5hdGl2ZVpvb206IDAsXG4gICAgICAgICAgICB0bXM6IGZhbHNlXG4gICAgICAgIH0pKTtcbiAgICAgICAgdGhpcy5fdXJsID0gdGhpcy5fZm9ybWF0VGlsZVVSTChfKTtcbiAgICAgICAgdGhpcy5fZ2V0QXR0cmlidXRpb24oXyk7XG4gICAgfSxcblxuICAgIF9nZXRBdHRyaWJ1dGlvbjogZnVuY3Rpb24oXykge1xuICAgICAgICB2YXIgc3R5bGVVUkwgPSBmb3JtYXRfdXJsLnN0eWxlKF8sIHRoaXMub3B0aW9ucyAmJiB0aGlzLm9wdGlvbnMuYWNjZXNzVG9rZW4pO1xuICAgICAgICByZXF1ZXN0KHN0eWxlVVJMLCBMLmJpbmQoZnVuY3Rpb24oZXJyLCBzdHlsZSkge1xuICAgICAgICAgICAgaWYgKGVycikge1xuICAgICAgICAgICAgICAgIHV0aWwubG9nKCdjb3VsZCBub3QgbG9hZCBNYXBib3ggc3R5bGUgYXQgJyArIHN0eWxlVVJMKTtcbiAgICAgICAgICAgICAgICB0aGlzLmZpcmUoJ2Vycm9yJywge2Vycm9yOiBlcnJ9KTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHZhciBzb3VyY2VzID0gW107XG4gICAgICAgICAgICBmb3IgKHZhciBpZCBpbiBzdHlsZS5zb3VyY2VzKSB7XG4gICAgICAgICAgICAgICAgdmFyIHNvdXJjZSA9IHN0eWxlLnNvdXJjZXNbaWRdLnVybC5zcGxpdCgnbWFwYm94Oi8vJylbMV07XG4gICAgICAgICAgICAgICAgc291cmNlcy5wdXNoKHNvdXJjZSk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICByZXF1ZXN0KGZvcm1hdF91cmwudGlsZUpTT04oc291cmNlcy5qb2luKCksIHRoaXMub3B0aW9ucy5hY2Nlc3NUb2tlbiksIEwuYmluZChmdW5jdGlvbihlcnIsIGpzb24pIHtcbiAgICAgICAgICAgICAgICBpZiAoZXJyKSB7XG4gICAgICAgICAgICAgICAgICAgIHV0aWwubG9nKCdjb3VsZCBub3QgbG9hZCBUaWxlSlNPTiBhdCAnICsgXyk7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuZmlyZSgnZXJyb3InLCB7ZXJyb3I6IGVycn0pO1xuICAgICAgICAgICAgICAgIH0gZWxzZSBpZiAoanNvbikge1xuICAgICAgICAgICAgICAgICAgICB1dGlsLnN0cmljdChqc29uLCAnb2JqZWN0Jyk7XG5cbiAgICAgICAgICAgICAgICAgICAgdGhpcy5vcHRpb25zLmF0dHJpYnV0aW9uID0gdGhpcy5vcHRpb25zLnNhbml0aXplcihqc29uLmF0dHJpYnV0aW9uKTtcblxuICAgICAgICAgICAgICAgICAgICB0aGlzLl90aWxlanNvbiA9IGpzb247XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuZmlyZSgncmVhZHknKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9LCB0aGlzKSk7XG4gICAgICAgIH0sIHRoaXMpKTtcbiAgICB9LFxuXG4gICAgLy8gZGlzYWJsZSB0aGUgc2V0VXJsIGZ1bmN0aW9uLCB3aGljaCBpcyBub3QgYXZhaWxhYmxlIG9uIG1hcGJveCB0aWxlbGF5ZXJzXG4gICAgc2V0VXJsOiBudWxsLFxuXG4gICAgX2Zvcm1hdFRpbGVVUkw6IGZ1bmN0aW9uKHN0eWxlKSB7XG4gICAgICAgIGlmICh0eXBlb2Ygc3R5bGUgPT09ICdzdHJpbmcnKSB7XG4gICAgICAgICAgICBpZiAoc3R5bGUuaW5kZXhPZignbWFwYm94Oi8vc3R5bGVzLycpID09PSAtMSkge1xuICAgICAgICAgICAgICAgIHV0aWwubG9nKCdJbmNvcnJlY3RseSBmb3JtYXR0ZWQgTWFwYm94IHN0eWxlIGF0ICcgKyBzdHlsZSk7XG4gICAgICAgICAgICAgICAgdGhpcy5maXJlKCdlcnJvcicpO1xuICAgICAgICAgICAgfVxuICAgICAgICAgICAgdmFyIG93bmVySURTdHlsZSA9IHN0eWxlLnNwbGl0KCdtYXBib3g6Ly9zdHlsZXMvJylbMV07XG4gICAgICAgICAgICByZXR1cm4gZm9ybWF0X3VybCgnL3N0eWxlcy92MS8nICsgb3duZXJJRFN0eWxlICsgJy90aWxlcy97en0ve3h9L3t5fXtyfScsIHRoaXMub3B0aW9ucy5hY2Nlc3NUb2tlbik7XG4gICAgICAgIH0gZWxzZSBpZiAodHlwZW9mIHN0eWxlID09PSAnb2JqZWN0Jykge1xuICAgICAgICAgICAgcmV0dXJuIGZvcm1hdF91cmwoJy9zdHlsZXMvdjEvJyArIHN0eWxlLm93bmVyICsgJy8nICsgc3R5bGUuaWQgKyAnL3RpbGVzL3t6fS97eH0ve3l9e3J9JywgdGhpcy5vcHRpb25zLmFjY2Vzc1Rva2VuKTtcbiAgICAgICAgfVxuICAgIH1cbn0pO1xuXG5tb2R1bGUuZXhwb3J0cy5TdHlsZUxheWVyID0gU3R5bGVMYXllcjtcblxubW9kdWxlLmV4cG9ydHMuc3R5bGVMYXllciA9IGZ1bmN0aW9uKF8sIG9wdGlvbnMpIHtcbiAgICByZXR1cm4gbmV3IFN0eWxlTGF5ZXIoXywgb3B0aW9ucyk7XG59O1xuIiwiJ3VzZSBzdHJpY3QnO1xuXG52YXIgdXRpbCA9IHJlcXVpcmUoJy4vdXRpbCcpO1xudmFyIGZvcm1hdFBhdHRlcm4gPSAvXFwuKCg/OnBuZ3xqcGcpXFxkKikoPz0kfFxcPykvO1xuXG52YXIgVGlsZUxheWVyID0gTC5UaWxlTGF5ZXIuZXh0ZW5kKHtcbiAgICBpbmNsdWRlczogW3JlcXVpcmUoJy4vbG9hZF90aWxlanNvbicpXSxcblxuICAgIG9wdGlvbnM6IHtcbiAgICAgICAgc2FuaXRpemVyOiByZXF1aXJlKCdzYW5pdGl6ZS1jYWphJylcbiAgICB9LFxuXG4gICAgLy8gaHR0cDovL21hcGJveC5jb20vZGV2ZWxvcGVycy9hcGkvI2ltYWdlX3F1YWxpdHlcbiAgICBmb3JtYXRzOiBbXG4gICAgICAgICdwbmcnLCAnanBnJyxcbiAgICAgICAgLy8gUE5HXG4gICAgICAgICdwbmczMicsICdwbmc2NCcsICdwbmcxMjgnLCAncG5nMjU2JyxcbiAgICAgICAgLy8gSlBHXG4gICAgICAgICdqcGc3MCcsICdqcGc4MCcsICdqcGc5MCddLFxuXG4gICAgc2NhbGVQcmVmaXg6ICdAMnguJyxcblxuICAgIGluaXRpYWxpemU6IGZ1bmN0aW9uKF8sIG9wdGlvbnMpIHtcbiAgICAgICAgTC5UaWxlTGF5ZXIucHJvdG90eXBlLmluaXRpYWxpemUuY2FsbCh0aGlzLCB1bmRlZmluZWQsIG9wdGlvbnMpO1xuXG4gICAgICAgIHRoaXMuX3RpbGVqc29uID0ge307XG5cbiAgICAgICAgaWYgKG9wdGlvbnMgJiYgb3B0aW9ucy5mb3JtYXQpIHtcbiAgICAgICAgICAgIHV0aWwuc3RyaWN0X29uZW9mKG9wdGlvbnMuZm9ybWF0LCB0aGlzLmZvcm1hdHMpO1xuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5fbG9hZFRpbGVKU09OKF8pO1xuICAgIH0sXG5cbiAgICBzZXRGb3JtYXQ6IGZ1bmN0aW9uKF8pIHtcbiAgICAgICAgdXRpbC5zdHJpY3QoXywgJ3N0cmluZycpO1xuICAgICAgICB0aGlzLm9wdGlvbnMuZm9ybWF0ID0gXztcbiAgICAgICAgdGhpcy5yZWRyYXcoKTtcbiAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfSxcblxuICAgIC8vIGRpc2FibGUgdGhlIHNldFVybCBmdW5jdGlvbiwgd2hpY2ggaXMgbm90IGF2YWlsYWJsZSBvbiBtYXBib3ggdGlsZWxheWVyc1xuICAgIHNldFVybDogbnVsbCxcblxuICAgIF9zZXRUaWxlSlNPTjogZnVuY3Rpb24oanNvbikge1xuICAgICAgICB1dGlsLnN0cmljdChqc29uLCAnb2JqZWN0Jyk7XG5cbiAgICAgICAgaWYgKCF0aGlzLm9wdGlvbnMuZm9ybWF0KSB7XG4gICAgICAgICAgdmFyIG1hdGNoID0ganNvbi50aWxlc1swXS5tYXRjaChmb3JtYXRQYXR0ZXJuKTtcbiAgICAgICAgICBpZiAobWF0Y2gpIHtcbiAgICAgICAgICAgICAgdGhpcy5vcHRpb25zLmZvcm1hdCA9IG1hdGNoWzFdO1xuICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIEwuZXh0ZW5kKHRoaXMub3B0aW9ucywge1xuICAgICAgICAgICAgdGlsZXM6IGpzb24udGlsZXMsXG4gICAgICAgICAgICBhdHRyaWJ1dGlvbjogdGhpcy5vcHRpb25zLnNhbml0aXplcihqc29uLmF0dHJpYnV0aW9uKSxcbiAgICAgICAgICAgIG1pblpvb206IGpzb24ubWluem9vbSB8fCAwLFxuICAgICAgICAgICAgbWF4Wm9vbToganNvbi5tYXh6b29tIHx8IDE4LFxuICAgICAgICAgICAgdG1zOiBqc29uLnNjaGVtZSA9PT0gJ3RtcycsXG4gICAgICAgICAgICBib3VuZHM6IGpzb24uYm91bmRzICYmIHV0aWwubGJvdW5kcyhqc29uLmJvdW5kcylcbiAgICAgICAgfSk7XG5cbiAgICAgICAgdGhpcy5fdGlsZWpzb24gPSBqc29uO1xuICAgICAgICB0aGlzLnJlZHJhdygpO1xuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9LFxuXG4gICAgZ2V0VGlsZUpTT046IGZ1bmN0aW9uKCkge1xuICAgICAgICByZXR1cm4gdGhpcy5fdGlsZWpzb247XG4gICAgfSxcblxuICAgIC8vIHRoaXMgaXMgYW4gZXhjZXB0aW9uIHRvIG1hcGJveC5qcyBuYW1pbmcgcnVsZXMgYmVjYXVzZSBpdCdzIGNhbGxlZFxuICAgIC8vIGJ5IGBMLm1hcGBcbiAgICBnZXRUaWxlVXJsOiBmdW5jdGlvbih0aWxlUG9pbnQpIHtcbiAgICAgICAgdmFyIHRpbGVzID0gdGhpcy5vcHRpb25zLnRpbGVzLFxuICAgICAgICAgICAgaW5kZXggPSBNYXRoLmZsb29yKE1hdGguYWJzKHRpbGVQb2ludC54ICsgdGlsZVBvaW50LnkpICUgdGlsZXMubGVuZ3RoKSxcbiAgICAgICAgICAgIHVybCA9IHRpbGVzW2luZGV4XTtcblxuICAgICAgICB2YXIgdGVtcGxhdGVkID0gTC5VdGlsLnRlbXBsYXRlKHVybCwgdGlsZVBvaW50KTtcbiAgICAgICAgaWYgKCF0ZW1wbGF0ZWQgfHwgIXRoaXMub3B0aW9ucy5mb3JtYXQpIHtcbiAgICAgICAgICAgIHJldHVybiB0ZW1wbGF0ZWQ7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICByZXR1cm4gdGVtcGxhdGVkLnJlcGxhY2UoZm9ybWF0UGF0dGVybixcbiAgICAgICAgICAgICAgICAoTC5Ccm93c2VyLnJldGluYSA/IHRoaXMuc2NhbGVQcmVmaXggOiAnLicpICsgdGhpcy5vcHRpb25zLmZvcm1hdCk7XG4gICAgICAgIH1cbiAgICB9LFxuXG4gICAgLy8gVGlsZUpTT04uVGlsZUxheWVycyBhcmUgYWRkZWQgdG8gdGhlIG1hcCBpbW1lZGlhdGVseSwgc28gdGhhdCB0aGV5IGdldFxuICAgIC8vIHRoZSBkZXNpcmVkIHotaW5kZXgsIGJ1dCBkbyBub3QgdXBkYXRlIHVudGlsIHRoZSBUaWxlSlNPTiBoYXMgYmVlbiBsb2FkZWQuXG4gICAgX3VwZGF0ZTogZnVuY3Rpb24oKSB7XG4gICAgICAgIGlmICh0aGlzLm9wdGlvbnMudGlsZXMpIHtcbiAgICAgICAgICAgIEwuVGlsZUxheWVyLnByb3RvdHlwZS5fdXBkYXRlLmNhbGwodGhpcyk7XG4gICAgICAgIH1cbiAgICB9XG59KTtcblxubW9kdWxlLmV4cG9ydHMuVGlsZUxheWVyID0gVGlsZUxheWVyO1xuXG5tb2R1bGUuZXhwb3J0cy50aWxlTGF5ZXIgPSBmdW5jdGlvbihfLCBvcHRpb25zKSB7XG4gICAgcmV0dXJuIG5ldyBUaWxlTGF5ZXIoXywgb3B0aW9ucyk7XG59O1xuIiwiJ3VzZSBzdHJpY3QnO1xuXG5mdW5jdGlvbiBjb250YWlucyhpdGVtLCBsaXN0KSB7XG4gICAgaWYgKCFsaXN0IHx8ICFsaXN0Lmxlbmd0aCkgcmV0dXJuIGZhbHNlO1xuICAgIGZvciAodmFyIGkgPSAwOyBpIDwgbGlzdC5sZW5ndGg7IGkrKykge1xuICAgICAgICBpZiAobGlzdFtpXSA9PT0gaXRlbSkgcmV0dXJuIHRydWU7XG4gICAgfVxuICAgIHJldHVybiBmYWxzZTtcbn1cblxubW9kdWxlLmV4cG9ydHMgPSB7XG4gICAgaWRVcmw6IGZ1bmN0aW9uKF8sIHQpIHtcbiAgICAgICAgaWYgKF8uaW5kZXhPZignLycpID09PSAtMSkgdC5sb2FkSUQoXyk7XG4gICAgICAgIGVsc2UgdC5sb2FkVVJMKF8pO1xuICAgIH0sXG4gICAgbG9nOiBmdW5jdGlvbihfKSB7XG4gICAgICAgIGlmICh0eXBlb2YgY29uc29sZSA9PT0gJ29iamVjdCcgJiZcbiAgICAgICAgICAgIHR5cGVvZiBjb25zb2xlLmVycm9yID09PSAnZnVuY3Rpb24nKSB7XG4gICAgICAgICAgICBjb25zb2xlLmVycm9yKF8pO1xuICAgICAgICB9XG4gICAgfSxcbiAgICBzdHJpY3Q6IGZ1bmN0aW9uKF8sIHR5cGUpIHtcbiAgICAgICAgaWYgKHR5cGVvZiBfICE9PSB0eXBlKSB7XG4gICAgICAgICAgICB0aHJvdyBuZXcgRXJyb3IoJ0ludmFsaWQgYXJndW1lbnQ6ICcgKyB0eXBlICsgJyBleHBlY3RlZCcpO1xuICAgICAgICB9XG4gICAgfSxcbiAgICBzdHJpY3RfaW5zdGFuY2U6IGZ1bmN0aW9uKF8sIGtsYXNzLCBuYW1lKSB7XG4gICAgICAgIGlmICghKF8gaW5zdGFuY2VvZiBrbGFzcykpIHtcbiAgICAgICAgICAgIHRocm93IG5ldyBFcnJvcignSW52YWxpZCBhcmd1bWVudDogJyArIG5hbWUgKyAnIGV4cGVjdGVkJyk7XG4gICAgICAgIH1cbiAgICB9LFxuICAgIHN0cmljdF9vbmVvZjogZnVuY3Rpb24oXywgdmFsdWVzKSB7XG4gICAgICAgIGlmICghY29udGFpbnMoXywgdmFsdWVzKSkge1xuICAgICAgICAgICAgdGhyb3cgbmV3IEVycm9yKCdJbnZhbGlkIGFyZ3VtZW50OiAnICsgXyArICcgZ2l2ZW4sIHZhbGlkIHZhbHVlcyBhcmUgJyArXG4gICAgICAgICAgICAgICAgdmFsdWVzLmpvaW4oJywgJykpO1xuICAgICAgICB9XG4gICAgfSxcbiAgICBzdHJpcF90YWdzOiBmdW5jdGlvbihfKSB7XG4gICAgICAgIHJldHVybiBfLnJlcGxhY2UoLzxbXjxdKz4vZywgJycpO1xuICAgIH0sXG4gICAgbGJvdW5kczogZnVuY3Rpb24oXykge1xuICAgICAgICAvLyBsZWFmbGV0LWNvbXBhdGlibGUgYm91bmRzLCBzaW5jZSBsZWFmbGV0IGRvZXMgbm90IGRvIGdlb2pzb25cbiAgICAgICAgcmV0dXJuIG5ldyBMLkxhdExuZ0JvdW5kcyhbW19bMV0sIF9bMF1dLCBbX1szXSwgX1syXV1dKTtcbiAgICB9XG59O1xuIl19
