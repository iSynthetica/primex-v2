/**!
 * @fileOverview Kickass library to create and place poppers near their reference elements.
 * @version 1.15.0
 * @license
 * Copyright (c) 2016 Federico Zivolo and contributors
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
	typeof define === 'function' && define.amd ? define(factory) :
	(global.Popper = factory());
}(this, (function () { 'use strict';

var isBrowser = typeof window !== 'undefined' && typeof document !== 'undefined';

var longerTimeoutBrowsers = ['Edge', 'Trident', 'Firefox'];
var timeoutDuration = 0;
for (var i = 0; i < longerTimeoutBrowsers.length; i += 1) {
  if (isBrowser && navigator.userAgent.indexOf(longerTimeoutBrowsers[i]) >= 0) {
    timeoutDuration = 1;
    break;
  }
}

function microtaskDebounce(fn) {
  var called = false;
  return function () {
    if (called) {
      return;
    }
    called = true;
    window.Promise.resolve().then(function () {
      called = false;
      fn();
    });
  };
}

function taskDebounce(fn) {
  var scheduled = false;
  return function () {
    if (!scheduled) {
      scheduled = true;
      setTimeout(function () {
        scheduled = false;
        fn();
      }, timeoutDuration);
    }
  };
}

var supportsMicroTasks = isBrowser && window.Promise;

/**
* Create a debounced version of a method, that's asynchronously deferred
* but called in the minimum time possible.
*
* @method
* @memberof Popper.Utils
* @argument {Function} fn
* @returns {Function}
*/
var debounce = supportsMicroTasks ? microtaskDebounce : taskDebounce;

/**
 * Check if the given variable is a function
 * @method
 * @memberof Popper.Utils
 * @argument {Any} functionToCheck - variable to check
 * @returns {Boolean} answer to: is a function?
 */
function isFunction(functionToCheck) {
  var getType = {};
  return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
}

/**
 * Get CSS computed property of the given element
 * @method
 * @memberof Popper.Utils
 * @argument {Eement} element
 * @argument {String} property
 */
function getStyleComputedProperty(element, property) {
  if (element.nodeType !== 1) {
    return [];
  }
  // NOTE: 1 DOM access here
  var window = element.ownerDocument.defaultView;
  var css = window.getComputedStyle(element, null);
  return property ? css[property] : css;
}

/**
 * Returns the parentNode or the host of the element
 * @method
 * @memberof Popper.Utils
 * @argument {Element} element
 * @returns {Element} parent
 */
function getParentNode(element) {
  if (element.nodeName === 'HTML') {
    return element;
  }
  return element.parentNode || element.host;
}

/**
 * Returns the scrolling parent of the given element
 * @method
 * @memberof Popper.Utils
 * @argument {Element} element
 * @returns {Element} scroll parent
 */
function getScrollParent(element) {
  // Return body, `getScroll` will take care to get the correct `scrollTop` from it
  if (!element) {
    return document.body;
  }

  switch (element.nodeName) {
    case 'HTML':
    case 'BODY':
      return element.ownerDocument.body;
    case '#document':
      return element.body;
  }

  // Firefox want us to check `-x` and `-y` variations as well

  var _getStyleComputedProp = getStyleComputedProperty(element),
      overflow = _getStyleComputedProp.overflow,
      overflowX = _getStyleComputedProp.overflowX,
      overflowY = _getStyleComputedProp.overflowY;

  if (/(auto|scroll|overlay)/.test(overflow + overflowY + overflowX)) {
    return element;
  }

  return getScrollParent(getParentNode(element));
}

var isIE11 = isBrowser && !!(window.MSInputMethodContext && document.documentMode);
var isIE10 = isBrowser && /MSIE 10/.test(navigator.userAgent);

/**
 * Determines if the browser is Internet Explorer
 * @method
 * @memberof Popper.Utils
 * @param {Number} version to check
 * @returns {Boolean} isIE
 */
function isIE(version) {
  if (version === 11) {
    return isIE11;
  }
  if (version === 10) {
    return isIE10;
  }
  return isIE11 || isIE10;
}

/**
 * Returns the offset parent of the given element
 * @method
 * @memberof Popper.Utils
 * @argument {Element} element
 * @returns {Element} offset parent
 */
function getOffsetParent(element) {
  if (!element) {
    return document.documentElement;
  }

  var noOffsetParent = isIE(10) ? document.body : null;

  // NOTE: 1 DOM access here
  var offsetParent = element.offsetParent || null;
  // Skip hidden elements which don't have an offsetParent
  while (offsetParent === noOffsetParent && element.nextElementSibling) {
    offsetParent = (element = element.nextElementSibling).offsetParent;
  }

  var nodeName = offsetParent && offsetParent.nodeName;

  if (!nodeName || nodeName === 'BODY' || nodeName === 'HTML') {
    return element ? element.ownerDocument.documentElement : document.documentElement;
  }

  // .offsetParent will return the closest TH, TD or TABLE in case
  // no offsetParent is present, I hate this job...
  if (['TH', 'TD', 'TABLE'].indexOf(offsetParent.nodeName) !== -1 && getStyleComputedProperty(offsetParent, 'position') === 'static') {
    return getOffsetParent(offsetParent);
  }

  return offsetParent;
}

function isOffsetContainer(element) {
  var nodeName = element.nodeName;

  if (nodeName === 'BODY') {
    return false;
  }
  return nodeName === 'HTML' || getOffsetParent(element.firstElementChild) === element;
}

/**
 * Finds the root node (document, shadowDOM root) of the given element
 * @method
 * @memberof Popper.Utils
 * @argument {Element} node
 * @returns {Element} root node
 */
function getRoot(node) {
  if (node.parentNode !== null) {
    return getRoot(node.parentNode);
  }

  return node;
}

/**
 * Finds the offset parent common to the two provided nodes
 * @method
 * @memberof Popper.Utils
 * @argument {Element} element1
 * @argument {Element} element2
 * @returns {Element} common offset parent
 */
function findCommonOffsetParent(element1, element2) {
  // This check is needed to avoid errors in case one of the elements isn't defined for any reason
  if (!element1 || !element1.nodeType || !element2 || !element2.nodeType) {
    return document.documentElement;
  }

  // Here we make sure to give as "start" the element that comes first in the DOM
  var order = element1.compareDocumentPosition(element2) & Node.DOCUMENT_POSITION_FOLLOWING;
  var start = order ? element1 : element2;
  var end = order ? element2 : element1;

  // Get common ancestor container
  var range = document.createRange();
  range.setStart(start, 0);
  range.setEnd(end, 0);
  var commonAncestorContainer = range.commonAncestorContainer;

  // Both nodes are inside #document

  if (element1 !== commonAncestorContainer && element2 !== commonAncestorContainer || start.contains(end)) {
    if (isOffsetContainer(commonAncestorContainer)) {
      return commonAncestorContainer;
    }

    return getOffsetParent(commonAncestorContainer);
  }

  // one of the nodes is inside shadowDOM, find which one
  var element1root = getRoot(element1);
  if (element1root.host) {
    return findCommonOffsetParent(element1root.host, element2);
  } else {
    return findCommonOffsetParent(element1, getRoot(element2).host);
  }
}

/**
 * Gets the scroll value of the given element in the given side (top and left)
 * @method
 * @memberof Popper.Utils
 * @argument {Element} element
 * @argument {String} side `top` or `left`
 * @returns {number} amount of scrolled pixels
 */
function getScroll(element) {
  var side = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'top';

  var upperSide = side === 'top' ? 'scrollTop' : 'scrollLeft';
  var nodeName = element.nodeName;

  if (nodeName === 'BODY' || nodeName === 'HTML') {
    var html = element.ownerDocument.documentElement;
    var scrollingElement = element.ownerDocument.scrollingElement || html;
    return scrollingElement[upperSide];
  }

  return element[upperSide];
}

/*
 * Sum or subtract the element scroll values (left and top) from a given rect object
 * @method
 * @memberof Popper.Utils
 * @param {Object} rect - Rect object you want to change
 * @param {HTMLElement} element - The element from the function reads the scroll values
 * @param {Boolean} subtract - set to true if you want to subtract the scroll values
 * @return {Object} rect - The modifier rect object
 */
function includeScroll(rect, element) {
  var subtract = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

  var scrollTop = getScroll(element, 'top');
  var scrollLeft = getScroll(element, 'left');
  var modifier = subtract ? -1 : 1;
  rect.top += scrollTop * modifier;
  rect.bottom += scrollTop * modifier;
  rect.left += scrollLeft * modifier;
  rect.right += scrollLeft * modifier;
  return rect;
}

/*
 * Helper to detect borders of a given element
 * @method
 * @memberof Popper.Utils
 * @param {CSSStyleDeclaration} styles
 * Result of `getStyleComputedProperty` on the given element
 * @param {String} axis - `x` or `y`
 * @return {number} borders - The borders size of the given axis
 */

function getBordersSize(styles, axis) {
  var sideA = axis === 'x' ? 'Left' : 'Top';
  var sideB = sideA === 'Left' ? 'Right' : 'Bottom';

  return parseFloat(styles['border' + sideA + 'Width'], 10) + parseFloat(styles['border' + sideB + 'Width'], 10);
}

function getSize(axis, body, html, computedStyle) {
  return Math.max(body['offset' + axis], body['scroll' + axis], html['client' + axis], html['offset' + axis], html['scroll' + axis], isIE(10) ? parseInt(html['offset' + axis]) + parseInt(computedStyle['margin' + (axis === 'Height' ? 'Top' : 'Left')]) + parseInt(computedStyle['margin' + (axis === 'Height' ? 'Bottom' : 'Right')]) : 0);
}

function getWindowSizes(document) {
  var body = document.body;
  var html = document.documentElement;
  var computedStyle = isIE(10) && getComputedStyle(html);

  return {
    height: getSize('Height', body, html, computedStyle),
    width: getSize('Width', body, html, computedStyle)
  };
}

var classCallCheck = function (instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
};

var createClass = function () {
  function defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  return function (Constructor, protoProps, staticProps) {
    if (protoProps) defineProperties(Constructor.prototype, protoProps);
    if (staticProps) defineProperties(Constructor, staticProps);
    return Constructor;
  };
}();





var defineProperty = function (obj, key, value) {
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }

  return obj;
};

var _extends = Object.assign || function (target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i];

    for (var key in source) {
      if (Object.prototype.hasOwnProperty.call(source, key)) {
        target[key] = source[key];
      }
    }
  }

  return target;
};

/**
 * Given element offsets, generate an output similar to getBoundingClientRect
 * @method
 * @memberof Popper.Utils
 * @argument {Object} offsets
 * @returns {Object} ClientRect like output
 */
function getClientRect(offsets) {
  return _extends({}, offsets, {
    right: offsets.left + offsets.width,
    bottom: offsets.top + offsets.height
  });
}

/**
 * Get bounding client rect of given element
 * @method
 * @memberof Popper.Utils
 * @param {HTMLElement} element
 * @return {Object} client rect
 */
function getBoundingClientRect(element) {
  var rect = {};

  // IE10 10 FIX: Please, don't ask, the element isn't
  // considered in DOM in some circumstances...
  // This isn't reproducible in IE10 compatibility mode of IE11
  try {
    if (isIE(10)) {
      rect = element.getBoundingClientRect();
      var scrollTop = getScroll(element, 'top');
      var scrollLeft = getScroll(element, 'left');
      rect.top += scrollTop;
      rect.left += scrollLeft;
      rect.bottom += scrollTop;
      rect.right += scrollLeft;
    } else {
      rect = element.getBoundingClientRect();
    }
  } catch (e) {}

  var result = {
    left: rect.left,
    top: rect.top,
    width: rect.right - rect.left,
    height: rect.bottom - rect.top
  };

  // subtract scrollbar size from sizes
  var sizes = element.nodeName === 'HTML' ? getWindowSizes(element.ownerDocument) : {};
  var width = sizes.width || element.clientWidth || result.right - result.left;
  var height = sizes.height || element.clientHeight || result.bottom - result.top;

  var horizScrollbar = element.offsetWidth - width;
  var vertScrollbar = element.offsetHeight - height;

  // if an hypothetical scrollbar is detected, we must be sure it's not a `border`
  // we make this check conditional for performance reasons
  if (horizScrollbar || vertScrollbar) {
    var styles = getStyleComputedProperty(element);
    horizScrollbar -= getBordersSize(styles, 'x');
    vertScrollbar -= getBordersSize(styles, 'y');

    result.width -= horizScrollbar;
    result.height -= vertScrollbar;
  }

  return getClientRect(result);
}

function getOffsetRectRelativeToArbitraryNode(children, parent) {
  var fixedPosition = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

  var isIE10 = isIE(10);
  var isHTML = parent.nodeName === 'HTML';
  var childrenRect = getBoundingClientRect(children);
  var parentRect = getBoundingClientRect(parent);
  var scrollParent = getScrollParent(children);

  var styles = getStyleComputedProperty(parent);
  var borderTopWidth = parseFloat(styles.borderTopWidth, 10);
  var borderLeftWidth = parseFloat(styles.borderLeftWidth, 10);

  // In cases where the parent is fixed, we must ignore negative scroll in offset calc
  if (fixedPosition && isHTML) {
    parentRect.top = Math.max(parentRect.top, 0);
    parentRect.left = Math.max(parentRect.left, 0);
  }
  var offsets = getClientRect({
    top: childrenRect.top - parentRect.top - borderTopWidth,
    left: childrenRect.left - parentRect.left - borderLeftWidth,
    width: childrenRect.width,
    height: childrenRect.height
  });
  offsets.marginTop = 0;
  offsets.marginLeft = 0;

  // Subtract margins of documentElement in case it's being used as parent
  // we do this only on HTML because it's the only element that behaves
  // differently when margins are applied to it. The margins are included in
  // the box of the documentElement, in the other cases not.
  if (!isIE10 && isHTML) {
    var marginTop = parseFloat(styles.marginTop, 10);
    var marginLeft = parseFloat(styles.marginLeft, 10);

    offsets.top -= borderTopWidth - marginTop;
    offsets.bottom -= borderTopWidth - marginTop;
    offsets.left -= borderLeftWidth - marginLeft;
    offsets.right -= borderLeftWidth - marginLeft;

    // Attach marginTop and marginLeft because in some circumstances we may need them
    offsets.marginTop = marginTop;
    offsets.marginLeft = marginLeft;
  }

  if (isIE10 && !fixedPosition ? parent.contains(scrollParent) : parent === scrollParent && scrollParent.nodeName !== 'BODY') {
    offsets = includeScroll(offsets, parent);
  }

  return offsets;
}

function getViewportOffsetRectRelativeToArtbitraryNode(element) {
  var excludeScroll = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

  var html = element.ownerDocument.documentElement;
  var relativeOffset = getOffsetRectRelativeToArbitraryNode(element, html);
  var width = Math.max(html.clientWidth, window.innerWidth || 0);
  var height = Math.max(html.clientHeight, window.innerHeight || 0);

  var scrollTop = !excludeScroll ? getScroll(html) : 0;
  var scrollLeft = !excludeScroll ? getScroll(html, 'left') : 0;

  var offset = {
    top: scrollTop - relativeOffset.top + relativeOffset.marginTop,
    left: scrollLeft - relativeOffset.left + relativeOffset.marginLeft,
    width: width,
    height: height
  };

  return getClientRect(offset);
}

/**
 * Check if the given element is fixed or is inside a fixed parent
 * @method
 * @memberof Popper.Utils
 * @argument {Element} element
 * @argument {Element} customContainer
 * @returns {Boolean} answer to "isFixed?"
 */
function isFixed(element) {
  var nodeName = element.nodeName;
  if (nodeName === 'BODY' || nodeName === 'HTML') {
    return false;
  }
  if (getStyleComputedProperty(element, 'position') === 'fixed') {
    return true;
  }
  var parentNode = getParentNode(element);
  if (!parentNode) {
    return false;
  }
  return isFixed(parentNode);
}

/**
 * Finds the first parent of an element that has a transformed property defined
 * @method
 * @memberof Popper.Utils
 * @argument {Element} element
 * @returns {Element} first transformed parent or documentElement
 */

function getFixedPositionOffsetParent(element) {
  // This check is needed to avoid errors in case one of the elements isn't defined for any reason
  if (!element || !element.parentElement || isIE()) {
    return document.documentElement;
  }
  var el = element.parentElement;
  while (el && getStyleComputedProperty(el, 'transform') === 'none') {
    el = el.parentElement;
  }
  return el || document.documentElement;
}

/**
 * Computed the boundaries limits and return them
 * @method
 * @memberof Popper.Utils
 * @param {HTMLElement} popper
 * @param {HTMLElement} reference
 * @param {number} padding
 * @param {HTMLElement} boundariesElement - Element used to define the boundaries
 * @param {Boolean} fixedPosition - Is in fixed position mode
 * @returns {Object} Coordinates of the boundaries
 */
function getBoundaries(popper, reference, padding, boundariesElement) {
  var fixedPosition = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : false;

  // NOTE: 1 DOM access here

  var boundaries = { top: 0, left: 0 };
  var offsetParent = fixedPosition ? getFixedPositionOffsetParent(popper) : findCommonOffsetParent(popper, reference);

  // Handle viewport case
  if (boundariesElement === 'viewport') {
    boundaries = getViewportOffsetRectRelativeToArtbitraryNode(offsetParent, fixedPosition);
  } else {
    // Handle other cases based on DOM element used as boundaries
    var boundariesNode = void 0;
    if (boundariesElement === 'scrollParent') {
      boundariesNode = getScrollParent(getParentNode(reference));
      if (boundariesNode.nodeName === 'BODY') {
        boundariesNode = popper.ownerDocument.documentElement;
      }
    } else if (boundariesElement === 'window') {
      boundariesNode = popper.ownerDocument.documentElement;
    } else {
      boundariesNode = boundariesElement;
    }

    var offsets = getOffsetRectRelativeToArbitraryNode(boundariesNode, offsetParent, fixedPosition);

    // In case of HTML, we need a different computation
    if (boundariesNode.nodeName === 'HTML' && !isFixed(offsetParent)) {
      var _getWindowSizes = getWindowSizes(popper.ownerDocument),
          height = _getWindowSizes.height,
          width = _getWindowSizes.width;

      boundaries.top += offsets.top - offsets.marginTop;
      boundaries.bottom = height + offsets.top;
      boundaries.left += offsets.left - offsets.marginLeft;
      boundaries.right = width + offsets.left;
    } else {
      // for all the other DOM elements, this one is good
      boundaries = offsets;
    }
  }

  // Add paddings
  padding = padding || 0;
  var isPaddingNumber = typeof padding === 'number';
  boundaries.left += isPaddingNumber ? padding : padding.left || 0;
  boundaries.top += isPaddingNumber ? padding : padding.top || 0;
  boundaries.right -= isPaddingNumber ? padding : padding.right || 0;
  boundaries.bottom -= isPaddingNumber ? padding : padding.bottom || 0;

  return boundaries;
}

function getArea(_ref) {
  var width = _ref.width,
      height = _ref.height;

  return width * height;
}

/**
 * Utility used to transform the `auto` placement to the placement with more
 * available space.
 * @method
 * @memberof Popper.Utils
 * @argument {Object} data - The data object generated by update method
 * @argument {Object} options - Modifiers configuration and options
 * @returns {Object} The data object, properly modified
 */
function computeAutoPlacement(placement, refRect, popper, reference, boundariesElement) {
  var padding = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : 0;

  if (placement.indexOf('auto') === -1) {
    return placement;
  }

  var boundaries = getBoundaries(popper, reference, padding, boundariesElement);

  var rects = {
    top: {
      width: boundaries.width,
      height: refRect.top - boundaries.top
    },
    right: {
      width: boundaries.right - refRect.right,
      height: boundaries.height
    },
    bottom: {
      width: boundaries.width,
      height: boundaries.bottom - refRect.bottom
    },
    left: {
      width: refRect.left - boundaries.left,
      height: boundaries.height
    }
  };

  var sortedAreas = Object.keys(rects).map(function (key) {
    return _extends({
      key: key
    }, rects[key], {
      area: getArea(rects[key])
    });
  }).sort(function (a, b) {
    return b.area - a.area;
  });

  var filteredAreas = sortedAreas.filter(function (_ref2) {
    var width = _ref2.width,
        height = _ref2.height;
    return width >= popper.clientWidth && height >= popper.clientHeight;
  });

  var computedPlacement = filteredAreas.length > 0 ? filteredAreas[0].key : sortedAreas[0].key;

  var variation = placement.split('-')[1];

  return computedPlacement + (variation ? '-' + variation : '');
}

/**
 * Get offsets to the reference element
 * @method
 * @memberof Popper.Utils
 * @param {Object} state
 * @param {Element} popper - the popper element
 * @param {Element} reference - the reference element (the popper will be relative to this)
 * @param {Element} fixedPosition - is in fixed position mode
 * @returns {Object} An object containing the offsets which will be applied to the popper
 */
function getReferenceOffsets(state, popper, reference) {
  var fixedPosition = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;

  var commonOffsetParent = fixedPosition ? getFixedPositionOffsetParent(popper) : findCommonOffsetParent(popper, reference);
  return getOffsetRectRelativeToArbitraryNode(reference, commonOffsetParent, fixedPosition);
}

/**
 * Get the outer sizes of the given element (offset size + margins)
 * @method
 * @memberof Popper.Utils
 * @argument {Element} element
 * @returns {Object} object containing width and height properties
 */
function getOuterSizes(element) {
  var window = element.ownerDocument.defaultView;
  var styles = window.getComputedStyle(element);
  var x = parseFloat(styles.marginTop || 0) + parseFloat(styles.marginBottom || 0);
  var y = parseFloat(styles.marginLeft || 0) + parseFloat(styles.marginRight || 0);
  var result = {
    width: element.offsetWidth + y,
    height: element.offsetHeight + x
  };
  return result;
}

/**
 * Get the opposite placement of the given one
 * @method
 * @memberof Popper.Utils
 * @argument {String} placement
 * @returns {String} flipped placement
 */
function getOppositePlacement(placement) {
  var hash = { left: 'right', right: 'left', bottom: 'top', top: 'bottom' };
  return placement.replace(/left|right|bottom|top/g, function (matched) {
    return hash[matched];
  });
}

/**
 * Get offsets to the popper
 * @method
 * @memberof Popper.Utils
 * @param {Object} position - CSS position the Popper will get applied
 * @param {HTMLElement} popper - the popper element
 * @param {Object} referenceOffsets - the reference offsets (the popper will be relative to this)
 * @param {String} placement - one of the valid placement options
 * @returns {Object} popperOffsets - An object containing the offsets which will be applied to the popper
 */
function getPopperOffsets(popper, referenceOffsets, placement) {
  placement = placement.split('-')[0];

  // Get popper node sizes
  var popperRect = getOuterSizes(popper);

  // Add position, width and height to our offsets object
  var popperOffsets = {
    width: popperRect.width,
    height: popperRect.height
  };

  // depending by the popper placement we have to compute its offsets slightly differently
  var isHoriz = ['right', 'left'].indexOf(placement) !== -1;
  var mainSide = isHoriz ? 'top' : 'left';
  var secondarySide = isHoriz ? 'left' : 'top';
  var measurement = isHoriz ? 'height' : 'width';
  var secondaryMeasurement = !isHoriz ? 'height' : 'width';

  popperOffsets[mainSide] = referenceOffsets[mainSide] + referenceOffsets[measurement] / 2 - popperRect[measurement] / 2;
  if (placement === secondarySide) {
    popperOffsets[secondarySide] = referenceOffsets[secondarySide] - popperRect[secondaryMeasurement];
  } else {
    popperOffsets[secondarySide] = referenceOffsets[getOppositePlacement(secondarySide)];
  }

  return popperOffsets;
}

/**
 * Mimics the `find` method of Array
 * @method
 * @memberof Popper.Utils
 * @argument {Array} arr
 * @argument prop
 * @argument value
 * @returns index or -1
 */
function find(arr, check) {
  // use native find if supported
  if (Array.prototype.find) {
    return arr.find(check);
  }

  // use `filter` to obtain the same behavior of `find`
  return arr.filter(check)[0];
}

/**
 * Return the index of the matching object
 * @method
 * @memberof Popper.Utils
 * @argument {Array} arr
 * @argument prop
 * @argument value
 * @returns index or -1
 */
function findIndex(arr, prop, value) {
  // use native findIndex if supported
  if (Array.prototype.findIndex) {
    return arr.findIndex(function (cur) {
      return cur[prop] === value;
    });
  }

  // use `find` + `indexOf` if `findIndex` isn't supported
  var match = find(arr, function (obj) {
    return obj[prop] === value;
  });
  return arr.indexOf(match);
}

/**
 * Loop trough the list of modifiers and run them in order,
 * each of them will then edit the data object.
 * @method
 * @memberof Popper.Utils
 * @param {dataObject} data
 * @param {Array} modifiers
 * @param {String} ends - Optional modifier name used as stopper
 * @returns {dataObject}
 */
function runModifiers(modifiers, data, ends) {
  var modifiersToRun = ends === undefined ? modifiers : modifiers.slice(0, findIndex(modifiers, 'name', ends));

  modifiersToRun.forEach(function (modifier) {
    if (modifier['function']) {
      // eslint-disable-line dot-notation
      console.warn('`modifier.function` is deprecated, use `modifier.fn`!');
    }
    var fn = modifier['function'] || modifier.fn; // eslint-disable-line dot-notation
    if (modifier.enabled && isFunction(fn)) {
      // Add properties to offsets to make them a complete clientRect object
      // we do this before each modifier to make sure the previous one doesn't
      // mess with these values
      data.offsets.popper = getClientRect(data.offsets.popper);
      data.offsets.reference = getClientRect(data.offsets.reference);

      data = fn(data, modifier);
    }
  });

  return data;
}

/**
 * Updates the position of the popper, computing the new offsets and applying
 * the new style.<br />
 * Prefer `scheduleUpdate` over `update` because of performance reasons.
 * @method
 * @memberof Popper
 */
function update() {
  // if popper is destroyed, don't perform any further update
  if (this.state.isDestroyed) {
    return;
  }

  var data = {
    instance: this,
    styles: {},
    arrowStyles: {},
    attributes: {},
    flipped: false,
    offsets: {}
  };

  // compute reference element offsets
  data.offsets.reference = getReferenceOffsets(this.state, this.popper, this.reference, this.options.positionFixed);

  // compute auto placement, store placement inside the data object,
  // modifiers will be able to edit `placement` if needed
  // and refer to originalPlacement to know the original value
  data.placement = computeAutoPlacement(this.options.placement, data.offsets.reference, this.popper, this.reference, this.options.modifiers.flip.boundariesElement, this.options.modifiers.flip.padding);

  // store the computed placement inside `originalPlacement`
  data.originalPlacement = data.placement;

  data.positionFixed = this.options.positionFixed;

  // compute the popper offsets
  data.offsets.popper = getPopperOffsets(this.popper, data.offsets.reference, data.placement);

  data.offsets.popper.position = this.options.positionFixed ? 'fixed' : 'absolute';

  // run the modifiers
  data = runModifiers(this.modifiers, data);

  // the first `update` will call `onCreate` callback
  // the other ones will call `onUpdate` callback
  if (!this.state.isCreated) {
    this.state.isCreated = true;
    this.options.onCreate(data);
  } else {
    this.options.onUpdate(data);
  }
}

/**
 * Helper used to know if the given modifier is enabled.
 * @method
 * @memberof Popper.Utils
 * @returns {Boolean}
 */
function isModifierEnabled(modifiers, modifierName) {
  return modifiers.some(function (_ref) {
    var name = _ref.name,
        enabled = _ref.enabled;
    return enabled && name === modifierName;
  });
}

/**
 * Get the prefixed supported property name
 * @method
 * @memberof Popper.Utils
 * @argument {String} property (camelCase)
 * @returns {String} prefixed property (camelCase or PascalCase, depending on the vendor prefix)
 */
function getSupportedPropertyName(property) {
  var prefixes = [false, 'ms', 'Webkit', 'Moz', 'O'];
  var upperProp = property.charAt(0).toUpperCase() + property.slice(1);

  for (var i = 0; i < prefixes.length; i++) {
    var prefix = prefixes[i];
    var toCheck = prefix ? '' + prefix + upperProp : property;
    if (typeof document.body.style[toCheck] !== 'undefined') {
      return toCheck;
    }
  }
  return null;
}

/**
 * Destroys the popper.
 * @method
 * @memberof Popper
 */
function destroy() {
  this.state.isDestroyed = true;

  // touch DOM only if `applyStyle` modifier is enabled
  if (isModifierEnabled(this.modifiers, 'applyStyle')) {
    this.popper.removeAttribute('x-placement');
    this.popper.style.position = '';
    this.popper.style.top = '';
    this.popper.style.left = '';
    this.popper.style.right = '';
    this.popper.style.bottom = '';
    this.popper.style.willChange = '';
    this.popper.style[getSupportedPropertyName('transform')] = '';
  }

  this.disableEventListeners();

  // remove the popper if user explicity asked for the deletion on destroy
  // do not use `remove` because IE11 doesn't support it
  if (this.options.removeOnDestroy) {
    this.popper.parentNode.removeChild(this.popper);
  }
  return this;
}

/**
 * Get the window associated with the element
 * @argument {Element} element
 * @returns {Window}
 */
function getWindow(element) {
  var ownerDocument = element.ownerDocument;
  return ownerDocument ? ownerDocument.defaultView : window;
}

function attachToScrollParents(scrollParent, event, callback, scrollParents) {
  var isBody = scrollParent.nodeName === 'BODY';
  var target = isBody ? scrollParent.ownerDocument.defaultView : scrollParent;
  target.addEventListener(event, callback, { passive: true });

  if (!isBody) {
    attachToScrollParents(getScrollParent(target.parentNode), event, callback, scrollParents);
  }
  scrollParents.push(target);
}

/**
 * Setup needed event listeners used to update the popper position
 * @method
 * @memberof Popper.Utils
 * @private
 */
function setupEventListeners(reference, options, state, updateBound) {
  // Resize event listener on window
  state.updateBound = updateBound;
  getWindow(reference).addEventListener('resize', state.updateBound, { passive: true });

  // Scroll event listener on scroll parents
  var scrollElement = getScrollParent(reference);
  attachToScrollParents(scrollElement, 'scroll', state.updateBound, state.scrollParents);
  state.scrollElement = scrollElement;
  state.eventsEnabled = true;

  return state;
}

/**
 * It will add resize/scroll events and start recalculating
 * position of the popper element when they are triggered.
 * @method
 * @memberof Popper
 */
function enableEventListeners() {
  if (!this.state.eventsEnabled) {
    this.state = setupEventListeners(this.reference, this.options, this.state, this.scheduleUpdate);
  }
}

/**
 * Remove event listeners used to update the popper position
 * @method
 * @memberof Popper.Utils
 * @private
 */
function removeEventListeners(reference, state) {
  // Remove resize event listener on window
  getWindow(reference).removeEventListener('resize', state.updateBound);

  // Remove scroll event listener on scroll parents
  state.scrollParents.forEach(function (target) {
    target.removeEventListener('scroll', state.updateBound);
  });

  // Reset state
  state.updateBound = null;
  state.scrollParents = [];
  state.scrollElement = null;
  state.eventsEnabled = false;
  return state;
}

/**
 * It will remove resize/scroll events and won't recalculate popper position
 * when they are triggered. It also won't trigger `onUpdate` callback anymore,
 * unless you call `update` method manually.
 * @method
 * @memberof Popper
 */
function disableEventListeners() {
  if (this.state.eventsEnabled) {
    cancelAnimationFrame(this.scheduleUpdate);
    this.state = removeEventListeners(this.reference, this.state);
  }
}

/**
 * Tells if a given input is a number
 * @method
 * @memberof Popper.Utils
 * @param {*} input to check
 * @return {Boolean}
 */
function isNumeric(n) {
  return n !== '' && !isNaN(parseFloat(n)) && isFinite(n);
}

/**
 * Set the style to the given popper
 * @method
 * @memberof Popper.Utils
 * @argument {Element} element - Element to apply the style to
 * @argument {Object} styles
 * Object with a list of properties and values which will be applied to the element
 */
function setStyles(element, styles) {
  Object.keys(styles).forEach(function (prop) {
    var unit = '';
    // add unit if the value is numeric and is one of the following
    if (['width', 'height', 'top', 'right', 'bottom', 'left'].indexOf(prop) !== -1 && isNumeric(styles[prop])) {
      unit = 'px';
    }
    element.style[prop] = styles[prop] + unit;
  });
}

/**
 * Set the attributes to the given popper
 * @method
 * @memberof Popper.Utils
 * @argument {Element} element - Element to apply the attributes to
 * @argument {Object} styles
 * Object with a list of properties and values which will be applied to the element
 */
function setAttributes(element, attributes) {
  Object.keys(attributes).forEach(function (prop) {
    var value = attributes[prop];
    if (value !== false) {
      element.setAttribute(prop, attributes[prop]);
    } else {
      element.removeAttribute(prop);
    }
  });
}

/**
 * @function
 * @memberof Modifiers
 * @argument {Object} data - The data object generated by `update` method
 * @argument {Object} data.styles - List of style properties - values to apply to popper element
 * @argument {Object} data.attributes - List of attribute properties - values to apply to popper element
 * @argument {Object} options - Modifiers configuration and options
 * @returns {Object} The same data object
 */
function applyStyle(data) {
  // any property present in `data.styles` will be applied to the popper,
  // in this way we can make the 3rd party modifiers add custom styles to it
  // Be aware, modifiers could override the properties defined in the previous
  // lines of this modifier!
  setStyles(data.instance.popper, data.styles);

  // any property present in `data.attributes` will be applied to the popper,
  // they will be set as HTML attributes of the element
  setAttributes(data.instance.popper, data.attributes);

  // if arrowElement is defined and arrowStyles has some properties
  if (data.arrowElement && Object.keys(data.arrowStyles).length) {
    setStyles(data.arrowElement, data.arrowStyles);
  }

  return data;
}

/**
 * Set the x-placement attribute before everything else because it could be used
 * to add margins to the popper margins needs to be calculated to get the
 * correct popper offsets.
 * @method
 * @memberof Popper.modifiers
 * @param {HTMLElement} reference - The reference element used to position the popper
 * @param {HTMLElement} popper - The HTML element used as popper
 * @param {Object} options - Popper.js options
 */
function applyStyleOnLoad(reference, popper, options, modifierOptions, state) {
  // compute reference element offsets
  var referenceOffsets = getReferenceOffsets(state, popper, reference, options.positionFixed);

  // compute auto placement, store placement inside the data object,
  // modifiers will be able to edit `placement` if needed
  // and refer to originalPlacement to know the original value
  var placement = computeAutoPlacement(options.placement, referenceOffsets, popper, reference, options.modifiers.flip.boundariesElement, options.modifiers.flip.padding);

  popper.setAttribute('x-placement', placement);

  // Apply `position` to popper before anything else because
  // without the position applied we can't guarantee correct computations
  setStyles(popper, { position: options.positionFixed ? 'fixed' : 'absolute' });

  return options;
}

/**
 * @function
 * @memberof Popper.Utils
 * @argument {Object} data - The data object generated by `update` method
 * @argument {Boolean} shouldRound - If the offsets should be rounded at all
 * @returns {Object} The popper's position offsets rounded
 *
 * The tale of pixel-perfect positioning. It's still not 100% perfect, but as
 * good as it can be within reason.
 * Discussion here: https://github.com/FezVrasta/popper.js/pull/715
 *
 * Low DPI screens cause a popper to be blurry if not using full pixels (Safari
 * as well on High DPI screens).
 *
 * Firefox prefers no rounding for positioning and does not have blurriness on
 * high DPI screens.
 *
 * Only horizontal placement and left/right values need to be considered.
 */
function getRoundedOffsets(data, shouldRound) {
  var _data$offsets = data.offsets,
      popper = _data$offsets.popper,
      reference = _data$offsets.reference;
  var round = Math.round,
      floor = Math.floor;

  var noRound = function noRound(v) {
    return v;
  };

  var referenceWidth = round(reference.width);
  var popperWidth = round(popper.width);

  var isVertical = ['left', 'right'].indexOf(data.placement) !== -1;
  var isVariation = data.placement.indexOf('-') !== -1;
  var sameWidthParity = referenceWidth % 2 === popperWidth % 2;
  var bothOddWidth = referenceWidth % 2 === 1 && popperWidth % 2 === 1;

  var horizontalToInteger = !shouldRound ? noRound : isVertical || isVariation || sameWidthParity ? round : floor;
  var verticalToInteger = !shouldRound ? noRound : round;

  return {
    left: horizontalToInteger(bothOddWidth && !isVariation && shouldRound ? popper.left - 1 : popper.left),
    top: verticalToInteger(popper.top),
    bottom: verticalToInteger(popper.bottom),
    right: horizontalToInteger(popper.right)
  };
}

var isFirefox = isBrowser && /Firefox/i.test(navigator.userAgent);

/**
 * @function
 * @memberof Modifiers
 * @argument {Object} data - The data object generated by `update` method
 * @argument {Object} options - Modifiers configuration and options
 * @returns {Object} The data object, properly modified
 */
function computeStyle(data, options) {
  var x = options.x,
      y = options.y;
  var popper = data.offsets.popper;

  // Remove this legacy support in Popper.js v2

  var legacyGpuAccelerationOption = find(data.instance.modifiers, function (modifier) {
    return modifier.name === 'applyStyle';
  }).gpuAcceleration;
  if (legacyGpuAccelerationOption !== undefined) {
    console.warn('WARNING: `gpuAcceleration` option moved to `computeStyle` modifier and will not be supported in future versions of Popper.js!');
  }
  var gpuAcceleration = legacyGpuAccelerationOption !== undefined ? legacyGpuAccelerationOption : options.gpuAcceleration;

  var offsetParent = getOffsetParent(data.instance.popper);
  var offsetParentRect = getBoundingClientRect(offsetParent);

  // Styles
  var styles = {
    position: popper.position
  };

  var offsets = getRoundedOffsets(data, window.devicePixelRatio < 2 || !isFirefox);

  var sideA = x === 'bottom' ? 'top' : 'bottom';
  var sideB = y === 'right' ? 'left' : 'right';

  // if gpuAcceleration is set to `true` and transform is supported,
  //  we use `translate3d` to apply the position to the popper we
  // automatically use the supported prefixed version if needed
  var prefixedProperty = getSupportedPropertyName('transform');

  // now, let's make a step back and look at this code closely (wtf?)
  // If the content of the popper grows once it's been positioned, it
  // may happen that the popper gets misplaced because of the new content
  // overflowing its reference element
  // To avoid this problem, we provide two options (x and y), which allow
  // the consumer to define the offset origin.
  // If we position a popper on top of a reference element, we can set
  // `x` to `top` to make the popper grow towards its top instead of
  // its bottom.
  var left = void 0,
      top = void 0;
  if (sideA === 'bottom') {
    // when offsetParent is <html> the positioning is relative to the bottom of the screen (excluding the scrollbar)
    // and not the bottom of the html element
    if (offsetParent.nodeName === 'HTML') {
      top = -offsetParent.clientHeight + offsets.bottom;
    } else {
      top = -offsetParentRect.height + offsets.bottom;
    }
  } else {
    top = offsets.top;
  }
  if (sideB === 'right') {
    if (offsetParent.nodeName === 'HTML') {
      left = -offsetParent.clientWidth + offsets.right;
    } else {
      left = -offsetParentRect.width + offsets.right;
    }
  } else {
    left = offsets.left;
  }
  if (gpuAcceleration && prefixedProperty) {
    styles[prefixedProperty] = 'translate3d(' + left + 'px, ' + top + 'px, 0)';
    styles[sideA] = 0;
    styles[sideB] = 0;
    styles.willChange = 'transform';
  } else {
    // othwerise, we use the standard `top`, `left`, `bottom` and `right` properties
    var invertTop = sideA === 'bottom' ? -1 : 1;
    var invertLeft = sideB === 'right' ? -1 : 1;
    styles[sideA] = top * invertTop;
    styles[sideB] = left * invertLeft;
    styles.willChange = sideA + ', ' + sideB;
  }

  // Attributes
  var attributes = {
    'x-placement': data.placement
  };

  // Update `data` attributes, styles and arrowStyles
  data.attributes = _extends({}, attributes, data.attributes);
  data.styles = _extends({}, styles, data.styles);
  data.arrowStyles = _extends({}, data.offsets.arrow, data.arrowStyles);

  return data;
}

/**
 * Helper used to know if the given modifier depends from another one.<br />
 * It checks if the needed modifier is listed and enabled.
 * @method
 * @memberof Popper.Utils
 * @param {Array} modifiers - list of modifiers
 * @param {String} requestingName - name of requesting modifier
 * @param {String} requestedName - name of requested modifier
 * @returns {Boolean}
 */
function isModifierRequired(modifiers, requestingName, requestedName) {
  var requesting = find(modifiers, function (_ref) {
    var name = _ref.name;
    return name === requestingName;
  });

  var isRequired = !!requesting && modifiers.some(function (modifier) {
    return modifier.name === requestedName && modifier.enabled && modifier.order < requesting.order;
  });

  if (!isRequired) {
    var _requesting = '`' + requestingName + '`';
    var requested = '`' + requestedName + '`';
    console.warn(requested + ' modifier is required by ' + _requesting + ' modifier in order to work, be sure to include it before ' + _requesting + '!');
  }
  return isRequired;
}

/**
 * @function
 * @memberof Modifiers
 * @argument {Object} data - The data object generated by update method
 * @argument {Object} options - Modifiers configuration and options
 * @returns {Object} The data object, properly modified
 */
function arrow(data, options) {
  var _data$offsets$arrow;

  // arrow depends on keepTogether in order to work
  if (!isModifierRequired(data.instance.modifiers, 'arrow', 'keepTogether')) {
    return data;
  }

  var arrowElement = options.element;

  // if arrowElement is a string, suppose it's a CSS selector
  if (typeof arrowElement === 'string') {
    arrowElement = data.instance.popper.querySelector(arrowElement);

    // if arrowElement is not found, don't run the modifier
    if (!arrowElement) {
      return data;
    }
  } else {
    // if the arrowElement isn't a query selector we must check that the
    // provided DOM node is child of its popper node
    if (!data.instance.popper.contains(arrowElement)) {
      console.warn('WARNING: `arrow.element` must be child of its popper element!');
      return data;
    }
  }

  var placement = data.placement.split('-')[0];
  var _data$offsets = data.offsets,
      popper = _data$offsets.popper,
      reference = _data$offsets.reference;

  var isVertical = ['left', 'right'].indexOf(placement) !== -1;

  var len = isVertical ? 'height' : 'width';
  var sideCapitalized = isVertical ? 'Top' : 'Left';
  var side = sideCapitalized.toLowerCase();
  var altSide = isVertical ? 'left' : 'top';
  var opSide = isVertical ? 'bottom' : 'right';
  var arrowElementSize = getOuterSizes(arrowElement)[len];

  //
  // extends keepTogether behavior making sure the popper and its
  // reference have enough pixels in conjunction
  //

  // top/left side
  if (reference[opSide] - arrowElementSize < popper[side]) {
    data.offsets.popper[side] -= popper[side] - (reference[opSide] - arrowElementSize);
  }
  // bottom/right side
  if (reference[side] + arrowElementSize > popper[opSide]) {
    data.offsets.popper[side] += reference[side] + arrowElementSize - popper[opSide];
  }
  data.offsets.popper = getClientRect(data.offsets.popper);

  // compute center of the popper
  var center = reference[side] + reference[len] / 2 - arrowElementSize / 2;

  // Compute the sideValue using the updated popper offsets
  // take popper margin in account because we don't have this info available
  var css = getStyleComputedProperty(data.instance.popper);
  var popperMarginSide = parseFloat(css['margin' + sideCapitalized], 10);
  var popperBorderSide = parseFloat(css['border' + sideCapitalized + 'Width'], 10);
  var sideValue = center - data.offsets.popper[side] - popperMarginSide - popperBorderSide;

  // prevent arrowElement from being placed not contiguously to its popper
  sideValue = Math.max(Math.min(popper[len] - arrowElementSize, sideValue), 0);

  data.arrowElement = arrowElement;
  data.offsets.arrow = (_data$offsets$arrow = {}, defineProperty(_data$offsets$arrow, side, Math.round(sideValue)), defineProperty(_data$offsets$arrow, altSide, ''), _data$offsets$arrow);

  return data;
}

/**
 * Get the opposite placement variation of the given one
 * @method
 * @memberof Popper.Utils
 * @argument {String} placement variation
 * @returns {String} flipped placement variation
 */
function getOppositeVariation(variation) {
  if (variation === 'end') {
    return 'start';
  } else if (variation === 'start') {
    return 'end';
  }
  return variation;
}

/**
 * List of accepted placements to use as values of the `placement` option.<br />
 * Valid placements are:
 * - `auto`
 * - `top`
 * - `right`
 * - `bottom`
 * - `left`
 *
 * Each placement can have a variation from this list:
 * - `-start`
 * - `-end`
 *
 * Variations are interpreted easily if you think of them as the left to right
 * written languages. Horizontally (`top` and `bottom`), `start` is left and `end`
 * is right.<br />
 * Vertically (`left` and `right`), `start` is top and `end` is bottom.
 *
 * Some valid examples are:
 * - `top-end` (on top of reference, right aligned)
 * - `right-start` (on right of reference, top aligned)
 * - `bottom` (on bottom, centered)
 * - `auto-end` (on the side with more space available, alignment depends by placement)
 *
 * @static
 * @type {Array}
 * @enum {String}
 * @readonly
 * @method placements
 * @memberof Popper
 */
var placements = ['auto-start', 'auto', 'auto-end', 'top-start', 'top', 'top-end', 'right-start', 'right', 'right-end', 'bottom-end', 'bottom', 'bottom-start', 'left-end', 'left', 'left-start'];

// Get rid of `auto` `auto-start` and `auto-end`
var validPlacements = placements.slice(3);

/**
 * Given an initial placement, returns all the subsequent placements
 * clockwise (or counter-clockwise).
 *
 * @method
 * @memberof Popper.Utils
 * @argument {String} placement - A valid placement (it accepts variations)
 * @argument {Boolean} counter - Set to true to walk the placements counterclockwise
 * @returns {Array} placements including their variations
 */
function clockwise(placement) {
  var counter = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

  var index = validPlacements.indexOf(placement);
  var arr = validPlacements.slice(index + 1).concat(validPlacements.slice(0, index));
  return counter ? arr.reverse() : arr;
}

var BEHAVIORS = {
  FLIP: 'flip',
  CLOCKWISE: 'clockwise',
  COUNTERCLOCKWISE: 'counterclockwise'
};

/**
 * @function
 * @memberof Modifiers
 * @argument {Object} data - The data object generated by update method
 * @argument {Object} options - Modifiers configuration and options
 * @returns {Object} The data object, properly modified
 */
function flip(data, options) {
  // if `inner` modifier is enabled, we can't use the `flip` modifier
  if (isModifierEnabled(data.instance.modifiers, 'inner')) {
    return data;
  }

  if (data.flipped && data.placement === data.originalPlacement) {
    // seems like flip is trying to loop, probably there's not enough space on any of the flippable sides
    return data;
  }

  var boundaries = getBoundaries(data.instance.popper, data.instance.reference, options.padding, options.boundariesElement, data.positionFixed);

  var placement = data.placement.split('-')[0];
  var placementOpposite = getOppositePlacement(placement);
  var variation = data.placement.split('-')[1] || '';

  var flipOrder = [];

  switch (options.behavior) {
    case BEHAVIORS.FLIP:
      flipOrder = [placement, placementOpposite];
      break;
    case BEHAVIORS.CLOCKWISE:
      flipOrder = clockwise(placement);
      break;
    case BEHAVIORS.COUNTERCLOCKWISE:
      flipOrder = clockwise(placement, true);
      break;
    default:
      flipOrder = options.behavior;
  }

  flipOrder.forEach(function (step, index) {
    if (placement !== step || flipOrder.length === index + 1) {
      return data;
    }

    placement = data.placement.split('-')[0];
    placementOpposite = getOppositePlacement(placement);

    var popperOffsets = data.offsets.popper;
    var refOffsets = data.offsets.reference;

    // using floor because the reference offsets may contain decimals we are not going to consider here
    var floor = Math.floor;
    var overlapsRef = placement === 'left' && floor(popperOffsets.right) > floor(refOffsets.left) || placement === 'right' && floor(popperOffsets.left) < floor(refOffsets.right) || placement === 'top' && floor(popperOffsets.bottom) > floor(refOffsets.top) || placement === 'bottom' && floor(popperOffsets.top) < floor(refOffsets.bottom);

    var overflowsLeft = floor(popperOffsets.left) < floor(boundaries.left);
    var overflowsRight = floor(popperOffsets.right) > floor(boundaries.right);
    var overflowsTop = floor(popperOffsets.top) < floor(boundaries.top);
    var overflowsBottom = floor(popperOffsets.bottom) > floor(boundaries.bottom);

    var overflowsBoundaries = placement === 'left' && overflowsLeft || placement === 'right' && overflowsRight || placement === 'top' && overflowsTop || placement === 'bottom' && overflowsBottom;

    // flip the variation if required
    var isVertical = ['top', 'bottom'].indexOf(placement) !== -1;

    // flips variation if reference element overflows boundaries
    var flippedVariationByRef = !!options.flipVariations && (isVertical && variation === 'start' && overflowsLeft || isVertical && variation === 'end' && overflowsRight || !isVertical && variation === 'start' && overflowsTop || !isVertical && variation === 'end' && overflowsBottom);

    // flips variation if popper content overflows boundaries
    var flippedVariationByContent = !!options.flipVariationsByContent && (isVertical && variation === 'start' && overflowsRight || isVertical && variation === 'end' && overflowsLeft || !isVertical && variation === 'start' && overflowsBottom || !isVertical && variation === 'end' && overflowsTop);

    var flippedVariation = flippedVariationByRef || flippedVariationByContent;

    if (overlapsRef || overflowsBoundaries || flippedVariation) {
      // this boolean to detect any flip loop
      data.flipped = true;

      if (overlapsRef || overflowsBoundaries) {
        placement = flipOrder[index + 1];
      }

      if (flippedVariation) {
        variation = getOppositeVariation(variation);
      }

      data.placement = placement + (variation ? '-' + variation : '');

      // this object contains `position`, we want to preserve it along with
      // any additional property we may add in the future
      data.offsets.popper = _extends({}, data.offsets.popper, getPopperOffsets(data.instance.popper, data.offsets.reference, data.placement));

      data = runModifiers(data.instance.modifiers, data, 'flip');
    }
  });
  return data;
}

/**
 * @function
 * @memberof Modifiers
 * @argument {Object} data - The data object generated by update method
 * @argument {Object} options - Modifiers configuration and options
 * @returns {Object} The data object, properly modified
 */
function keepTogether(data) {
  var _data$offsets = data.offsets,
      popper = _data$offsets.popper,
      reference = _data$offsets.reference;

  var placement = data.placement.split('-')[0];
  var floor = Math.floor;
  var isVertical = ['top', 'bottom'].indexOf(placement) !== -1;
  var side = isVertical ? 'right' : 'bottom';
  var opSide = isVertical ? 'left' : 'top';
  var measurement = isVertical ? 'width' : 'height';

  if (popper[side] < floor(reference[opSide])) {
    data.offsets.popper[opSide] = floor(reference[opSide]) - popper[measurement];
  }
  if (popper[opSide] > floor(reference[side])) {
    data.offsets.popper[opSide] = floor(reference[side]);
  }

  return data;
}

/**
 * Converts a string containing value + unit into a px value number
 * @function
 * @memberof {modifiers~offset}
 * @private
 * @argument {String} str - Value + unit string
 * @argument {String} measurement - `height` or `width`
 * @argument {Object} popperOffsets
 * @argument {Object} referenceOffsets
 * @returns {Number|String}
 * Value in pixels, or original string if no values were extracted
 */
function toValue(str, measurement, popperOffsets, referenceOffsets) {
  // separate value from unit
  var split = str.match(/((?:\-|\+)?\d*\.?\d*)(.*)/);
  var value = +split[1];
  var unit = split[2];

  // If it's not a number it's an operator, I guess
  if (!value) {
    return str;
  }

  if (unit.indexOf('%') === 0) {
    var element = void 0;
    switch (unit) {
      case '%p':
        element = popperOffsets;
        break;
      case '%':
      case '%r':
      default:
        element = referenceOffsets;
    }

    var rect = getClientRect(element);
    return rect[measurement] / 100 * value;
  } else if (unit === 'vh' || unit === 'vw') {
    // if is a vh or vw, we calculate the size based on the viewport
    var size = void 0;
    if (unit === 'vh') {
      size = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
    } else {
      size = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    }
    return size / 100 * value;
  } else {
    // if is an explicit pixel unit, we get rid of the unit and keep the value
    // if is an implicit unit, it's px, and we return just the value
    return value;
  }
}

/**
 * Parse an `offset` string to extrapolate `x` and `y` numeric offsets.
 * @function
 * @memberof {modifiers~offset}
 * @private
 * @argument {String} offset
 * @argument {Object} popperOffsets
 * @argument {Object} referenceOffsets
 * @argument {String} basePlacement
 * @returns {Array} a two cells array with x and y offsets in numbers
 */
function parseOffset(offset, popperOffsets, referenceOffsets, basePlacement) {
  var offsets = [0, 0];

  // Use height if placement is left or right and index is 0 otherwise use width
  // in this way the first offset will use an axis and the second one
  // will use the other one
  var useHeight = ['right', 'left'].indexOf(basePlacement) !== -1;

  // Split the offset string to obtain a list of values and operands
  // The regex addresses values with the plus or minus sign in front (+10, -20, etc)
  var fragments = offset.split(/(\+|\-)/).map(function (frag) {
    return frag.trim();
  });

  // Detect if the offset string contains a pair of values or a single one
  // they could be separated by comma or space
  var divider = fragments.indexOf(find(fragments, function (frag) {
    return frag.search(/,|\s/) !== -1;
  }));

  if (fragments[divider] && fragments[divider].indexOf(',') === -1) {
    console.warn('Offsets separated by white space(s) are deprecated, use a comma (,) instead.');
  }

  // If divider is found, we divide the list of values and operands to divide
  // them by ofset X and Y.
  var splitRegex = /\s*,\s*|\s+/;
  var ops = divider !== -1 ? [fragments.slice(0, divider).concat([fragments[divider].split(splitRegex)[0]]), [fragments[divider].split(splitRegex)[1]].concat(fragments.slice(divider + 1))] : [fragments];

  // Convert the values with units to absolute pixels to allow our computations
  ops = ops.map(function (op, index) {
    // Most of the units rely on the orientation of the popper
    var measurement = (index === 1 ? !useHeight : useHeight) ? 'height' : 'width';
    var mergeWithPrevious = false;
    return op
    // This aggregates any `+` or `-` sign that aren't considered operators
    // e.g.: 10 + +5 => [10, +, +5]
    .reduce(function (a, b) {
      if (a[a.length - 1] === '' && ['+', '-'].indexOf(b) !== -1) {
        a[a.length - 1] = b;
        mergeWithPrevious = true;
        return a;
      } else if (mergeWithPrevious) {
        a[a.length - 1] += b;
        mergeWithPrevious = false;
        return a;
      } else {
        return a.concat(b);
      }
    }, [])
    // Here we convert the string values into number values (in px)
    .map(function (str) {
      return toValue(str, measurement, popperOffsets, referenceOffsets);
    });
  });

  // Loop trough the offsets arrays and execute the operations
  ops.forEach(function (op, index) {
    op.forEach(function (frag, index2) {
      if (isNumeric(frag)) {
        offsets[index] += frag * (op[index2 - 1] === '-' ? -1 : 1);
      }
    });
  });
  return offsets;
}

/**
 * @function
 * @memberof Modifiers
 * @argument {Object} data - The data object generated by update method
 * @argument {Object} options - Modifiers configuration and options
 * @argument {Number|String} options.offset=0
 * The offset value as described in the modifier description
 * @returns {Object} The data object, properly modified
 */
function offset(data, _ref) {
  var offset = _ref.offset;
  var placement = data.placement,
      _data$offsets = data.offsets,
      popper = _data$offsets.popper,
      reference = _data$offsets.reference;

  var basePlacement = placement.split('-')[0];

  var offsets = void 0;
  if (isNumeric(+offset)) {
    offsets = [+offset, 0];
  } else {
    offsets = parseOffset(offset, popper, reference, basePlacement);
  }

  if (basePlacement === 'left') {
    popper.top += offsets[0];
    popper.left -= offsets[1];
  } else if (basePlacement === 'right') {
    popper.top += offsets[0];
    popper.left += offsets[1];
  } else if (basePlacement === 'top') {
    popper.left += offsets[0];
    popper.top -= offsets[1];
  } else if (basePlacement === 'bottom') {
    popper.left += offsets[0];
    popper.top += offsets[1];
  }

  data.popper = popper;
  return data;
}

/**
 * @function
 * @memberof Modifiers
 * @argument {Object} data - The data object generated by `update` method
 * @argument {Object} options - Modifiers configuration and options
 * @returns {Object} The data object, properly modified
 */
function preventOverflow(data, options) {
  var boundariesElement = options.boundariesElement || getOffsetParent(data.instance.popper);

  // If offsetParent is the reference element, we really want to
  // go one step up and use the next offsetParent as reference to
  // avoid to make this modifier completely useless and look like broken
  if (data.instance.reference === boundariesElement) {
    boundariesElement = getOffsetParent(boundariesElement);
  }

  // NOTE: DOM access here
  // resets the popper's position so that the document size can be calculated excluding
  // the size of the popper element itself
  var transformProp = getSupportedPropertyName('transform');
  var popperStyles = data.instance.popper.style; // assignment to help minification
  var top = popperStyles.top,
      left = popperStyles.left,
      transform = popperStyles[transformProp];

  popperStyles.top = '';
  popperStyles.left = '';
  popperStyles[transformProp] = '';

  var boundaries = getBoundaries(data.instance.popper, data.instance.reference, options.padding, boundariesElement, data.positionFixed);

  // NOTE: DOM access here
  // restores the original style properties after the offsets have been computed
  popperStyles.top = top;
  popperStyles.left = left;
  popperStyles[transformProp] = transform;

  options.boundaries = boundaries;

  var order = options.priority;
  var popper = data.offsets.popper;

  var check = {
    primary: function primary(placement) {
      var value = popper[placement];
      if (popper[placement] < boundaries[placement] && !options.escapeWithReference) {
        value = Math.max(popper[placement], boundaries[placement]);
      }
      return defineProperty({}, placement, value);
    },
    secondary: function secondary(placement) {
      var mainSide = placement === 'right' ? 'left' : 'top';
      var value = popper[mainSide];
      if (popper[placement] > boundaries[placement] && !options.escapeWithReference) {
        value = Math.min(popper[mainSide], boundaries[placement] - (placement === 'right' ? popper.width : popper.height));
      }
      return defineProperty({}, mainSide, value);
    }
  };

  order.forEach(function (placement) {
    var side = ['left', 'top'].indexOf(placement) !== -1 ? 'primary' : 'secondary';
    popper = _extends({}, popper, check[side](placement));
  });

  data.offsets.popper = popper;

  return data;
}

/**
 * @function
 * @memberof Modifiers
 * @argument {Object} data - The data object generated by `update` method
 * @argument {Object} options - Modifiers configuration and options
 * @returns {Object} The data object, properly modified
 */
function shift(data) {
  var placement = data.placement;
  var basePlacement = placement.split('-')[0];
  var shiftvariation = placement.split('-')[1];

  // if shift shiftvariation is specified, run the modifier
  if (shiftvariation) {
    var _data$offsets = data.offsets,
        reference = _data$offsets.reference,
        popper = _data$offsets.popper;

    var isVertical = ['bottom', 'top'].indexOf(basePlacement) !== -1;
    var side = isVertical ? 'left' : 'top';
    var measurement = isVertical ? 'width' : 'height';

    var shiftOffsets = {
      start: defineProperty({}, side, reference[side]),
      end: defineProperty({}, side, reference[side] + reference[measurement] - popper[measurement])
    };

    data.offsets.popper = _extends({}, popper, shiftOffsets[shiftvariation]);
  }

  return data;
}

/**
 * @function
 * @memberof Modifiers
 * @argument {Object} data - The data object generated by update method
 * @argument {Object} options - Modifiers configuration and options
 * @returns {Object} The data object, properly modified
 */
function hide(data) {
  if (!isModifierRequired(data.instance.modifiers, 'hide', 'preventOverflow')) {
    return data;
  }

  var refRect = data.offsets.reference;
  var bound = find(data.instance.modifiers, function (modifier) {
    return modifier.name === 'preventOverflow';
  }).boundaries;

  if (refRect.bottom < bound.top || refRect.left > bound.right || refRect.top > bound.bottom || refRect.right < bound.left) {
    // Avoid unnecessary DOM access if visibility hasn't changed
    if (data.hide === true) {
      return data;
    }

    data.hide = true;
    data.attributes['x-out-of-boundaries'] = '';
  } else {
    // Avoid unnecessary DOM access if visibility hasn't changed
    if (data.hide === false) {
      return data;
    }

    data.hide = false;
    data.attributes['x-out-of-boundaries'] = false;
  }

  return data;
}

/**
 * @function
 * @memberof Modifiers
 * @argument {Object} data - The data object generated by `update` method
 * @argument {Object} options - Modifiers configuration and options
 * @returns {Object} The data object, properly modified
 */
function inner(data) {
  var placement = data.placement;
  var basePlacement = placement.split('-')[0];
  var _data$offsets = data.offsets,
      popper = _data$offsets.popper,
      reference = _data$offsets.reference;

  var isHoriz = ['left', 'right'].indexOf(basePlacement) !== -1;

  var subtractLength = ['top', 'left'].indexOf(basePlacement) === -1;

  popper[isHoriz ? 'left' : 'top'] = reference[basePlacement] - (subtractLength ? popper[isHoriz ? 'width' : 'height'] : 0);

  data.placement = getOppositePlacement(placement);
  data.offsets.popper = getClientRect(popper);

  return data;
}

/**
 * Modifier function, each modifier can have a function of this type assigned
 * to its `fn` property.<br />
 * These functions will be called on each update, this means that you must
 * make sure they are performant enough to avoid performance bottlenecks.
 *
 * @function ModifierFn
 * @argument {dataObject} data - The data object generated by `update` method
 * @argument {Object} options - Modifiers configuration and options
 * @returns {dataObject} The data object, properly modified
 */

/**
 * Modifiers are plugins used to alter the behavior of your poppers.<br />
 * Popper.js uses a set of 9 modifiers to provide all the basic functionalities
 * needed by the library.
 *
 * Usually you don't want to override the `order`, `fn` and `onLoad` props.
 * All the other properties are configurations that could be tweaked.
 * @namespace modifiers
 */
var modifiers = {
  /**
   * Modifier used to shift the popper on the start or end of its reference
   * element.<br />
   * It will read the variation of the `placement` property.<br />
   * It can be one either `-end` or `-start`.
   * @memberof modifiers
   * @inner
   */
  shift: {
    /** @prop {number} order=100 - Index used to define the order of execution */
    order: 100,
    /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
    enabled: true,
    /** @prop {ModifierFn} */
    fn: shift
  },

  /**
   * The `offset` modifier can shift your popper on both its axis.
   *
   * It accepts the following units:
   * - `px` or unit-less, interpreted as pixels
   * - `%` or `%r`, percentage relative to the length of the reference element
   * - `%p`, percentage relative to the length of the popper element
   * - `vw`, CSS viewport width unit
   * - `vh`, CSS viewport height unit
   *
   * For length is intended the main axis relative to the placement of the popper.<br />
   * This means that if the placement is `top` or `bottom`, the length will be the
   * `width`. In case of `left` or `right`, it will be the `height`.
   *
   * You can provide a single value (as `Number` or `String`), or a pair of values
   * as `String` divided by a comma or one (or more) white spaces.<br />
   * The latter is a deprecated method because it leads to confusion and will be
   * removed in v2.<br />
   * Additionally, it accepts additions and subtractions between different units.
   * Note that multiplications and divisions aren't supported.
   *
   * Valid examples are:
   * ```
   * 10
   * '10%'
   * '10, 10'
   * '10%, 10'
   * '10 + 10%'
   * '10 - 5vh + 3%'
   * '-10px + 5vh, 5px - 6%'
   * ```
   * > **NB**: If you desire to apply offsets to your poppers in a way that may make them overlap
   * > with their reference element, unfortunately, you will have to disable the `flip` modifier.
   * > You can read more on this at this [issue](https://github.com/FezVrasta/popper.js/issues/373).
   *
   * @memberof modifiers
   * @inner
   */
  offset: {
    /** @prop {number} order=200 - Index used to define the order of execution */
    order: 200,
    /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
    enabled: true,
    /** @prop {ModifierFn} */
    fn: offset,
    /** @prop {Number|String} offset=0
     * The offset value as described in the modifier description
     */
    offset: 0
  },

  /**
   * Modifier used to prevent the popper from being positioned outside the boundary.
   *
   * A scenario exists where the reference itself is not within the boundaries.<br />
   * We can say it has "escaped the boundaries" — or just "escaped".<br />
   * In this case we need to decide whether the popper should either:
   *
   * - detach from the reference and remain "trapped" in the boundaries, or
   * - if it should ignore the boundary and "escape with its reference"
   *
   * When `escapeWithReference` is set to`true` and reference is completely
   * outside its boundaries, the popper will overflow (or completely leave)
   * the boundaries in order to remain attached to the edge of the reference.
   *
   * @memberof modifiers
   * @inner
   */
  preventOverflow: {
    /** @prop {number} order=300 - Index used to define the order of execution */
    order: 300,
    /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
    enabled: true,
    /** @prop {ModifierFn} */
    fn: preventOverflow,
    /**
     * @prop {Array} [priority=['left','right','top','bottom']]
     * Popper will try to prevent overflow following these priorities by default,
     * then, it could overflow on the left and on top of the `boundariesElement`
     */
    priority: ['left', 'right', 'top', 'bottom'],
    /**
     * @prop {number} padding=5
     * Amount of pixel used to define a minimum distance between the boundaries
     * and the popper. This makes sure the popper always has a little padding
     * between the edges of its container
     */
    padding: 5,
    /**
     * @prop {String|HTMLElement} boundariesElement='scrollParent'
     * Boundaries used by the modifier. Can be `scrollParent`, `window`,
     * `viewport` or any DOM element.
     */
    boundariesElement: 'scrollParent'
  },

  /**
   * Modifier used to make sure the reference and its popper stay near each other
   * without leaving any gap between the two. Especially useful when the arrow is
   * enabled and you want to ensure that it points to its reference element.
   * It cares only about the first axis. You can still have poppers with margin
   * between the popper and its reference element.
   * @memberof modifiers
   * @inner
   */
  keepTogether: {
    /** @prop {number} order=400 - Index used to define the order of execution */
    order: 400,
    /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
    enabled: true,
    /** @prop {ModifierFn} */
    fn: keepTogether
  },

  /**
   * This modifier is used to move the `arrowElement` of the popper to make
   * sure it is positioned between the reference element and its popper element.
   * It will read the outer size of the `arrowElement` node to detect how many
   * pixels of conjunction are needed.
   *
   * It has no effect if no `arrowElement` is provided.
   * @memberof modifiers
   * @inner
   */
  arrow: {
    /** @prop {number} order=500 - Index used to define the order of execution */
    order: 500,
    /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
    enabled: true,
    /** @prop {ModifierFn} */
    fn: arrow,
    /** @prop {String|HTMLElement} element='[x-arrow]' - Selector or node used as arrow */
    element: '[x-arrow]'
  },

  /**
   * Modifier used to flip the popper's placement when it starts to overlap its
   * reference element.
   *
   * Requires the `preventOverflow` modifier before it in order to work.
   *
   * **NOTE:** this modifier will interrupt the current update cycle and will
   * restart it if it detects the need to flip the placement.
   * @memberof modifiers
   * @inner
   */
  flip: {
    /** @prop {number} order=600 - Index used to define the order of execution */
    order: 600,
    /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
    enabled: true,
    /** @prop {ModifierFn} */
    fn: flip,
    /**
     * @prop {String|Array} behavior='flip'
     * The behavior used to change the popper's placement. It can be one of
     * `flip`, `clockwise`, `counterclockwise` or an array with a list of valid
     * placements (with optional variations)
     */
    behavior: 'flip',
    /**
     * @prop {number} padding=5
     * The popper will flip if it hits the edges of the `boundariesElement`
     */
    padding: 5,
    /**
     * @prop {String|HTMLElement} boundariesElement='viewport'
     * The element which will define the boundaries of the popper position.
     * The popper will never be placed outside of the defined boundaries
     * (except if `keepTogether` is enabled)
     */
    boundariesElement: 'viewport',
    /**
     * @prop {Boolean} flipVariations=false
     * The popper will switch placement variation between `-start` and `-end` when
     * the reference element overlaps its boundaries.
     *
     * The original placement should have a set variation.
     */
    flipVariations: false,
    /**
     * @prop {Boolean} flipVariationsByContent=false
     * The popper will switch placement variation between `-start` and `-end` when
     * the popper element overlaps its reference boundaries.
     *
     * The original placement should have a set variation.
     */
    flipVariationsByContent: false
  },

  /**
   * Modifier used to make the popper flow toward the inner of the reference element.
   * By default, when this modifier is disabled, the popper will be placed outside
   * the reference element.
   * @memberof modifiers
   * @inner
   */
  inner: {
    /** @prop {number} order=700 - Index used to define the order of execution */
    order: 700,
    /** @prop {Boolean} enabled=false - Whether the modifier is enabled or not */
    enabled: false,
    /** @prop {ModifierFn} */
    fn: inner
  },

  /**
   * Modifier used to hide the popper when its reference element is outside of the
   * popper boundaries. It will set a `x-out-of-boundaries` attribute which can
   * be used to hide with a CSS selector the popper when its reference is
   * out of boundaries.
   *
   * Requires the `preventOverflow` modifier before it in order to work.
   * @memberof modifiers
   * @inner
   */
  hide: {
    /** @prop {number} order=800 - Index used to define the order of execution */
    order: 800,
    /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
    enabled: true,
    /** @prop {ModifierFn} */
    fn: hide
  },

  /**
   * Computes the style that will be applied to the popper element to gets
   * properly positioned.
   *
   * Note that this modifier will not touch the DOM, it just prepares the styles
   * so that `applyStyle` modifier can apply it. This separation is useful
   * in case you need to replace `applyStyle` with a custom implementation.
   *
   * This modifier has `850` as `order` value to maintain backward compatibility
   * with previous versions of Popper.js. Expect the modifiers ordering method
   * to change in future major versions of the library.
   *
   * @memberof modifiers
   * @inner
   */
  computeStyle: {
    /** @prop {number} order=850 - Index used to define the order of execution */
    order: 850,
    /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
    enabled: true,
    /** @prop {ModifierFn} */
    fn: computeStyle,
    /**
     * @prop {Boolean} gpuAcceleration=true
     * If true, it uses the CSS 3D transformation to position the popper.
     * Otherwise, it will use the `top` and `left` properties
     */
    gpuAcceleration: true,
    /**
     * @prop {string} [x='bottom']
     * Where to anchor the X axis (`bottom` or `top`). AKA X offset origin.
     * Change this if your popper should grow in a direction different from `bottom`
     */
    x: 'bottom',
    /**
     * @prop {string} [x='left']
     * Where to anchor the Y axis (`left` or `right`). AKA Y offset origin.
     * Change this if your popper should grow in a direction different from `right`
     */
    y: 'right'
  },

  /**
   * Applies the computed styles to the popper element.
   *
   * All the DOM manipulations are limited to this modifier. This is useful in case
   * you want to integrate Popper.js inside a framework or view library and you
   * want to delegate all the DOM manipulations to it.
   *
   * Note that if you disable this modifier, you must make sure the popper element
   * has its position set to `absolute` before Popper.js can do its work!
   *
   * Just disable this modifier and define your own to achieve the desired effect.
   *
   * @memberof modifiers
   * @inner
   */
  applyStyle: {
    /** @prop {number} order=900 - Index used to define the order of execution */
    order: 900,
    /** @prop {Boolean} enabled=true - Whether the modifier is enabled or not */
    enabled: true,
    /** @prop {ModifierFn} */
    fn: applyStyle,
    /** @prop {Function} */
    onLoad: applyStyleOnLoad,
    /**
     * @deprecated since version 1.10.0, the property moved to `computeStyle` modifier
     * @prop {Boolean} gpuAcceleration=true
     * If true, it uses the CSS 3D transformation to position the popper.
     * Otherwise, it will use the `top` and `left` properties
     */
    gpuAcceleration: undefined
  }
};

/**
 * The `dataObject` is an object containing all the information used by Popper.js.
 * This object is passed to modifiers and to the `onCreate` and `onUpdate` callbacks.
 * @name dataObject
 * @property {Object} data.instance The Popper.js instance
 * @property {String} data.placement Placement applied to popper
 * @property {String} data.originalPlacement Placement originally defined on init
 * @property {Boolean} data.flipped True if popper has been flipped by flip modifier
 * @property {Boolean} data.hide True if the reference element is out of boundaries, useful to know when to hide the popper
 * @property {HTMLElement} data.arrowElement Node used as arrow by arrow modifier
 * @property {Object} data.styles Any CSS property defined here will be applied to the popper. It expects the JavaScript nomenclature (eg. `marginBottom`)
 * @property {Object} data.arrowStyles Any CSS property defined here will be applied to the popper arrow. It expects the JavaScript nomenclature (eg. `marginBottom`)
 * @property {Object} data.boundaries Offsets of the popper boundaries
 * @property {Object} data.offsets The measurements of popper, reference and arrow elements
 * @property {Object} data.offsets.popper `top`, `left`, `width`, `height` values
 * @property {Object} data.offsets.reference `top`, `left`, `width`, `height` values
 * @property {Object} data.offsets.arrow] `top` and `left` offsets, only one of them will be different from 0
 */

/**
 * Default options provided to Popper.js constructor.<br />
 * These can be overridden using the `options` argument of Popper.js.<br />
 * To override an option, simply pass an object with the same
 * structure of the `options` object, as the 3rd argument. For example:
 * ```
 * new Popper(ref, pop, {
 *   modifiers: {
 *     preventOverflow: { enabled: false }
 *   }
 * })
 * ```
 * @type {Object}
 * @static
 * @memberof Popper
 */
var Defaults = {
  /**
   * Popper's placement.
   * @prop {Popper.placements} placement='bottom'
   */
  placement: 'bottom',

  /**
   * Set this to true if you want popper to position it self in 'fixed' mode
   * @prop {Boolean} positionFixed=false
   */
  positionFixed: false,

  /**
   * Whether events (resize, scroll) are initially enabled.
   * @prop {Boolean} eventsEnabled=true
   */
  eventsEnabled: true,

  /**
   * Set to true if you want to automatically remove the popper when
   * you call the `destroy` method.
   * @prop {Boolean} removeOnDestroy=false
   */
  removeOnDestroy: false,

  /**
   * Callback called when the popper is created.<br />
   * By default, it is set to no-op.<br />
   * Access Popper.js instance with `data.instance`.
   * @prop {onCreate}
   */
  onCreate: function onCreate() {},

  /**
   * Callback called when the popper is updated. This callback is not called
   * on the initialization/creation of the popper, but only on subsequent
   * updates.<br />
   * By default, it is set to no-op.<br />
   * Access Popper.js instance with `data.instance`.
   * @prop {onUpdate}
   */
  onUpdate: function onUpdate() {},

  /**
   * List of modifiers used to modify the offsets before they are applied to the popper.
   * They provide most of the functionalities of Popper.js.
   * @prop {modifiers}
   */
  modifiers: modifiers
};

/**
 * @callback onCreate
 * @param {dataObject} data
 */

/**
 * @callback onUpdate
 * @param {dataObject} data
 */

// Utils
// Methods
var Popper = function () {
  /**
   * Creates a new Popper.js instance.
   * @class Popper
   * @param {Element|referenceObject} reference - The reference element used to position the popper
   * @param {Element} popper - The HTML / XML element used as the popper
   * @param {Object} options - Your custom options to override the ones defined in [Defaults](#defaults)
   * @return {Object} instance - The generated Popper.js instance
   */
  function Popper(reference, popper) {
    var _this = this;

    var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
    classCallCheck(this, Popper);

    this.scheduleUpdate = function () {
      return requestAnimationFrame(_this.update);
    };

    // make update() debounced, so that it only runs at most once-per-tick
    this.update = debounce(this.update.bind(this));

    // with {} we create a new object with the options inside it
    this.options = _extends({}, Popper.Defaults, options);

    // init state
    this.state = {
      isDestroyed: false,
      isCreated: false,
      scrollParents: []
    };

    // get reference and popper elements (allow jQuery wrappers)
    this.reference = reference && reference.jquery ? reference[0] : reference;
    this.popper = popper && popper.jquery ? popper[0] : popper;

    // Deep merge modifiers options
    this.options.modifiers = {};
    Object.keys(_extends({}, Popper.Defaults.modifiers, options.modifiers)).forEach(function (name) {
      _this.options.modifiers[name] = _extends({}, Popper.Defaults.modifiers[name] || {}, options.modifiers ? options.modifiers[name] : {});
    });

    // Refactoring modifiers' list (Object => Array)
    this.modifiers = Object.keys(this.options.modifiers).map(function (name) {
      return _extends({
        name: name
      }, _this.options.modifiers[name]);
    })
    // sort the modifiers by order
    .sort(function (a, b) {
      return a.order - b.order;
    });

    // modifiers have the ability to execute arbitrary code when Popper.js get inited
    // such code is executed in the same order of its modifier
    // they could add new properties to their options configuration
    // BE AWARE: don't add options to `options.modifiers.name` but to `modifierOptions`!
    this.modifiers.forEach(function (modifierOptions) {
      if (modifierOptions.enabled && isFunction(modifierOptions.onLoad)) {
        modifierOptions.onLoad(_this.reference, _this.popper, _this.options, modifierOptions, _this.state);
      }
    });

    // fire the first update to position the popper in the right place
    this.update();

    var eventsEnabled = this.options.eventsEnabled;
    if (eventsEnabled) {
      // setup event listeners, they will take care of update the position in specific situations
      this.enableEventListeners();
    }

    this.state.eventsEnabled = eventsEnabled;
  }

  // We can't use class properties because they don't get listed in the
  // class prototype and break stuff like Sinon stubs


  createClass(Popper, [{
    key: 'update',
    value: function update$$1() {
      return update.call(this);
    }
  }, {
    key: 'destroy',
    value: function destroy$$1() {
      return destroy.call(this);
    }
  }, {
    key: 'enableEventListeners',
    value: function enableEventListeners$$1() {
      return enableEventListeners.call(this);
    }
  }, {
    key: 'disableEventListeners',
    value: function disableEventListeners$$1() {
      return disableEventListeners.call(this);
    }

    /**
     * Schedules an update. It will run on the next UI update available.
     * @method scheduleUpdate
     * @memberof Popper
     */


    /**
     * Collection of utilities useful when writing custom modifiers.
     * Starting from version 1.7, this method is available only if you
     * include `popper-utils.js` before `popper.js`.
     *
     * **DEPRECATION**: This way to access PopperUtils is deprecated
     * and will be removed in v2! Use the PopperUtils module directly instead.
     * Due to the high instability of the methods contained in Utils, we can't
     * guarantee them to follow semver. Use them at your own risk!
     * @static
     * @private
     * @type {Object}
     * @deprecated since version 1.8
     * @member Utils
     * @memberof Popper
     */

  }]);
  return Popper;
}();

/**
 * The `referenceObject` is an object that provides an interface compatible with Popper.js
 * and lets you use it as replacement of a real DOM node.<br />
 * You can use this method to position a popper relatively to a set of coordinates
 * in case you don't have a DOM node to use as reference.
 *
 * ```
 * new Popper(referenceObject, popperNode);
 * ```
 *
 * NB: This feature isn't supported in Internet Explorer 10.
 * @name referenceObject
 * @property {Function} data.getBoundingClientRect
 * A function that returns a set of coordinates compatible with the native `getBoundingClientRect` method.
 * @property {number} data.clientWidth
 * An ES6 getter that will return the width of the virtual reference element.
 * @property {number} data.clientHeight
 * An ES6 getter that will return the height of the virtual reference element.
 */


Popper.Utils = (typeof window !== 'undefined' ? window : global).PopperUtils;
Popper.placements = placements;
Popper.Defaults = Defaults;

return Popper;

})));
//# sourceMappingURL=popper.js.map

/*!
  * Bootstrap v4.3.1 (https://getbootstrap.com/)
  * Copyright 2011-2019 The Bootstrap Authors (https://github.com/twbs/bootstrap/graphs/contributors)
  * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
  */
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('jquery'), require('popper.js')) :
  typeof define === 'function' && define.amd ? define(['exports', 'jquery', 'popper.js'], factory) :
  (global = global || self, factory(global.bootstrap = {}, global.jQuery, global.Popper));
}(this, function (exports, $, Popper) { 'use strict';

  $ = $ && $.hasOwnProperty('default') ? $['default'] : $;
  Popper = Popper && Popper.hasOwnProperty('default') ? Popper['default'] : Popper;

  function _defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  function _createClass(Constructor, protoProps, staticProps) {
    if (protoProps) _defineProperties(Constructor.prototype, protoProps);
    if (staticProps) _defineProperties(Constructor, staticProps);
    return Constructor;
  }

  function _defineProperty(obj, key, value) {
    if (key in obj) {
      Object.defineProperty(obj, key, {
        value: value,
        enumerable: true,
        configurable: true,
        writable: true
      });
    } else {
      obj[key] = value;
    }

    return obj;
  }

  function _objectSpread(target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i] != null ? arguments[i] : {};
      var ownKeys = Object.keys(source);

      if (typeof Object.getOwnPropertySymbols === 'function') {
        ownKeys = ownKeys.concat(Object.getOwnPropertySymbols(source).filter(function (sym) {
          return Object.getOwnPropertyDescriptor(source, sym).enumerable;
        }));
      }

      ownKeys.forEach(function (key) {
        _defineProperty(target, key, source[key]);
      });
    }

    return target;
  }

  function _inheritsLoose(subClass, superClass) {
    subClass.prototype = Object.create(superClass.prototype);
    subClass.prototype.constructor = subClass;
    subClass.__proto__ = superClass;
  }

  /**
   * --------------------------------------------------------------------------
   * Bootstrap (v4.3.1): util.js
   * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
   * --------------------------------------------------------------------------
   */
  /**
   * ------------------------------------------------------------------------
   * Private TransitionEnd Helpers
   * ------------------------------------------------------------------------
   */

  var TRANSITION_END = 'transitionend';
  var MAX_UID = 1000000;
  var MILLISECONDS_MULTIPLIER = 1000; // Shoutout AngusCroll (https://goo.gl/pxwQGp)

  function toType(obj) {
    return {}.toString.call(obj).match(/\s([a-z]+)/i)[1].toLowerCase();
  }

  function getSpecialTransitionEndEvent() {
    return {
      bindType: TRANSITION_END,
      delegateType: TRANSITION_END,
      handle: function handle(event) {
        if ($(event.target).is(this)) {
          return event.handleObj.handler.apply(this, arguments); // eslint-disable-line prefer-rest-params
        }

        return undefined; // eslint-disable-line no-undefined
      }
    };
  }

  function transitionEndEmulator(duration) {
    var _this = this;

    var called = false;
    $(this).one(Util.TRANSITION_END, function () {
      called = true;
    });
    setTimeout(function () {
      if (!called) {
        Util.triggerTransitionEnd(_this);
      }
    }, duration);
    return this;
  }

  function setTransitionEndSupport() {
    $.fn.emulateTransitionEnd = transitionEndEmulator;
    $.event.special[Util.TRANSITION_END] = getSpecialTransitionEndEvent();
  }
  /**
   * --------------------------------------------------------------------------
   * Public Util Api
   * --------------------------------------------------------------------------
   */


  var Util = {
    TRANSITION_END: 'bsTransitionEnd',
    getUID: function getUID(prefix) {
      do {
        // eslint-disable-next-line no-bitwise
        prefix += ~~(Math.random() * MAX_UID); // "~~" acts like a faster Math.floor() here
      } while (document.getElementById(prefix));

      return prefix;
    },
    getSelectorFromElement: function getSelectorFromElement(element) {
      var selector = element.getAttribute('data-target');

      if (!selector || selector === '#') {
        var hrefAttr = element.getAttribute('href');
        selector = hrefAttr && hrefAttr !== '#' ? hrefAttr.trim() : '';
      }

      try {
        return document.querySelector(selector) ? selector : null;
      } catch (err) {
        return null;
      }
    },
    getTransitionDurationFromElement: function getTransitionDurationFromElement(element) {
      if (!element) {
        return 0;
      } // Get transition-duration of the element


      var transitionDuration = $(element).css('transition-duration');
      var transitionDelay = $(element).css('transition-delay');
      var floatTransitionDuration = parseFloat(transitionDuration);
      var floatTransitionDelay = parseFloat(transitionDelay); // Return 0 if element or transition duration is not found

      if (!floatTransitionDuration && !floatTransitionDelay) {
        return 0;
      } // If multiple durations are defined, take the first


      transitionDuration = transitionDuration.split(',')[0];
      transitionDelay = transitionDelay.split(',')[0];
      return (parseFloat(transitionDuration) + parseFloat(transitionDelay)) * MILLISECONDS_MULTIPLIER;
    },
    reflow: function reflow(element) {
      return element.offsetHeight;
    },
    triggerTransitionEnd: function triggerTransitionEnd(element) {
      $(element).trigger(TRANSITION_END);
    },
    // TODO: Remove in v5
    supportsTransitionEnd: function supportsTransitionEnd() {
      return Boolean(TRANSITION_END);
    },
    isElement: function isElement(obj) {
      return (obj[0] || obj).nodeType;
    },
    typeCheckConfig: function typeCheckConfig(componentName, config, configTypes) {
      for (var property in configTypes) {
        if (Object.prototype.hasOwnProperty.call(configTypes, property)) {
          var expectedTypes = configTypes[property];
          var value = config[property];
          var valueType = value && Util.isElement(value) ? 'element' : toType(value);

          if (!new RegExp(expectedTypes).test(valueType)) {
            throw new Error(componentName.toUpperCase() + ": " + ("Option \"" + property + "\" provided type \"" + valueType + "\" ") + ("but expected type \"" + expectedTypes + "\"."));
          }
        }
      }
    },
    findShadowRoot: function findShadowRoot(element) {
      if (!document.documentElement.attachShadow) {
        return null;
      } // Can find the shadow root otherwise it'll return the document


      if (typeof element.getRootNode === 'function') {
        var root = element.getRootNode();
        return root instanceof ShadowRoot ? root : null;
      }

      if (element instanceof ShadowRoot) {
        return element;
      } // when we don't find a shadow root


      if (!element.parentNode) {
        return null;
      }

      return Util.findShadowRoot(element.parentNode);
    }
  };
  setTransitionEndSupport();

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME = 'alert';
  var VERSION = '4.3.1';
  var DATA_KEY = 'bs.alert';
  var EVENT_KEY = "." + DATA_KEY;
  var DATA_API_KEY = '.data-api';
  var JQUERY_NO_CONFLICT = $.fn[NAME];
  var Selector = {
    DISMISS: '[data-dismiss="alert"]'
  };
  var Event = {
    CLOSE: "close" + EVENT_KEY,
    CLOSED: "closed" + EVENT_KEY,
    CLICK_DATA_API: "click" + EVENT_KEY + DATA_API_KEY
  };
  var ClassName = {
    ALERT: 'alert',
    FADE: 'fade',
    SHOW: 'show'
    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

  };

  var Alert =
  /*#__PURE__*/
  function () {
    function Alert(element) {
      this._element = element;
    } // Getters


    var _proto = Alert.prototype;

    // Public
    _proto.close = function close(element) {
      var rootElement = this._element;

      if (element) {
        rootElement = this._getRootElement(element);
      }

      var customEvent = this._triggerCloseEvent(rootElement);

      if (customEvent.isDefaultPrevented()) {
        return;
      }

      this._removeElement(rootElement);
    };

    _proto.dispose = function dispose() {
      $.removeData(this._element, DATA_KEY);
      this._element = null;
    } // Private
    ;

    _proto._getRootElement = function _getRootElement(element) {
      var selector = Util.getSelectorFromElement(element);
      var parent = false;

      if (selector) {
        parent = document.querySelector(selector);
      }

      if (!parent) {
        parent = $(element).closest("." + ClassName.ALERT)[0];
      }

      return parent;
    };

    _proto._triggerCloseEvent = function _triggerCloseEvent(element) {
      var closeEvent = $.Event(Event.CLOSE);
      $(element).trigger(closeEvent);
      return closeEvent;
    };

    _proto._removeElement = function _removeElement(element) {
      var _this = this;

      $(element).removeClass(ClassName.SHOW);

      if (!$(element).hasClass(ClassName.FADE)) {
        this._destroyElement(element);

        return;
      }

      var transitionDuration = Util.getTransitionDurationFromElement(element);
      $(element).one(Util.TRANSITION_END, function (event) {
        return _this._destroyElement(element, event);
      }).emulateTransitionEnd(transitionDuration);
    };

    _proto._destroyElement = function _destroyElement(element) {
      $(element).detach().trigger(Event.CLOSED).remove();
    } // Static
    ;

    Alert._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var $element = $(this);
        var data = $element.data(DATA_KEY);

        if (!data) {
          data = new Alert(this);
          $element.data(DATA_KEY, data);
        }

        if (config === 'close') {
          data[config](this);
        }
      });
    };

    Alert._handleDismiss = function _handleDismiss(alertInstance) {
      return function (event) {
        if (event) {
          event.preventDefault();
        }

        alertInstance.close(this);
      };
    };

    _createClass(Alert, null, [{
      key: "VERSION",
      get: function get() {
        return VERSION;
      }
    }]);

    return Alert;
  }();
  /**
   * ------------------------------------------------------------------------
   * Data Api implementation
   * ------------------------------------------------------------------------
   */


  $(document).on(Event.CLICK_DATA_API, Selector.DISMISS, Alert._handleDismiss(new Alert()));
  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */

  $.fn[NAME] = Alert._jQueryInterface;
  $.fn[NAME].Constructor = Alert;

  $.fn[NAME].noConflict = function () {
    $.fn[NAME] = JQUERY_NO_CONFLICT;
    return Alert._jQueryInterface;
  };

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME$1 = 'button';
  var VERSION$1 = '4.3.1';
  var DATA_KEY$1 = 'bs.button';
  var EVENT_KEY$1 = "." + DATA_KEY$1;
  var DATA_API_KEY$1 = '.data-api';
  var JQUERY_NO_CONFLICT$1 = $.fn[NAME$1];
  var ClassName$1 = {
    ACTIVE: 'active',
    BUTTON: 'btn',
    FOCUS: 'focus'
  };
  var Selector$1 = {
    DATA_TOGGLE_CARROT: '[data-toggle^="button"]',
    DATA_TOGGLE: '[data-toggle="buttons"]',
    INPUT: 'input:not([type="hidden"])',
    ACTIVE: '.active',
    BUTTON: '.btn'
  };
  var Event$1 = {
    CLICK_DATA_API: "click" + EVENT_KEY$1 + DATA_API_KEY$1,
    FOCUS_BLUR_DATA_API: "focus" + EVENT_KEY$1 + DATA_API_KEY$1 + " " + ("blur" + EVENT_KEY$1 + DATA_API_KEY$1)
    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

  };

  var Button =
  /*#__PURE__*/
  function () {
    function Button(element) {
      this._element = element;
    } // Getters


    var _proto = Button.prototype;

    // Public
    _proto.toggle = function toggle() {
      var triggerChangeEvent = true;
      var addAriaPressed = true;
      var rootElement = $(this._element).closest(Selector$1.DATA_TOGGLE)[0];

      if (rootElement) {
        var input = this._element.querySelector(Selector$1.INPUT);

        if (input) {
          if (input.type === 'radio') {
            if (input.checked && this._element.classList.contains(ClassName$1.ACTIVE)) {
              triggerChangeEvent = false;
            } else {
              var activeElement = rootElement.querySelector(Selector$1.ACTIVE);

              if (activeElement) {
                $(activeElement).removeClass(ClassName$1.ACTIVE);
              }
            }
          }

          if (triggerChangeEvent) {
            if (input.hasAttribute('disabled') || rootElement.hasAttribute('disabled') || input.classList.contains('disabled') || rootElement.classList.contains('disabled')) {
              return;
            }

            input.checked = !this._element.classList.contains(ClassName$1.ACTIVE);
            $(input).trigger('change');
          }

          input.focus();
          addAriaPressed = false;
        }
      }

      if (addAriaPressed) {
        this._element.setAttribute('aria-pressed', !this._element.classList.contains(ClassName$1.ACTIVE));
      }

      if (triggerChangeEvent) {
        $(this._element).toggleClass(ClassName$1.ACTIVE);
      }
    };

    _proto.dispose = function dispose() {
      $.removeData(this._element, DATA_KEY$1);
      this._element = null;
    } // Static
    ;

    Button._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$1);

        if (!data) {
          data = new Button(this);
          $(this).data(DATA_KEY$1, data);
        }

        if (config === 'toggle') {
          data[config]();
        }
      });
    };

    _createClass(Button, null, [{
      key: "VERSION",
      get: function get() {
        return VERSION$1;
      }
    }]);

    return Button;
  }();
  /**
   * ------------------------------------------------------------------------
   * Data Api implementation
   * ------------------------------------------------------------------------
   */


  $(document).on(Event$1.CLICK_DATA_API, Selector$1.DATA_TOGGLE_CARROT, function (event) {
    event.preventDefault();
    var button = event.target;

    if (!$(button).hasClass(ClassName$1.BUTTON)) {
      button = $(button).closest(Selector$1.BUTTON);
    }

    Button._jQueryInterface.call($(button), 'toggle');
  }).on(Event$1.FOCUS_BLUR_DATA_API, Selector$1.DATA_TOGGLE_CARROT, function (event) {
    var button = $(event.target).closest(Selector$1.BUTTON)[0];
    $(button).toggleClass(ClassName$1.FOCUS, /^focus(in)?$/.test(event.type));
  });
  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */

  $.fn[NAME$1] = Button._jQueryInterface;
  $.fn[NAME$1].Constructor = Button;

  $.fn[NAME$1].noConflict = function () {
    $.fn[NAME$1] = JQUERY_NO_CONFLICT$1;
    return Button._jQueryInterface;
  };

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME$2 = 'carousel';
  var VERSION$2 = '4.3.1';
  var DATA_KEY$2 = 'bs.carousel';
  var EVENT_KEY$2 = "." + DATA_KEY$2;
  var DATA_API_KEY$2 = '.data-api';
  var JQUERY_NO_CONFLICT$2 = $.fn[NAME$2];
  var ARROW_LEFT_KEYCODE = 37; // KeyboardEvent.which value for left arrow key

  var ARROW_RIGHT_KEYCODE = 39; // KeyboardEvent.which value for right arrow key

  var TOUCHEVENT_COMPAT_WAIT = 500; // Time for mouse compat events to fire after touch

  var SWIPE_THRESHOLD = 40;
  var Default = {
    interval: 5000,
    keyboard: true,
    slide: false,
    pause: 'hover',
    wrap: true,
    touch: true
  };
  var DefaultType = {
    interval: '(number|boolean)',
    keyboard: 'boolean',
    slide: '(boolean|string)',
    pause: '(string|boolean)',
    wrap: 'boolean',
    touch: 'boolean'
  };
  var Direction = {
    NEXT: 'next',
    PREV: 'prev',
    LEFT: 'left',
    RIGHT: 'right'
  };
  var Event$2 = {
    SLIDE: "slide" + EVENT_KEY$2,
    SLID: "slid" + EVENT_KEY$2,
    KEYDOWN: "keydown" + EVENT_KEY$2,
    MOUSEENTER: "mouseenter" + EVENT_KEY$2,
    MOUSELEAVE: "mouseleave" + EVENT_KEY$2,
    TOUCHSTART: "touchstart" + EVENT_KEY$2,
    TOUCHMOVE: "touchmove" + EVENT_KEY$2,
    TOUCHEND: "touchend" + EVENT_KEY$2,
    POINTERDOWN: "pointerdown" + EVENT_KEY$2,
    POINTERUP: "pointerup" + EVENT_KEY$2,
    DRAG_START: "dragstart" + EVENT_KEY$2,
    LOAD_DATA_API: "load" + EVENT_KEY$2 + DATA_API_KEY$2,
    CLICK_DATA_API: "click" + EVENT_KEY$2 + DATA_API_KEY$2
  };
  var ClassName$2 = {
    CAROUSEL: 'carousel',
    ACTIVE: 'active',
    SLIDE: 'slide',
    RIGHT: 'carousel-item-right',
    LEFT: 'carousel-item-left',
    NEXT: 'carousel-item-next',
    PREV: 'carousel-item-prev',
    ITEM: 'carousel-item',
    POINTER_EVENT: 'pointer-event'
  };
  var Selector$2 = {
    ACTIVE: '.active',
    ACTIVE_ITEM: '.active.carousel-item',
    ITEM: '.carousel-item',
    ITEM_IMG: '.carousel-item img',
    NEXT_PREV: '.carousel-item-next, .carousel-item-prev',
    INDICATORS: '.carousel-indicators',
    DATA_SLIDE: '[data-slide], [data-slide-to]',
    DATA_RIDE: '[data-ride="carousel"]'
  };
  var PointerType = {
    TOUCH: 'touch',
    PEN: 'pen'
    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

  };

  var Carousel =
  /*#__PURE__*/
  function () {
    function Carousel(element, config) {
      this._items = null;
      this._interval = null;
      this._activeElement = null;
      this._isPaused = false;
      this._isSliding = false;
      this.touchTimeout = null;
      this.touchStartX = 0;
      this.touchDeltaX = 0;
      this._config = this._getConfig(config);
      this._element = element;
      this._indicatorsElement = this._element.querySelector(Selector$2.INDICATORS);
      this._touchSupported = 'ontouchstart' in document.documentElement || navigator.maxTouchPoints > 0;
      this._pointerEvent = Boolean(window.PointerEvent || window.MSPointerEvent);

      this._addEventListeners();
    } // Getters


    var _proto = Carousel.prototype;

    // Public
    _proto.next = function next() {
      if (!this._isSliding) {
        this._slide(Direction.NEXT);
      }
    };

    _proto.nextWhenVisible = function nextWhenVisible() {
      // Don't call next when the page isn't visible
      // or the carousel or its parent isn't visible
      if (!document.hidden && $(this._element).is(':visible') && $(this._element).css('visibility') !== 'hidden') {
        this.next();
      }
    };

    _proto.prev = function prev() {
      if (!this._isSliding) {
        this._slide(Direction.PREV);
      }
    };

    _proto.pause = function pause(event) {
      if (!event) {
        this._isPaused = true;
      }

      if (this._element.querySelector(Selector$2.NEXT_PREV)) {
        Util.triggerTransitionEnd(this._element);
        this.cycle(true);
      }

      clearInterval(this._interval);
      this._interval = null;
    };

    _proto.cycle = function cycle(event) {
      if (!event) {
        this._isPaused = false;
      }

      if (this._interval) {
        clearInterval(this._interval);
        this._interval = null;
      }

      if (this._config.interval && !this._isPaused) {
        this._interval = setInterval((document.visibilityState ? this.nextWhenVisible : this.next).bind(this), this._config.interval);
      }
    };

    _proto.to = function to(index) {
      var _this = this;

      this._activeElement = this._element.querySelector(Selector$2.ACTIVE_ITEM);

      var activeIndex = this._getItemIndex(this._activeElement);

      if (index > this._items.length - 1 || index < 0) {
        return;
      }

      if (this._isSliding) {
        $(this._element).one(Event$2.SLID, function () {
          return _this.to(index);
        });
        return;
      }

      if (activeIndex === index) {
        this.pause();
        this.cycle();
        return;
      }

      var direction = index > activeIndex ? Direction.NEXT : Direction.PREV;

      this._slide(direction, this._items[index]);
    };

    _proto.dispose = function dispose() {
      $(this._element).off(EVENT_KEY$2);
      $.removeData(this._element, DATA_KEY$2);
      this._items = null;
      this._config = null;
      this._element = null;
      this._interval = null;
      this._isPaused = null;
      this._isSliding = null;
      this._activeElement = null;
      this._indicatorsElement = null;
    } // Private
    ;

    _proto._getConfig = function _getConfig(config) {
      config = _objectSpread({}, Default, config);
      Util.typeCheckConfig(NAME$2, config, DefaultType);
      return config;
    };

    _proto._handleSwipe = function _handleSwipe() {
      var absDeltax = Math.abs(this.touchDeltaX);

      if (absDeltax <= SWIPE_THRESHOLD) {
        return;
      }

      var direction = absDeltax / this.touchDeltaX; // swipe left

      if (direction > 0) {
        this.prev();
      } // swipe right


      if (direction < 0) {
        this.next();
      }
    };

    _proto._addEventListeners = function _addEventListeners() {
      var _this2 = this;

      if (this._config.keyboard) {
        $(this._element).on(Event$2.KEYDOWN, function (event) {
          return _this2._keydown(event);
        });
      }

      if (this._config.pause === 'hover') {
        $(this._element).on(Event$2.MOUSEENTER, function (event) {
          return _this2.pause(event);
        }).on(Event$2.MOUSELEAVE, function (event) {
          return _this2.cycle(event);
        });
      }

      if (this._config.touch) {
        this._addTouchEventListeners();
      }
    };

    _proto._addTouchEventListeners = function _addTouchEventListeners() {
      var _this3 = this;

      if (!this._touchSupported) {
        return;
      }

      var start = function start(event) {
        if (_this3._pointerEvent && PointerType[event.originalEvent.pointerType.toUpperCase()]) {
          _this3.touchStartX = event.originalEvent.clientX;
        } else if (!_this3._pointerEvent) {
          _this3.touchStartX = event.originalEvent.touches[0].clientX;
        }
      };

      var move = function move(event) {
        // ensure swiping with one touch and not pinching
        if (event.originalEvent.touches && event.originalEvent.touches.length > 1) {
          _this3.touchDeltaX = 0;
        } else {
          _this3.touchDeltaX = event.originalEvent.touches[0].clientX - _this3.touchStartX;
        }
      };

      var end = function end(event) {
        if (_this3._pointerEvent && PointerType[event.originalEvent.pointerType.toUpperCase()]) {
          _this3.touchDeltaX = event.originalEvent.clientX - _this3.touchStartX;
        }

        _this3._handleSwipe();

        if (_this3._config.pause === 'hover') {
          // If it's a touch-enabled device, mouseenter/leave are fired as
          // part of the mouse compatibility events on first tap - the carousel
          // would stop cycling until user tapped out of it;
          // here, we listen for touchend, explicitly pause the carousel
          // (as if it's the second time we tap on it, mouseenter compat event
          // is NOT fired) and after a timeout (to allow for mouse compatibility
          // events to fire) we explicitly restart cycling
          _this3.pause();

          if (_this3.touchTimeout) {
            clearTimeout(_this3.touchTimeout);
          }

          _this3.touchTimeout = setTimeout(function (event) {
            return _this3.cycle(event);
          }, TOUCHEVENT_COMPAT_WAIT + _this3._config.interval);
        }
      };

      $(this._element.querySelectorAll(Selector$2.ITEM_IMG)).on(Event$2.DRAG_START, function (e) {
        return e.preventDefault();
      });

      if (this._pointerEvent) {
        $(this._element).on(Event$2.POINTERDOWN, function (event) {
          return start(event);
        });
        $(this._element).on(Event$2.POINTERUP, function (event) {
          return end(event);
        });

        this._element.classList.add(ClassName$2.POINTER_EVENT);
      } else {
        $(this._element).on(Event$2.TOUCHSTART, function (event) {
          return start(event);
        });
        $(this._element).on(Event$2.TOUCHMOVE, function (event) {
          return move(event);
        });
        $(this._element).on(Event$2.TOUCHEND, function (event) {
          return end(event);
        });
      }
    };

    _proto._keydown = function _keydown(event) {
      if (/input|textarea/i.test(event.target.tagName)) {
        return;
      }

      switch (event.which) {
        case ARROW_LEFT_KEYCODE:
          event.preventDefault();
          this.prev();
          break;

        case ARROW_RIGHT_KEYCODE:
          event.preventDefault();
          this.next();
          break;

        default:
      }
    };

    _proto._getItemIndex = function _getItemIndex(element) {
      this._items = element && element.parentNode ? [].slice.call(element.parentNode.querySelectorAll(Selector$2.ITEM)) : [];
      return this._items.indexOf(element);
    };

    _proto._getItemByDirection = function _getItemByDirection(direction, activeElement) {
      var isNextDirection = direction === Direction.NEXT;
      var isPrevDirection = direction === Direction.PREV;

      var activeIndex = this._getItemIndex(activeElement);

      var lastItemIndex = this._items.length - 1;
      var isGoingToWrap = isPrevDirection && activeIndex === 0 || isNextDirection && activeIndex === lastItemIndex;

      if (isGoingToWrap && !this._config.wrap) {
        return activeElement;
      }

      var delta = direction === Direction.PREV ? -1 : 1;
      var itemIndex = (activeIndex + delta) % this._items.length;
      return itemIndex === -1 ? this._items[this._items.length - 1] : this._items[itemIndex];
    };

    _proto._triggerSlideEvent = function _triggerSlideEvent(relatedTarget, eventDirectionName) {
      var targetIndex = this._getItemIndex(relatedTarget);

      var fromIndex = this._getItemIndex(this._element.querySelector(Selector$2.ACTIVE_ITEM));

      var slideEvent = $.Event(Event$2.SLIDE, {
        relatedTarget: relatedTarget,
        direction: eventDirectionName,
        from: fromIndex,
        to: targetIndex
      });
      $(this._element).trigger(slideEvent);
      return slideEvent;
    };

    _proto._setActiveIndicatorElement = function _setActiveIndicatorElement(element) {
      if (this._indicatorsElement) {
        var indicators = [].slice.call(this._indicatorsElement.querySelectorAll(Selector$2.ACTIVE));
        $(indicators).removeClass(ClassName$2.ACTIVE);

        var nextIndicator = this._indicatorsElement.children[this._getItemIndex(element)];

        if (nextIndicator) {
          $(nextIndicator).addClass(ClassName$2.ACTIVE);
        }
      }
    };

    _proto._slide = function _slide(direction, element) {
      var _this4 = this;

      var activeElement = this._element.querySelector(Selector$2.ACTIVE_ITEM);

      var activeElementIndex = this._getItemIndex(activeElement);

      var nextElement = element || activeElement && this._getItemByDirection(direction, activeElement);

      var nextElementIndex = this._getItemIndex(nextElement);

      var isCycling = Boolean(this._interval);
      var directionalClassName;
      var orderClassName;
      var eventDirectionName;

      if (direction === Direction.NEXT) {
        directionalClassName = ClassName$2.LEFT;
        orderClassName = ClassName$2.NEXT;
        eventDirectionName = Direction.LEFT;
      } else {
        directionalClassName = ClassName$2.RIGHT;
        orderClassName = ClassName$2.PREV;
        eventDirectionName = Direction.RIGHT;
      }

      if (nextElement && $(nextElement).hasClass(ClassName$2.ACTIVE)) {
        this._isSliding = false;
        return;
      }

      var slideEvent = this._triggerSlideEvent(nextElement, eventDirectionName);

      if (slideEvent.isDefaultPrevented()) {
        return;
      }

      if (!activeElement || !nextElement) {
        // Some weirdness is happening, so we bail
        return;
      }

      this._isSliding = true;

      if (isCycling) {
        this.pause();
      }

      this._setActiveIndicatorElement(nextElement);

      var slidEvent = $.Event(Event$2.SLID, {
        relatedTarget: nextElement,
        direction: eventDirectionName,
        from: activeElementIndex,
        to: nextElementIndex
      });

      if ($(this._element).hasClass(ClassName$2.SLIDE)) {
        $(nextElement).addClass(orderClassName);
        Util.reflow(nextElement);
        $(activeElement).addClass(directionalClassName);
        $(nextElement).addClass(directionalClassName);
        var nextElementInterval = parseInt(nextElement.getAttribute('data-interval'), 10);

        if (nextElementInterval) {
          this._config.defaultInterval = this._config.defaultInterval || this._config.interval;
          this._config.interval = nextElementInterval;
        } else {
          this._config.interval = this._config.defaultInterval || this._config.interval;
        }

        var transitionDuration = Util.getTransitionDurationFromElement(activeElement);
        $(activeElement).one(Util.TRANSITION_END, function () {
          $(nextElement).removeClass(directionalClassName + " " + orderClassName).addClass(ClassName$2.ACTIVE);
          $(activeElement).removeClass(ClassName$2.ACTIVE + " " + orderClassName + " " + directionalClassName);
          _this4._isSliding = false;
          setTimeout(function () {
            return $(_this4._element).trigger(slidEvent);
          }, 0);
        }).emulateTransitionEnd(transitionDuration);
      } else {
        $(activeElement).removeClass(ClassName$2.ACTIVE);
        $(nextElement).addClass(ClassName$2.ACTIVE);
        this._isSliding = false;
        $(this._element).trigger(slidEvent);
      }

      if (isCycling) {
        this.cycle();
      }
    } // Static
    ;

    Carousel._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$2);

        var _config = _objectSpread({}, Default, $(this).data());

        if (typeof config === 'object') {
          _config = _objectSpread({}, _config, config);
        }

        var action = typeof config === 'string' ? config : _config.slide;

        if (!data) {
          data = new Carousel(this, _config);
          $(this).data(DATA_KEY$2, data);
        }

        if (typeof config === 'number') {
          data.to(config);
        } else if (typeof action === 'string') {
          if (typeof data[action] === 'undefined') {
            throw new TypeError("No method named \"" + action + "\"");
          }

          data[action]();
        } else if (_config.interval && _config.ride) {
          data.pause();
          data.cycle();
        }
      });
    };

    Carousel._dataApiClickHandler = function _dataApiClickHandler(event) {
      var selector = Util.getSelectorFromElement(this);

      if (!selector) {
        return;
      }

      var target = $(selector)[0];

      if (!target || !$(target).hasClass(ClassName$2.CAROUSEL)) {
        return;
      }

      var config = _objectSpread({}, $(target).data(), $(this).data());

      var slideIndex = this.getAttribute('data-slide-to');

      if (slideIndex) {
        config.interval = false;
      }

      Carousel._jQueryInterface.call($(target), config);

      if (slideIndex) {
        $(target).data(DATA_KEY$2).to(slideIndex);
      }

      event.preventDefault();
    };

    _createClass(Carousel, null, [{
      key: "VERSION",
      get: function get() {
        return VERSION$2;
      }
    }, {
      key: "Default",
      get: function get() {
        return Default;
      }
    }]);

    return Carousel;
  }();
  /**
   * ------------------------------------------------------------------------
   * Data Api implementation
   * ------------------------------------------------------------------------
   */


  $(document).on(Event$2.CLICK_DATA_API, Selector$2.DATA_SLIDE, Carousel._dataApiClickHandler);
  $(window).on(Event$2.LOAD_DATA_API, function () {
    var carousels = [].slice.call(document.querySelectorAll(Selector$2.DATA_RIDE));

    for (var i = 0, len = carousels.length; i < len; i++) {
      var $carousel = $(carousels[i]);

      Carousel._jQueryInterface.call($carousel, $carousel.data());
    }
  });
  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */

  $.fn[NAME$2] = Carousel._jQueryInterface;
  $.fn[NAME$2].Constructor = Carousel;

  $.fn[NAME$2].noConflict = function () {
    $.fn[NAME$2] = JQUERY_NO_CONFLICT$2;
    return Carousel._jQueryInterface;
  };

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME$3 = 'collapse';
  var VERSION$3 = '4.3.1';
  var DATA_KEY$3 = 'bs.collapse';
  var EVENT_KEY$3 = "." + DATA_KEY$3;
  var DATA_API_KEY$3 = '.data-api';
  var JQUERY_NO_CONFLICT$3 = $.fn[NAME$3];
  var Default$1 = {
    toggle: true,
    parent: ''
  };
  var DefaultType$1 = {
    toggle: 'boolean',
    parent: '(string|element)'
  };
  var Event$3 = {
    SHOW: "show" + EVENT_KEY$3,
    SHOWN: "shown" + EVENT_KEY$3,
    HIDE: "hide" + EVENT_KEY$3,
    HIDDEN: "hidden" + EVENT_KEY$3,
    CLICK_DATA_API: "click" + EVENT_KEY$3 + DATA_API_KEY$3
  };
  var ClassName$3 = {
    SHOW: 'show',
    COLLAPSE: 'collapse',
    COLLAPSING: 'collapsing',
    COLLAPSED: 'collapsed'
  };
  var Dimension = {
    WIDTH: 'width',
    HEIGHT: 'height'
  };
  var Selector$3 = {
    ACTIVES: '.show, .collapsing',
    DATA_TOGGLE: '[data-toggle="collapse"]'
    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

  };

  var Collapse =
  /*#__PURE__*/
  function () {
    function Collapse(element, config) {
      this._isTransitioning = false;
      this._element = element;
      this._config = this._getConfig(config);
      this._triggerArray = [].slice.call(document.querySelectorAll("[data-toggle=\"collapse\"][href=\"#" + element.id + "\"]," + ("[data-toggle=\"collapse\"][data-target=\"#" + element.id + "\"]")));
      var toggleList = [].slice.call(document.querySelectorAll(Selector$3.DATA_TOGGLE));

      for (var i = 0, len = toggleList.length; i < len; i++) {
        var elem = toggleList[i];
        var selector = Util.getSelectorFromElement(elem);
        var filterElement = [].slice.call(document.querySelectorAll(selector)).filter(function (foundElem) {
          return foundElem === element;
        });

        if (selector !== null && filterElement.length > 0) {
          this._selector = selector;

          this._triggerArray.push(elem);
        }
      }

      this._parent = this._config.parent ? this._getParent() : null;

      if (!this._config.parent) {
        this._addAriaAndCollapsedClass(this._element, this._triggerArray);
      }

      if (this._config.toggle) {
        this.toggle();
      }
    } // Getters


    var _proto = Collapse.prototype;

    // Public
    _proto.toggle = function toggle() {
      if ($(this._element).hasClass(ClassName$3.SHOW)) {
        this.hide();
      } else {
        this.show();
      }
    };

    _proto.show = function show() {
      var _this = this;

      if (this._isTransitioning || $(this._element).hasClass(ClassName$3.SHOW)) {
        return;
      }

      var actives;
      var activesData;

      if (this._parent) {
        actives = [].slice.call(this._parent.querySelectorAll(Selector$3.ACTIVES)).filter(function (elem) {
          if (typeof _this._config.parent === 'string') {
            return elem.getAttribute('data-parent') === _this._config.parent;
          }

          return elem.classList.contains(ClassName$3.COLLAPSE);
        });

        if (actives.length === 0) {
          actives = null;
        }
      }

      if (actives) {
        activesData = $(actives).not(this._selector).data(DATA_KEY$3);

        if (activesData && activesData._isTransitioning) {
          return;
        }
      }

      var startEvent = $.Event(Event$3.SHOW);
      $(this._element).trigger(startEvent);

      if (startEvent.isDefaultPrevented()) {
        return;
      }

      if (actives) {
        Collapse._jQueryInterface.call($(actives).not(this._selector), 'hide');

        if (!activesData) {
          $(actives).data(DATA_KEY$3, null);
        }
      }

      var dimension = this._getDimension();

      $(this._element).removeClass(ClassName$3.COLLAPSE).addClass(ClassName$3.COLLAPSING);
      this._element.style[dimension] = 0;

      if (this._triggerArray.length) {
        $(this._triggerArray).removeClass(ClassName$3.COLLAPSED).attr('aria-expanded', true);
      }

      this.setTransitioning(true);

      var complete = function complete() {
        $(_this._element).removeClass(ClassName$3.COLLAPSING).addClass(ClassName$3.COLLAPSE).addClass(ClassName$3.SHOW);
        _this._element.style[dimension] = '';

        _this.setTransitioning(false);

        $(_this._element).trigger(Event$3.SHOWN);
      };

      var capitalizedDimension = dimension[0].toUpperCase() + dimension.slice(1);
      var scrollSize = "scroll" + capitalizedDimension;
      var transitionDuration = Util.getTransitionDurationFromElement(this._element);
      $(this._element).one(Util.TRANSITION_END, complete).emulateTransitionEnd(transitionDuration);
      this._element.style[dimension] = this._element[scrollSize] + "px";
    };

    _proto.hide = function hide() {
      var _this2 = this;

      if (this._isTransitioning || !$(this._element).hasClass(ClassName$3.SHOW)) {
        return;
      }

      var startEvent = $.Event(Event$3.HIDE);
      $(this._element).trigger(startEvent);

      if (startEvent.isDefaultPrevented()) {
        return;
      }

      var dimension = this._getDimension();

      this._element.style[dimension] = this._element.getBoundingClientRect()[dimension] + "px";
      Util.reflow(this._element);
      $(this._element).addClass(ClassName$3.COLLAPSING).removeClass(ClassName$3.COLLAPSE).removeClass(ClassName$3.SHOW);
      var triggerArrayLength = this._triggerArray.length;

      if (triggerArrayLength > 0) {
        for (var i = 0; i < triggerArrayLength; i++) {
          var trigger = this._triggerArray[i];
          var selector = Util.getSelectorFromElement(trigger);

          if (selector !== null) {
            var $elem = $([].slice.call(document.querySelectorAll(selector)));

            if (!$elem.hasClass(ClassName$3.SHOW)) {
              $(trigger).addClass(ClassName$3.COLLAPSED).attr('aria-expanded', false);
            }
          }
        }
      }

      this.setTransitioning(true);

      var complete = function complete() {
        _this2.setTransitioning(false);

        $(_this2._element).removeClass(ClassName$3.COLLAPSING).addClass(ClassName$3.COLLAPSE).trigger(Event$3.HIDDEN);
      };

      this._element.style[dimension] = '';
      var transitionDuration = Util.getTransitionDurationFromElement(this._element);
      $(this._element).one(Util.TRANSITION_END, complete).emulateTransitionEnd(transitionDuration);
    };

    _proto.setTransitioning = function setTransitioning(isTransitioning) {
      this._isTransitioning = isTransitioning;
    };

    _proto.dispose = function dispose() {
      $.removeData(this._element, DATA_KEY$3);
      this._config = null;
      this._parent = null;
      this._element = null;
      this._triggerArray = null;
      this._isTransitioning = null;
    } // Private
    ;

    _proto._getConfig = function _getConfig(config) {
      config = _objectSpread({}, Default$1, config);
      config.toggle = Boolean(config.toggle); // Coerce string values

      Util.typeCheckConfig(NAME$3, config, DefaultType$1);
      return config;
    };

    _proto._getDimension = function _getDimension() {
      var hasWidth = $(this._element).hasClass(Dimension.WIDTH);
      return hasWidth ? Dimension.WIDTH : Dimension.HEIGHT;
    };

    _proto._getParent = function _getParent() {
      var _this3 = this;

      var parent;

      if (Util.isElement(this._config.parent)) {
        parent = this._config.parent; // It's a jQuery object

        if (typeof this._config.parent.jquery !== 'undefined') {
          parent = this._config.parent[0];
        }
      } else {
        parent = document.querySelector(this._config.parent);
      }

      var selector = "[data-toggle=\"collapse\"][data-parent=\"" + this._config.parent + "\"]";
      var children = [].slice.call(parent.querySelectorAll(selector));
      $(children).each(function (i, element) {
        _this3._addAriaAndCollapsedClass(Collapse._getTargetFromElement(element), [element]);
      });
      return parent;
    };

    _proto._addAriaAndCollapsedClass = function _addAriaAndCollapsedClass(element, triggerArray) {
      var isOpen = $(element).hasClass(ClassName$3.SHOW);

      if (triggerArray.length) {
        $(triggerArray).toggleClass(ClassName$3.COLLAPSED, !isOpen).attr('aria-expanded', isOpen);
      }
    } // Static
    ;

    Collapse._getTargetFromElement = function _getTargetFromElement(element) {
      var selector = Util.getSelectorFromElement(element);
      return selector ? document.querySelector(selector) : null;
    };

    Collapse._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var $this = $(this);
        var data = $this.data(DATA_KEY$3);

        var _config = _objectSpread({}, Default$1, $this.data(), typeof config === 'object' && config ? config : {});

        if (!data && _config.toggle && /show|hide/.test(config)) {
          _config.toggle = false;
        }

        if (!data) {
          data = new Collapse(this, _config);
          $this.data(DATA_KEY$3, data);
        }

        if (typeof config === 'string') {
          if (typeof data[config] === 'undefined') {
            throw new TypeError("No method named \"" + config + "\"");
          }

          data[config]();
        }
      });
    };

    _createClass(Collapse, null, [{
      key: "VERSION",
      get: function get() {
        return VERSION$3;
      }
    }, {
      key: "Default",
      get: function get() {
        return Default$1;
      }
    }]);

    return Collapse;
  }();
  /**
   * ------------------------------------------------------------------------
   * Data Api implementation
   * ------------------------------------------------------------------------
   */


  $(document).on(Event$3.CLICK_DATA_API, Selector$3.DATA_TOGGLE, function (event) {
    // preventDefault only for <a> elements (which change the URL) not inside the collapsible element
    if (event.currentTarget.tagName === 'A') {
      event.preventDefault();
    }

    var $trigger = $(this);
    var selector = Util.getSelectorFromElement(this);
    var selectors = [].slice.call(document.querySelectorAll(selector));
    $(selectors).each(function () {
      var $target = $(this);
      var data = $target.data(DATA_KEY$3);
      var config = data ? 'toggle' : $trigger.data();

      Collapse._jQueryInterface.call($target, config);
    });
  });
  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */

  $.fn[NAME$3] = Collapse._jQueryInterface;
  $.fn[NAME$3].Constructor = Collapse;

  $.fn[NAME$3].noConflict = function () {
    $.fn[NAME$3] = JQUERY_NO_CONFLICT$3;
    return Collapse._jQueryInterface;
  };

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME$4 = 'dropdown';
  var VERSION$4 = '4.3.1';
  var DATA_KEY$4 = 'bs.dropdown';
  var EVENT_KEY$4 = "." + DATA_KEY$4;
  var DATA_API_KEY$4 = '.data-api';
  var JQUERY_NO_CONFLICT$4 = $.fn[NAME$4];
  var ESCAPE_KEYCODE = 27; // KeyboardEvent.which value for Escape (Esc) key

  var SPACE_KEYCODE = 32; // KeyboardEvent.which value for space key

  var TAB_KEYCODE = 9; // KeyboardEvent.which value for tab key

  var ARROW_UP_KEYCODE = 38; // KeyboardEvent.which value for up arrow key

  var ARROW_DOWN_KEYCODE = 40; // KeyboardEvent.which value for down arrow key

  var RIGHT_MOUSE_BUTTON_WHICH = 3; // MouseEvent.which value for the right button (assuming a right-handed mouse)

  var REGEXP_KEYDOWN = new RegExp(ARROW_UP_KEYCODE + "|" + ARROW_DOWN_KEYCODE + "|" + ESCAPE_KEYCODE);
  var Event$4 = {
    HIDE: "hide" + EVENT_KEY$4,
    HIDDEN: "hidden" + EVENT_KEY$4,
    SHOW: "show" + EVENT_KEY$4,
    SHOWN: "shown" + EVENT_KEY$4,
    CLICK: "click" + EVENT_KEY$4,
    CLICK_DATA_API: "click" + EVENT_KEY$4 + DATA_API_KEY$4,
    KEYDOWN_DATA_API: "keydown" + EVENT_KEY$4 + DATA_API_KEY$4,
    KEYUP_DATA_API: "keyup" + EVENT_KEY$4 + DATA_API_KEY$4
  };
  var ClassName$4 = {
    DISABLED: 'disabled',
    SHOW: 'show',
    DROPUP: 'dropup',
    DROPRIGHT: 'dropright',
    DROPLEFT: 'dropleft',
    MENURIGHT: 'dropdown-menu-right',
    MENULEFT: 'dropdown-menu-left',
    POSITION_STATIC: 'position-static'
  };
  var Selector$4 = {
    DATA_TOGGLE: '[data-toggle="dropdown"]',
    FORM_CHILD: '.dropdown form',
    MENU: '.dropdown-menu',
    NAVBAR_NAV: '.navbar-nav',
    VISIBLE_ITEMS: '.dropdown-menu .dropdown-item:not(.disabled):not(:disabled)'
  };
  var AttachmentMap = {
    TOP: 'top-start',
    TOPEND: 'top-end',
    BOTTOM: 'bottom-start',
    BOTTOMEND: 'bottom-end',
    RIGHT: 'right-start',
    RIGHTEND: 'right-end',
    LEFT: 'left-start',
    LEFTEND: 'left-end'
  };
  var Default$2 = {
    offset: 0,
    flip: true,
    boundary: 'scrollParent',
    reference: 'toggle',
    display: 'dynamic'
  };
  var DefaultType$2 = {
    offset: '(number|string|function)',
    flip: 'boolean',
    boundary: '(string|element)',
    reference: '(string|element)',
    display: 'string'
    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

  };

  var Dropdown =
  /*#__PURE__*/
  function () {
    function Dropdown(element, config) {
      this._element = element;
      this._popper = null;
      this._config = this._getConfig(config);
      this._menu = this._getMenuElement();
      this._inNavbar = this._detectNavbar();

      this._addEventListeners();
    } // Getters


    var _proto = Dropdown.prototype;

    // Public
    _proto.toggle = function toggle() {
      if (this._element.disabled || $(this._element).hasClass(ClassName$4.DISABLED)) {
        return;
      }

      var parent = Dropdown._getParentFromElement(this._element);

      var isActive = $(this._menu).hasClass(ClassName$4.SHOW);

      Dropdown._clearMenus();

      if (isActive) {
        return;
      }

      var relatedTarget = {
        relatedTarget: this._element
      };
      var showEvent = $.Event(Event$4.SHOW, relatedTarget);
      $(parent).trigger(showEvent);

      if (showEvent.isDefaultPrevented()) {
        return;
      } // Disable totally Popper.js for Dropdown in Navbar


      if (!this._inNavbar) {
        /**
         * Check for Popper dependency
         * Popper - https://popper.js.org
         */
        if (typeof Popper === 'undefined') {
          throw new TypeError('Bootstrap\'s dropdowns require Popper.js (https://popper.js.org/)');
        }

        var referenceElement = this._element;

        if (this._config.reference === 'parent') {
          referenceElement = parent;
        } else if (Util.isElement(this._config.reference)) {
          referenceElement = this._config.reference; // Check if it's jQuery element

          if (typeof this._config.reference.jquery !== 'undefined') {
            referenceElement = this._config.reference[0];
          }
        } // If boundary is not `scrollParent`, then set position to `static`
        // to allow the menu to "escape" the scroll parent's boundaries
        // https://github.com/twbs/bootstrap/issues/24251


        if (this._config.boundary !== 'scrollParent') {
          $(parent).addClass(ClassName$4.POSITION_STATIC);
        }

        this._popper = new Popper(referenceElement, this._menu, this._getPopperConfig());
      } // If this is a touch-enabled device we add extra
      // empty mouseover listeners to the body's immediate children;
      // only needed because of broken event delegation on iOS
      // https://www.quirksmode.org/blog/archives/2014/02/mouse_event_bub.html


      if ('ontouchstart' in document.documentElement && $(parent).closest(Selector$4.NAVBAR_NAV).length === 0) {
        $(document.body).children().on('mouseover', null, $.noop);
      }

      this._element.focus();

      this._element.setAttribute('aria-expanded', true);

      $(this._menu).toggleClass(ClassName$4.SHOW);
      $(parent).toggleClass(ClassName$4.SHOW).trigger($.Event(Event$4.SHOWN, relatedTarget));
    };

    _proto.show = function show() {
      if (this._element.disabled || $(this._element).hasClass(ClassName$4.DISABLED) || $(this._menu).hasClass(ClassName$4.SHOW)) {
        return;
      }

      var relatedTarget = {
        relatedTarget: this._element
      };
      var showEvent = $.Event(Event$4.SHOW, relatedTarget);

      var parent = Dropdown._getParentFromElement(this._element);

      $(parent).trigger(showEvent);

      if (showEvent.isDefaultPrevented()) {
        return;
      }

      $(this._menu).toggleClass(ClassName$4.SHOW);
      $(parent).toggleClass(ClassName$4.SHOW).trigger($.Event(Event$4.SHOWN, relatedTarget));
    };

    _proto.hide = function hide() {
      if (this._element.disabled || $(this._element).hasClass(ClassName$4.DISABLED) || !$(this._menu).hasClass(ClassName$4.SHOW)) {
        return;
      }

      var relatedTarget = {
        relatedTarget: this._element
      };
      var hideEvent = $.Event(Event$4.HIDE, relatedTarget);

      var parent = Dropdown._getParentFromElement(this._element);

      $(parent).trigger(hideEvent);

      if (hideEvent.isDefaultPrevented()) {
        return;
      }

      $(this._menu).toggleClass(ClassName$4.SHOW);
      $(parent).toggleClass(ClassName$4.SHOW).trigger($.Event(Event$4.HIDDEN, relatedTarget));
    };

    _proto.dispose = function dispose() {
      $.removeData(this._element, DATA_KEY$4);
      $(this._element).off(EVENT_KEY$4);
      this._element = null;
      this._menu = null;

      if (this._popper !== null) {
        this._popper.destroy();

        this._popper = null;
      }
    };

    _proto.update = function update() {
      this._inNavbar = this._detectNavbar();

      if (this._popper !== null) {
        this._popper.scheduleUpdate();
      }
    } // Private
    ;

    _proto._addEventListeners = function _addEventListeners() {
      var _this = this;

      $(this._element).on(Event$4.CLICK, function (event) {
        event.preventDefault();
        event.stopPropagation();

        _this.toggle();
      });
    };

    _proto._getConfig = function _getConfig(config) {
      config = _objectSpread({}, this.constructor.Default, $(this._element).data(), config);
      Util.typeCheckConfig(NAME$4, config, this.constructor.DefaultType);
      return config;
    };

    _proto._getMenuElement = function _getMenuElement() {
      if (!this._menu) {
        var parent = Dropdown._getParentFromElement(this._element);

        if (parent) {
          this._menu = parent.querySelector(Selector$4.MENU);
        }
      }

      return this._menu;
    };

    _proto._getPlacement = function _getPlacement() {
      var $parentDropdown = $(this._element.parentNode);
      var placement = AttachmentMap.BOTTOM; // Handle dropup

      if ($parentDropdown.hasClass(ClassName$4.DROPUP)) {
        placement = AttachmentMap.TOP;

        if ($(this._menu).hasClass(ClassName$4.MENURIGHT)) {
          placement = AttachmentMap.TOPEND;
        }
      } else if ($parentDropdown.hasClass(ClassName$4.DROPRIGHT)) {
        placement = AttachmentMap.RIGHT;
      } else if ($parentDropdown.hasClass(ClassName$4.DROPLEFT)) {
        placement = AttachmentMap.LEFT;
      } else if ($(this._menu).hasClass(ClassName$4.MENURIGHT)) {
        placement = AttachmentMap.BOTTOMEND;
      }

      return placement;
    };

    _proto._detectNavbar = function _detectNavbar() {
      return $(this._element).closest('.navbar').length > 0;
    };

    _proto._getOffset = function _getOffset() {
      var _this2 = this;

      var offset = {};

      if (typeof this._config.offset === 'function') {
        offset.fn = function (data) {
          data.offsets = _objectSpread({}, data.offsets, _this2._config.offset(data.offsets, _this2._element) || {});
          return data;
        };
      } else {
        offset.offset = this._config.offset;
      }

      return offset;
    };

    _proto._getPopperConfig = function _getPopperConfig() {
      var popperConfig = {
        placement: this._getPlacement(),
        modifiers: {
          offset: this._getOffset(),
          flip: {
            enabled: this._config.flip
          },
          preventOverflow: {
            boundariesElement: this._config.boundary
          }
        } // Disable Popper.js if we have a static display

      };

      if (this._config.display === 'static') {
        popperConfig.modifiers.applyStyle = {
          enabled: false
        };
      }

      return popperConfig;
    } // Static
    ;

    Dropdown._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$4);

        var _config = typeof config === 'object' ? config : null;

        if (!data) {
          data = new Dropdown(this, _config);
          $(this).data(DATA_KEY$4, data);
        }

        if (typeof config === 'string') {
          if (typeof data[config] === 'undefined') {
            throw new TypeError("No method named \"" + config + "\"");
          }

          data[config]();
        }
      });
    };

    Dropdown._clearMenus = function _clearMenus(event) {
      if (event && (event.which === RIGHT_MOUSE_BUTTON_WHICH || event.type === 'keyup' && event.which !== TAB_KEYCODE)) {
        return;
      }

      var toggles = [].slice.call(document.querySelectorAll(Selector$4.DATA_TOGGLE));

      for (var i = 0, len = toggles.length; i < len; i++) {
        var parent = Dropdown._getParentFromElement(toggles[i]);

        var context = $(toggles[i]).data(DATA_KEY$4);
        var relatedTarget = {
          relatedTarget: toggles[i]
        };

        if (event && event.type === 'click') {
          relatedTarget.clickEvent = event;
        }

        if (!context) {
          continue;
        }

        var dropdownMenu = context._menu;

        if (!$(parent).hasClass(ClassName$4.SHOW)) {
          continue;
        }

        if (event && (event.type === 'click' && /input|textarea/i.test(event.target.tagName) || event.type === 'keyup' && event.which === TAB_KEYCODE) && $.contains(parent, event.target)) {
          continue;
        }

        var hideEvent = $.Event(Event$4.HIDE, relatedTarget);
        $(parent).trigger(hideEvent);

        if (hideEvent.isDefaultPrevented()) {
          continue;
        } // If this is a touch-enabled device we remove the extra
        // empty mouseover listeners we added for iOS support


        if ('ontouchstart' in document.documentElement) {
          $(document.body).children().off('mouseover', null, $.noop);
        }

        toggles[i].setAttribute('aria-expanded', 'false');
        $(dropdownMenu).removeClass(ClassName$4.SHOW);
        $(parent).removeClass(ClassName$4.SHOW).trigger($.Event(Event$4.HIDDEN, relatedTarget));
      }
    };

    Dropdown._getParentFromElement = function _getParentFromElement(element) {
      var parent;
      var selector = Util.getSelectorFromElement(element);

      if (selector) {
        parent = document.querySelector(selector);
      }

      return parent || element.parentNode;
    } // eslint-disable-next-line complexity
    ;

    Dropdown._dataApiKeydownHandler = function _dataApiKeydownHandler(event) {
      // If not input/textarea:
      //  - And not a key in REGEXP_KEYDOWN => not a dropdown command
      // If input/textarea:
      //  - If space key => not a dropdown command
      //  - If key is other than escape
      //    - If key is not up or down => not a dropdown command
      //    - If trigger inside the menu => not a dropdown command
      if (/input|textarea/i.test(event.target.tagName) ? event.which === SPACE_KEYCODE || event.which !== ESCAPE_KEYCODE && (event.which !== ARROW_DOWN_KEYCODE && event.which !== ARROW_UP_KEYCODE || $(event.target).closest(Selector$4.MENU).length) : !REGEXP_KEYDOWN.test(event.which)) {
        return;
      }

      event.preventDefault();
      event.stopPropagation();

      if (this.disabled || $(this).hasClass(ClassName$4.DISABLED)) {
        return;
      }

      var parent = Dropdown._getParentFromElement(this);

      var isActive = $(parent).hasClass(ClassName$4.SHOW);

      if (!isActive || isActive && (event.which === ESCAPE_KEYCODE || event.which === SPACE_KEYCODE)) {
        if (event.which === ESCAPE_KEYCODE) {
          var toggle = parent.querySelector(Selector$4.DATA_TOGGLE);
          $(toggle).trigger('focus');
        }

        $(this).trigger('click');
        return;
      }

      var items = [].slice.call(parent.querySelectorAll(Selector$4.VISIBLE_ITEMS));

      if (items.length === 0) {
        return;
      }

      var index = items.indexOf(event.target);

      if (event.which === ARROW_UP_KEYCODE && index > 0) {
        // Up
        index--;
      }

      if (event.which === ARROW_DOWN_KEYCODE && index < items.length - 1) {
        // Down
        index++;
      }

      if (index < 0) {
        index = 0;
      }

      items[index].focus();
    };

    _createClass(Dropdown, null, [{
      key: "VERSION",
      get: function get() {
        return VERSION$4;
      }
    }, {
      key: "Default",
      get: function get() {
        return Default$2;
      }
    }, {
      key: "DefaultType",
      get: function get() {
        return DefaultType$2;
      }
    }]);

    return Dropdown;
  }();
  /**
   * ------------------------------------------------------------------------
   * Data Api implementation
   * ------------------------------------------------------------------------
   */


  $(document).on(Event$4.KEYDOWN_DATA_API, Selector$4.DATA_TOGGLE, Dropdown._dataApiKeydownHandler).on(Event$4.KEYDOWN_DATA_API, Selector$4.MENU, Dropdown._dataApiKeydownHandler).on(Event$4.CLICK_DATA_API + " " + Event$4.KEYUP_DATA_API, Dropdown._clearMenus).on(Event$4.CLICK_DATA_API, Selector$4.DATA_TOGGLE, function (event) {
    event.preventDefault();
    event.stopPropagation();

    Dropdown._jQueryInterface.call($(this), 'toggle');
  }).on(Event$4.CLICK_DATA_API, Selector$4.FORM_CHILD, function (e) {
    e.stopPropagation();
  });
  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */

  $.fn[NAME$4] = Dropdown._jQueryInterface;
  $.fn[NAME$4].Constructor = Dropdown;

  $.fn[NAME$4].noConflict = function () {
    $.fn[NAME$4] = JQUERY_NO_CONFLICT$4;
    return Dropdown._jQueryInterface;
  };

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME$5 = 'modal';
  var VERSION$5 = '4.3.1';
  var DATA_KEY$5 = 'bs.modal';
  var EVENT_KEY$5 = "." + DATA_KEY$5;
  var DATA_API_KEY$5 = '.data-api';
  var JQUERY_NO_CONFLICT$5 = $.fn[NAME$5];
  var ESCAPE_KEYCODE$1 = 27; // KeyboardEvent.which value for Escape (Esc) key

  var Default$3 = {
    backdrop: true,
    keyboard: true,
    focus: true,
    show: true
  };
  var DefaultType$3 = {
    backdrop: '(boolean|string)',
    keyboard: 'boolean',
    focus: 'boolean',
    show: 'boolean'
  };
  var Event$5 = {
    HIDE: "hide" + EVENT_KEY$5,
    HIDDEN: "hidden" + EVENT_KEY$5,
    SHOW: "show" + EVENT_KEY$5,
    SHOWN: "shown" + EVENT_KEY$5,
    FOCUSIN: "focusin" + EVENT_KEY$5,
    RESIZE: "resize" + EVENT_KEY$5,
    CLICK_DISMISS: "click.dismiss" + EVENT_KEY$5,
    KEYDOWN_DISMISS: "keydown.dismiss" + EVENT_KEY$5,
    MOUSEUP_DISMISS: "mouseup.dismiss" + EVENT_KEY$5,
    MOUSEDOWN_DISMISS: "mousedown.dismiss" + EVENT_KEY$5,
    CLICK_DATA_API: "click" + EVENT_KEY$5 + DATA_API_KEY$5
  };
  var ClassName$5 = {
    SCROLLABLE: 'modal-dialog-scrollable',
    SCROLLBAR_MEASURER: 'modal-scrollbar-measure',
    BACKDROP: 'modal-backdrop',
    OPEN: 'modal-open',
    FADE: 'fade',
    SHOW: 'show'
  };
  var Selector$5 = {
    DIALOG: '.modal-dialog',
    MODAL_BODY: '.modal-body',
    DATA_TOGGLE: '[data-toggle="modal"]',
    DATA_DISMISS: '[data-dismiss="modal"]',
    FIXED_CONTENT: '.fixed-top, .fixed-bottom, .is-fixed, .sticky-top',
    STICKY_CONTENT: '.sticky-top'
    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

  };

  var Modal =
  /*#__PURE__*/
  function () {
    function Modal(element, config) {
      this._config = this._getConfig(config);
      this._element = element;
      this._dialog = element.querySelector(Selector$5.DIALOG);
      this._backdrop = null;
      this._isShown = false;
      this._isBodyOverflowing = false;
      this._ignoreBackdropClick = false;
      this._isTransitioning = false;
      this._scrollbarWidth = 0;
    } // Getters


    var _proto = Modal.prototype;

    // Public
    _proto.toggle = function toggle(relatedTarget) {
      return this._isShown ? this.hide() : this.show(relatedTarget);
    };

    _proto.show = function show(relatedTarget) {
      var _this = this;

      if (this._isShown || this._isTransitioning) {
        return;
      }

      if ($(this._element).hasClass(ClassName$5.FADE)) {
        this._isTransitioning = true;
      }

      var showEvent = $.Event(Event$5.SHOW, {
        relatedTarget: relatedTarget
      });
      $(this._element).trigger(showEvent);

      if (this._isShown || showEvent.isDefaultPrevented()) {
        return;
      }

      this._isShown = true;

      this._checkScrollbar();

      this._setScrollbar();

      this._adjustDialog();

      this._setEscapeEvent();

      this._setResizeEvent();

      $(this._element).on(Event$5.CLICK_DISMISS, Selector$5.DATA_DISMISS, function (event) {
        return _this.hide(event);
      });
      $(this._dialog).on(Event$5.MOUSEDOWN_DISMISS, function () {
        $(_this._element).one(Event$5.MOUSEUP_DISMISS, function (event) {
          if ($(event.target).is(_this._element)) {
            _this._ignoreBackdropClick = true;
          }
        });
      });

      this._showBackdrop(function () {
        return _this._showElement(relatedTarget);
      });
    };

    _proto.hide = function hide(event) {
      var _this2 = this;

      if (event) {
        event.preventDefault();
      }

      if (!this._isShown || this._isTransitioning) {
        return;
      }

      var hideEvent = $.Event(Event$5.HIDE);
      $(this._element).trigger(hideEvent);

      if (!this._isShown || hideEvent.isDefaultPrevented()) {
        return;
      }

      this._isShown = false;
      var transition = $(this._element).hasClass(ClassName$5.FADE);

      if (transition) {
        this._isTransitioning = true;
      }

      this._setEscapeEvent();

      this._setResizeEvent();

      $(document).off(Event$5.FOCUSIN);
      $(this._element).removeClass(ClassName$5.SHOW);
      $(this._element).off(Event$5.CLICK_DISMISS);
      $(this._dialog).off(Event$5.MOUSEDOWN_DISMISS);

      if (transition) {
        var transitionDuration = Util.getTransitionDurationFromElement(this._element);
        $(this._element).one(Util.TRANSITION_END, function (event) {
          return _this2._hideModal(event);
        }).emulateTransitionEnd(transitionDuration);
      } else {
        this._hideModal();
      }
    };

    _proto.dispose = function dispose() {
      [window, this._element, this._dialog].forEach(function (htmlElement) {
        return $(htmlElement).off(EVENT_KEY$5);
      });
      /**
       * `document` has 2 events `Event.FOCUSIN` and `Event.CLICK_DATA_API`
       * Do not move `document` in `htmlElements` array
       * It will remove `Event.CLICK_DATA_API` event that should remain
       */

      $(document).off(Event$5.FOCUSIN);
      $.removeData(this._element, DATA_KEY$5);
      this._config = null;
      this._element = null;
      this._dialog = null;
      this._backdrop = null;
      this._isShown = null;
      this._isBodyOverflowing = null;
      this._ignoreBackdropClick = null;
      this._isTransitioning = null;
      this._scrollbarWidth = null;
    };

    _proto.handleUpdate = function handleUpdate() {
      this._adjustDialog();
    } // Private
    ;

    _proto._getConfig = function _getConfig(config) {
      config = _objectSpread({}, Default$3, config);
      Util.typeCheckConfig(NAME$5, config, DefaultType$3);
      return config;
    };

    _proto._showElement = function _showElement(relatedTarget) {
      var _this3 = this;

      var transition = $(this._element).hasClass(ClassName$5.FADE);

      if (!this._element.parentNode || this._element.parentNode.nodeType !== Node.ELEMENT_NODE) {
        // Don't move modal's DOM position
        document.body.appendChild(this._element);
      }

      this._element.style.display = 'block';

      this._element.removeAttribute('aria-hidden');

      this._element.setAttribute('aria-modal', true);

      if ($(this._dialog).hasClass(ClassName$5.SCROLLABLE)) {
        this._dialog.querySelector(Selector$5.MODAL_BODY).scrollTop = 0;
      } else {
        this._element.scrollTop = 0;
      }

      if (transition) {
        Util.reflow(this._element);
      }

      $(this._element).addClass(ClassName$5.SHOW);

      if (this._config.focus) {
        this._enforceFocus();
      }

      var shownEvent = $.Event(Event$5.SHOWN, {
        relatedTarget: relatedTarget
      });

      var transitionComplete = function transitionComplete() {
        if (_this3._config.focus) {
          _this3._element.focus();
        }

        _this3._isTransitioning = false;
        $(_this3._element).trigger(shownEvent);
      };

      if (transition) {
        var transitionDuration = Util.getTransitionDurationFromElement(this._dialog);
        $(this._dialog).one(Util.TRANSITION_END, transitionComplete).emulateTransitionEnd(transitionDuration);
      } else {
        transitionComplete();
      }
    };

    _proto._enforceFocus = function _enforceFocus() {
      var _this4 = this;

      $(document).off(Event$5.FOCUSIN) // Guard against infinite focus loop
      .on(Event$5.FOCUSIN, function (event) {
        if (document !== event.target && _this4._element !== event.target && $(_this4._element).has(event.target).length === 0) {
          _this4._element.focus();
        }
      });
    };

    _proto._setEscapeEvent = function _setEscapeEvent() {
      var _this5 = this;

      if (this._isShown && this._config.keyboard) {
        $(this._element).on(Event$5.KEYDOWN_DISMISS, function (event) {
          if (event.which === ESCAPE_KEYCODE$1) {
            event.preventDefault();

            _this5.hide();
          }
        });
      } else if (!this._isShown) {
        $(this._element).off(Event$5.KEYDOWN_DISMISS);
      }
    };

    _proto._setResizeEvent = function _setResizeEvent() {
      var _this6 = this;

      if (this._isShown) {
        $(window).on(Event$5.RESIZE, function (event) {
          return _this6.handleUpdate(event);
        });
      } else {
        $(window).off(Event$5.RESIZE);
      }
    };

    _proto._hideModal = function _hideModal() {
      var _this7 = this;

      this._element.style.display = 'none';

      this._element.setAttribute('aria-hidden', true);

      this._element.removeAttribute('aria-modal');

      this._isTransitioning = false;

      this._showBackdrop(function () {
        $(document.body).removeClass(ClassName$5.OPEN);

        _this7._resetAdjustments();

        _this7._resetScrollbar();

        $(_this7._element).trigger(Event$5.HIDDEN);
      });
    };

    _proto._removeBackdrop = function _removeBackdrop() {
      if (this._backdrop) {
        $(this._backdrop).remove();
        this._backdrop = null;
      }
    };

    _proto._showBackdrop = function _showBackdrop(callback) {
      var _this8 = this;

      var animate = $(this._element).hasClass(ClassName$5.FADE) ? ClassName$5.FADE : '';

      if (this._isShown && this._config.backdrop) {
        this._backdrop = document.createElement('div');
        this._backdrop.className = ClassName$5.BACKDROP;

        if (animate) {
          this._backdrop.classList.add(animate);
        }

        $(this._backdrop).appendTo(document.body);
        $(this._element).on(Event$5.CLICK_DISMISS, function (event) {
          if (_this8._ignoreBackdropClick) {
            _this8._ignoreBackdropClick = false;
            return;
          }

          if (event.target !== event.currentTarget) {
            return;
          }

          if (_this8._config.backdrop === 'static') {
            _this8._element.focus();
          } else {
            _this8.hide();
          }
        });

        if (animate) {
          Util.reflow(this._backdrop);
        }

        $(this._backdrop).addClass(ClassName$5.SHOW);

        if (!callback) {
          return;
        }

        if (!animate) {
          callback();
          return;
        }

        var backdropTransitionDuration = Util.getTransitionDurationFromElement(this._backdrop);
        $(this._backdrop).one(Util.TRANSITION_END, callback).emulateTransitionEnd(backdropTransitionDuration);
      } else if (!this._isShown && this._backdrop) {
        $(this._backdrop).removeClass(ClassName$5.SHOW);

        var callbackRemove = function callbackRemove() {
          _this8._removeBackdrop();

          if (callback) {
            callback();
          }
        };

        if ($(this._element).hasClass(ClassName$5.FADE)) {
          var _backdropTransitionDuration = Util.getTransitionDurationFromElement(this._backdrop);

          $(this._backdrop).one(Util.TRANSITION_END, callbackRemove).emulateTransitionEnd(_backdropTransitionDuration);
        } else {
          callbackRemove();
        }
      } else if (callback) {
        callback();
      }
    } // ----------------------------------------------------------------------
    // the following methods are used to handle overflowing modals
    // todo (fat): these should probably be refactored out of modal.js
    // ----------------------------------------------------------------------
    ;

    _proto._adjustDialog = function _adjustDialog() {
      var isModalOverflowing = this._element.scrollHeight > document.documentElement.clientHeight;

      if (!this._isBodyOverflowing && isModalOverflowing) {
        this._element.style.paddingLeft = this._scrollbarWidth + "px";
      }

      if (this._isBodyOverflowing && !isModalOverflowing) {
        this._element.style.paddingRight = this._scrollbarWidth + "px";
      }
    };

    _proto._resetAdjustments = function _resetAdjustments() {
      this._element.style.paddingLeft = '';
      this._element.style.paddingRight = '';
    };

    _proto._checkScrollbar = function _checkScrollbar() {
      var rect = document.body.getBoundingClientRect();
      this._isBodyOverflowing = rect.left + rect.right < window.innerWidth;
      this._scrollbarWidth = this._getScrollbarWidth();
    };

    _proto._setScrollbar = function _setScrollbar() {
      var _this9 = this;

      if (this._isBodyOverflowing) {
        // Note: DOMNode.style.paddingRight returns the actual value or '' if not set
        //   while $(DOMNode).css('padding-right') returns the calculated value or 0 if not set
        var fixedContent = [].slice.call(document.querySelectorAll(Selector$5.FIXED_CONTENT));
        var stickyContent = [].slice.call(document.querySelectorAll(Selector$5.STICKY_CONTENT)); // Adjust fixed content padding

        $(fixedContent).each(function (index, element) {
          var actualPadding = element.style.paddingRight;
          var calculatedPadding = $(element).css('padding-right');
          $(element).data('padding-right', actualPadding).css('padding-right', parseFloat(calculatedPadding) + _this9._scrollbarWidth + "px");
        }); // Adjust sticky content margin

        $(stickyContent).each(function (index, element) {
          var actualMargin = element.style.marginRight;
          var calculatedMargin = $(element).css('margin-right');
          $(element).data('margin-right', actualMargin).css('margin-right', parseFloat(calculatedMargin) - _this9._scrollbarWidth + "px");
        }); // Adjust body padding

        var actualPadding = document.body.style.paddingRight;
        var calculatedPadding = $(document.body).css('padding-right');
        $(document.body).data('padding-right', actualPadding).css('padding-right', parseFloat(calculatedPadding) + this._scrollbarWidth + "px");
      }

      $(document.body).addClass(ClassName$5.OPEN);
    };

    _proto._resetScrollbar = function _resetScrollbar() {
      // Restore fixed content padding
      var fixedContent = [].slice.call(document.querySelectorAll(Selector$5.FIXED_CONTENT));
      $(fixedContent).each(function (index, element) {
        var padding = $(element).data('padding-right');
        $(element).removeData('padding-right');
        element.style.paddingRight = padding ? padding : '';
      }); // Restore sticky content

      var elements = [].slice.call(document.querySelectorAll("" + Selector$5.STICKY_CONTENT));
      $(elements).each(function (index, element) {
        var margin = $(element).data('margin-right');

        if (typeof margin !== 'undefined') {
          $(element).css('margin-right', margin).removeData('margin-right');
        }
      }); // Restore body padding

      var padding = $(document.body).data('padding-right');
      $(document.body).removeData('padding-right');
      document.body.style.paddingRight = padding ? padding : '';
    };

    _proto._getScrollbarWidth = function _getScrollbarWidth() {
      // thx d.walsh
      var scrollDiv = document.createElement('div');
      scrollDiv.className = ClassName$5.SCROLLBAR_MEASURER;
      document.body.appendChild(scrollDiv);
      var scrollbarWidth = scrollDiv.getBoundingClientRect().width - scrollDiv.clientWidth;
      document.body.removeChild(scrollDiv);
      return scrollbarWidth;
    } // Static
    ;

    Modal._jQueryInterface = function _jQueryInterface(config, relatedTarget) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$5);

        var _config = _objectSpread({}, Default$3, $(this).data(), typeof config === 'object' && config ? config : {});

        if (!data) {
          data = new Modal(this, _config);
          $(this).data(DATA_KEY$5, data);
        }

        if (typeof config === 'string') {
          if (typeof data[config] === 'undefined') {
            throw new TypeError("No method named \"" + config + "\"");
          }

          data[config](relatedTarget);
        } else if (_config.show) {
          data.show(relatedTarget);
        }
      });
    };

    _createClass(Modal, null, [{
      key: "VERSION",
      get: function get() {
        return VERSION$5;
      }
    }, {
      key: "Default",
      get: function get() {
        return Default$3;
      }
    }]);

    return Modal;
  }();
  /**
   * ------------------------------------------------------------------------
   * Data Api implementation
   * ------------------------------------------------------------------------
   */


  $(document).on(Event$5.CLICK_DATA_API, Selector$5.DATA_TOGGLE, function (event) {
    var _this10 = this;

    var target;
    var selector = Util.getSelectorFromElement(this);

    if (selector) {
      target = document.querySelector(selector);
    }

    var config = $(target).data(DATA_KEY$5) ? 'toggle' : _objectSpread({}, $(target).data(), $(this).data());

    if (this.tagName === 'A' || this.tagName === 'AREA') {
      event.preventDefault();
    }

    var $target = $(target).one(Event$5.SHOW, function (showEvent) {
      if (showEvent.isDefaultPrevented()) {
        // Only register focus restorer if modal will actually get shown
        return;
      }

      $target.one(Event$5.HIDDEN, function () {
        if ($(_this10).is(':visible')) {
          _this10.focus();
        }
      });
    });

    Modal._jQueryInterface.call($(target), config, this);
  });
  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */

  $.fn[NAME$5] = Modal._jQueryInterface;
  $.fn[NAME$5].Constructor = Modal;

  $.fn[NAME$5].noConflict = function () {
    $.fn[NAME$5] = JQUERY_NO_CONFLICT$5;
    return Modal._jQueryInterface;
  };

  /**
   * --------------------------------------------------------------------------
   * Bootstrap (v4.3.1): tools/sanitizer.js
   * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
   * --------------------------------------------------------------------------
   */
  var uriAttrs = ['background', 'cite', 'href', 'itemtype', 'longdesc', 'poster', 'src', 'xlink:href'];
  var ARIA_ATTRIBUTE_PATTERN = /^aria-[\w-]*$/i;
  var DefaultWhitelist = {
    // Global attributes allowed on any supplied element below.
    '*': ['class', 'dir', 'id', 'lang', 'role', ARIA_ATTRIBUTE_PATTERN],
    a: ['target', 'href', 'title', 'rel'],
    area: [],
    b: [],
    br: [],
    col: [],
    code: [],
    div: [],
    em: [],
    hr: [],
    h1: [],
    h2: [],
    h3: [],
    h4: [],
    h5: [],
    h6: [],
    i: [],
    img: ['src', 'alt', 'title', 'width', 'height'],
    li: [],
    ol: [],
    p: [],
    pre: [],
    s: [],
    small: [],
    span: [],
    sub: [],
    sup: [],
    strong: [],
    u: [],
    ul: []
    /**
     * A pattern that recognizes a commonly useful subset of URLs that are safe.
     *
     * Shoutout to Angular 7 https://github.com/angular/angular/blob/7.2.4/packages/core/src/sanitization/url_sanitizer.ts
     */

  };
  var SAFE_URL_PATTERN = /^(?:(?:https?|mailto|ftp|tel|file):|[^&:/?#]*(?:[/?#]|$))/gi;
  /**
   * A pattern that matches safe data URLs. Only matches image, video and audio types.
   *
   * Shoutout to Angular 7 https://github.com/angular/angular/blob/7.2.4/packages/core/src/sanitization/url_sanitizer.ts
   */

  var DATA_URL_PATTERN = /^data:(?:image\/(?:bmp|gif|jpeg|jpg|png|tiff|webp)|video\/(?:mpeg|mp4|ogg|webm)|audio\/(?:mp3|oga|ogg|opus));base64,[a-z0-9+/]+=*$/i;

  function allowedAttribute(attr, allowedAttributeList) {
    var attrName = attr.nodeName.toLowerCase();

    if (allowedAttributeList.indexOf(attrName) !== -1) {
      if (uriAttrs.indexOf(attrName) !== -1) {
        return Boolean(attr.nodeValue.match(SAFE_URL_PATTERN) || attr.nodeValue.match(DATA_URL_PATTERN));
      }

      return true;
    }

    var regExp = allowedAttributeList.filter(function (attrRegex) {
      return attrRegex instanceof RegExp;
    }); // Check if a regular expression validates the attribute.

    for (var i = 0, l = regExp.length; i < l; i++) {
      if (attrName.match(regExp[i])) {
        return true;
      }
    }

    return false;
  }

  function sanitizeHtml(unsafeHtml, whiteList, sanitizeFn) {
    if (unsafeHtml.length === 0) {
      return unsafeHtml;
    }

    if (sanitizeFn && typeof sanitizeFn === 'function') {
      return sanitizeFn(unsafeHtml);
    }

    var domParser = new window.DOMParser();
    var createdDocument = domParser.parseFromString(unsafeHtml, 'text/html');
    var whitelistKeys = Object.keys(whiteList);
    var elements = [].slice.call(createdDocument.body.querySelectorAll('*'));

    var _loop = function _loop(i, len) {
      var el = elements[i];
      var elName = el.nodeName.toLowerCase();

      if (whitelistKeys.indexOf(el.nodeName.toLowerCase()) === -1) {
        el.parentNode.removeChild(el);
        return "continue";
      }

      var attributeList = [].slice.call(el.attributes);
      var whitelistedAttributes = [].concat(whiteList['*'] || [], whiteList[elName] || []);
      attributeList.forEach(function (attr) {
        if (!allowedAttribute(attr, whitelistedAttributes)) {
          el.removeAttribute(attr.nodeName);
        }
      });
    };

    for (var i = 0, len = elements.length; i < len; i++) {
      var _ret = _loop(i, len);

      if (_ret === "continue") continue;
    }

    return createdDocument.body.innerHTML;
  }

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME$6 = 'tooltip';
  var VERSION$6 = '4.3.1';
  var DATA_KEY$6 = 'bs.tooltip';
  var EVENT_KEY$6 = "." + DATA_KEY$6;
  var JQUERY_NO_CONFLICT$6 = $.fn[NAME$6];
  var CLASS_PREFIX = 'bs-tooltip';
  var BSCLS_PREFIX_REGEX = new RegExp("(^|\\s)" + CLASS_PREFIX + "\\S+", 'g');
  var DISALLOWED_ATTRIBUTES = ['sanitize', 'whiteList', 'sanitizeFn'];
  var DefaultType$4 = {
    animation: 'boolean',
    template: 'string',
    title: '(string|element|function)',
    trigger: 'string',
    delay: '(number|object)',
    html: 'boolean',
    selector: '(string|boolean)',
    placement: '(string|function)',
    offset: '(number|string|function)',
    container: '(string|element|boolean)',
    fallbackPlacement: '(string|array)',
    boundary: '(string|element)',
    sanitize: 'boolean',
    sanitizeFn: '(null|function)',
    whiteList: 'object'
  };
  var AttachmentMap$1 = {
    AUTO: 'auto',
    TOP: 'top',
    RIGHT: 'right',
    BOTTOM: 'bottom',
    LEFT: 'left'
  };
  var Default$4 = {
    animation: true,
    template: '<div class="tooltip" role="tooltip">' + '<div class="arrow"></div>' + '<div class="tooltip-inner"></div></div>',
    trigger: 'hover focus',
    title: '',
    delay: 0,
    html: false,
    selector: false,
    placement: 'top',
    offset: 0,
    container: false,
    fallbackPlacement: 'flip',
    boundary: 'scrollParent',
    sanitize: true,
    sanitizeFn: null,
    whiteList: DefaultWhitelist
  };
  var HoverState = {
    SHOW: 'show',
    OUT: 'out'
  };
  var Event$6 = {
    HIDE: "hide" + EVENT_KEY$6,
    HIDDEN: "hidden" + EVENT_KEY$6,
    SHOW: "show" + EVENT_KEY$6,
    SHOWN: "shown" + EVENT_KEY$6,
    INSERTED: "inserted" + EVENT_KEY$6,
    CLICK: "click" + EVENT_KEY$6,
    FOCUSIN: "focusin" + EVENT_KEY$6,
    FOCUSOUT: "focusout" + EVENT_KEY$6,
    MOUSEENTER: "mouseenter" + EVENT_KEY$6,
    MOUSELEAVE: "mouseleave" + EVENT_KEY$6
  };
  var ClassName$6 = {
    FADE: 'fade',
    SHOW: 'show'
  };
  var Selector$6 = {
    TOOLTIP: '.tooltip',
    TOOLTIP_INNER: '.tooltip-inner',
    ARROW: '.arrow'
  };
  var Trigger = {
    HOVER: 'hover',
    FOCUS: 'focus',
    CLICK: 'click',
    MANUAL: 'manual'
    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

  };

  var Tooltip =
  /*#__PURE__*/
  function () {
    function Tooltip(element, config) {
      /**
       * Check for Popper dependency
       * Popper - https://popper.js.org
       */
      if (typeof Popper === 'undefined') {
        throw new TypeError('Bootstrap\'s tooltips require Popper.js (https://popper.js.org/)');
      } // private


      this._isEnabled = true;
      this._timeout = 0;
      this._hoverState = '';
      this._activeTrigger = {};
      this._popper = null; // Protected

      this.element = element;
      this.config = this._getConfig(config);
      this.tip = null;

      this._setListeners();
    } // Getters


    var _proto = Tooltip.prototype;

    // Public
    _proto.enable = function enable() {
      this._isEnabled = true;
    };

    _proto.disable = function disable() {
      this._isEnabled = false;
    };

    _proto.toggleEnabled = function toggleEnabled() {
      this._isEnabled = !this._isEnabled;
    };

    _proto.toggle = function toggle(event) {
      if (!this._isEnabled) {
        return;
      }

      if (event) {
        var dataKey = this.constructor.DATA_KEY;
        var context = $(event.currentTarget).data(dataKey);

        if (!context) {
          context = new this.constructor(event.currentTarget, this._getDelegateConfig());
          $(event.currentTarget).data(dataKey, context);
        }

        context._activeTrigger.click = !context._activeTrigger.click;

        if (context._isWithActiveTrigger()) {
          context._enter(null, context);
        } else {
          context._leave(null, context);
        }
      } else {
        if ($(this.getTipElement()).hasClass(ClassName$6.SHOW)) {
          this._leave(null, this);

          return;
        }

        this._enter(null, this);
      }
    };

    _proto.dispose = function dispose() {
      clearTimeout(this._timeout);
      $.removeData(this.element, this.constructor.DATA_KEY);
      $(this.element).off(this.constructor.EVENT_KEY);
      $(this.element).closest('.modal').off('hide.bs.modal');

      if (this.tip) {
        $(this.tip).remove();
      }

      this._isEnabled = null;
      this._timeout = null;
      this._hoverState = null;
      this._activeTrigger = null;

      if (this._popper !== null) {
        this._popper.destroy();
      }

      this._popper = null;
      this.element = null;
      this.config = null;
      this.tip = null;
    };

    _proto.show = function show() {
      var _this = this;

      if ($(this.element).css('display') === 'none') {
        throw new Error('Please use show on visible elements');
      }

      var showEvent = $.Event(this.constructor.Event.SHOW);

      if (this.isWithContent() && this._isEnabled) {
        $(this.element).trigger(showEvent);
        var shadowRoot = Util.findShadowRoot(this.element);
        var isInTheDom = $.contains(shadowRoot !== null ? shadowRoot : this.element.ownerDocument.documentElement, this.element);

        if (showEvent.isDefaultPrevented() || !isInTheDom) {
          return;
        }

        var tip = this.getTipElement();
        var tipId = Util.getUID(this.constructor.NAME);
        tip.setAttribute('id', tipId);
        this.element.setAttribute('aria-describedby', tipId);
        this.setContent();

        if (this.config.animation) {
          $(tip).addClass(ClassName$6.FADE);
        }

        var placement = typeof this.config.placement === 'function' ? this.config.placement.call(this, tip, this.element) : this.config.placement;

        var attachment = this._getAttachment(placement);

        this.addAttachmentClass(attachment);

        var container = this._getContainer();

        $(tip).data(this.constructor.DATA_KEY, this);

        if (!$.contains(this.element.ownerDocument.documentElement, this.tip)) {
          $(tip).appendTo(container);
        }

        $(this.element).trigger(this.constructor.Event.INSERTED);
        this._popper = new Popper(this.element, tip, {
          placement: attachment,
          modifiers: {
            offset: this._getOffset(),
            flip: {
              behavior: this.config.fallbackPlacement
            },
            arrow: {
              element: Selector$6.ARROW
            },
            preventOverflow: {
              boundariesElement: this.config.boundary
            }
          },
          onCreate: function onCreate(data) {
            if (data.originalPlacement !== data.placement) {
              _this._handlePopperPlacementChange(data);
            }
          },
          onUpdate: function onUpdate(data) {
            return _this._handlePopperPlacementChange(data);
          }
        });
        $(tip).addClass(ClassName$6.SHOW); // If this is a touch-enabled device we add extra
        // empty mouseover listeners to the body's immediate children;
        // only needed because of broken event delegation on iOS
        // https://www.quirksmode.org/blog/archives/2014/02/mouse_event_bub.html

        if ('ontouchstart' in document.documentElement) {
          $(document.body).children().on('mouseover', null, $.noop);
        }

        var complete = function complete() {
          if (_this.config.animation) {
            _this._fixTransition();
          }

          var prevHoverState = _this._hoverState;
          _this._hoverState = null;
          $(_this.element).trigger(_this.constructor.Event.SHOWN);

          if (prevHoverState === HoverState.OUT) {
            _this._leave(null, _this);
          }
        };

        if ($(this.tip).hasClass(ClassName$6.FADE)) {
          var transitionDuration = Util.getTransitionDurationFromElement(this.tip);
          $(this.tip).one(Util.TRANSITION_END, complete).emulateTransitionEnd(transitionDuration);
        } else {
          complete();
        }
      }
    };

    _proto.hide = function hide(callback) {
      var _this2 = this;

      var tip = this.getTipElement();
      var hideEvent = $.Event(this.constructor.Event.HIDE);

      var complete = function complete() {
        if (_this2._hoverState !== HoverState.SHOW && tip.parentNode) {
          tip.parentNode.removeChild(tip);
        }

        _this2._cleanTipClass();

        _this2.element.removeAttribute('aria-describedby');

        $(_this2.element).trigger(_this2.constructor.Event.HIDDEN);

        if (_this2._popper !== null) {
          _this2._popper.destroy();
        }

        if (callback) {
          callback();
        }
      };

      $(this.element).trigger(hideEvent);

      if (hideEvent.isDefaultPrevented()) {
        return;
      }

      $(tip).removeClass(ClassName$6.SHOW); // If this is a touch-enabled device we remove the extra
      // empty mouseover listeners we added for iOS support

      if ('ontouchstart' in document.documentElement) {
        $(document.body).children().off('mouseover', null, $.noop);
      }

      this._activeTrigger[Trigger.CLICK] = false;
      this._activeTrigger[Trigger.FOCUS] = false;
      this._activeTrigger[Trigger.HOVER] = false;

      if ($(this.tip).hasClass(ClassName$6.FADE)) {
        var transitionDuration = Util.getTransitionDurationFromElement(tip);
        $(tip).one(Util.TRANSITION_END, complete).emulateTransitionEnd(transitionDuration);
      } else {
        complete();
      }

      this._hoverState = '';
    };

    _proto.update = function update() {
      if (this._popper !== null) {
        this._popper.scheduleUpdate();
      }
    } // Protected
    ;

    _proto.isWithContent = function isWithContent() {
      return Boolean(this.getTitle());
    };

    _proto.addAttachmentClass = function addAttachmentClass(attachment) {
      $(this.getTipElement()).addClass(CLASS_PREFIX + "-" + attachment);
    };

    _proto.getTipElement = function getTipElement() {
      this.tip = this.tip || $(this.config.template)[0];
      return this.tip;
    };

    _proto.setContent = function setContent() {
      var tip = this.getTipElement();
      this.setElementContent($(tip.querySelectorAll(Selector$6.TOOLTIP_INNER)), this.getTitle());
      $(tip).removeClass(ClassName$6.FADE + " " + ClassName$6.SHOW);
    };

    _proto.setElementContent = function setElementContent($element, content) {
      if (typeof content === 'object' && (content.nodeType || content.jquery)) {
        // Content is a DOM node or a jQuery
        if (this.config.html) {
          if (!$(content).parent().is($element)) {
            $element.empty().append(content);
          }
        } else {
          $element.text($(content).text());
        }

        return;
      }

      if (this.config.html) {
        if (this.config.sanitize) {
          content = sanitizeHtml(content, this.config.whiteList, this.config.sanitizeFn);
        }

        $element.html(content);
      } else {
        $element.text(content);
      }
    };

    _proto.getTitle = function getTitle() {
      var title = this.element.getAttribute('data-original-title');

      if (!title) {
        title = typeof this.config.title === 'function' ? this.config.title.call(this.element) : this.config.title;
      }

      return title;
    } // Private
    ;

    _proto._getOffset = function _getOffset() {
      var _this3 = this;

      var offset = {};

      if (typeof this.config.offset === 'function') {
        offset.fn = function (data) {
          data.offsets = _objectSpread({}, data.offsets, _this3.config.offset(data.offsets, _this3.element) || {});
          return data;
        };
      } else {
        offset.offset = this.config.offset;
      }

      return offset;
    };

    _proto._getContainer = function _getContainer() {
      if (this.config.container === false) {
        return document.body;
      }

      if (Util.isElement(this.config.container)) {
        return $(this.config.container);
      }

      return $(document).find(this.config.container);
    };

    _proto._getAttachment = function _getAttachment(placement) {
      return AttachmentMap$1[placement.toUpperCase()];
    };

    _proto._setListeners = function _setListeners() {
      var _this4 = this;

      var triggers = this.config.trigger.split(' ');
      triggers.forEach(function (trigger) {
        if (trigger === 'click') {
          $(_this4.element).on(_this4.constructor.Event.CLICK, _this4.config.selector, function (event) {
            return _this4.toggle(event);
          });
        } else if (trigger !== Trigger.MANUAL) {
          var eventIn = trigger === Trigger.HOVER ? _this4.constructor.Event.MOUSEENTER : _this4.constructor.Event.FOCUSIN;
          var eventOut = trigger === Trigger.HOVER ? _this4.constructor.Event.MOUSELEAVE : _this4.constructor.Event.FOCUSOUT;
          $(_this4.element).on(eventIn, _this4.config.selector, function (event) {
            return _this4._enter(event);
          }).on(eventOut, _this4.config.selector, function (event) {
            return _this4._leave(event);
          });
        }
      });
      $(this.element).closest('.modal').on('hide.bs.modal', function () {
        if (_this4.element) {
          _this4.hide();
        }
      });

      if (this.config.selector) {
        this.config = _objectSpread({}, this.config, {
          trigger: 'manual',
          selector: ''
        });
      } else {
        this._fixTitle();
      }
    };

    _proto._fixTitle = function _fixTitle() {
      var titleType = typeof this.element.getAttribute('data-original-title');

      if (this.element.getAttribute('title') || titleType !== 'string') {
        this.element.setAttribute('data-original-title', this.element.getAttribute('title') || '');
        this.element.setAttribute('title', '');
      }
    };

    _proto._enter = function _enter(event, context) {
      var dataKey = this.constructor.DATA_KEY;
      context = context || $(event.currentTarget).data(dataKey);

      if (!context) {
        context = new this.constructor(event.currentTarget, this._getDelegateConfig());
        $(event.currentTarget).data(dataKey, context);
      }

      if (event) {
        context._activeTrigger[event.type === 'focusin' ? Trigger.FOCUS : Trigger.HOVER] = true;
      }

      if ($(context.getTipElement()).hasClass(ClassName$6.SHOW) || context._hoverState === HoverState.SHOW) {
        context._hoverState = HoverState.SHOW;
        return;
      }

      clearTimeout(context._timeout);
      context._hoverState = HoverState.SHOW;

      if (!context.config.delay || !context.config.delay.show) {
        context.show();
        return;
      }

      context._timeout = setTimeout(function () {
        if (context._hoverState === HoverState.SHOW) {
          context.show();
        }
      }, context.config.delay.show);
    };

    _proto._leave = function _leave(event, context) {
      var dataKey = this.constructor.DATA_KEY;
      context = context || $(event.currentTarget).data(dataKey);

      if (!context) {
        context = new this.constructor(event.currentTarget, this._getDelegateConfig());
        $(event.currentTarget).data(dataKey, context);
      }

      if (event) {
        context._activeTrigger[event.type === 'focusout' ? Trigger.FOCUS : Trigger.HOVER] = false;
      }

      if (context._isWithActiveTrigger()) {
        return;
      }

      clearTimeout(context._timeout);
      context._hoverState = HoverState.OUT;

      if (!context.config.delay || !context.config.delay.hide) {
        context.hide();
        return;
      }

      context._timeout = setTimeout(function () {
        if (context._hoverState === HoverState.OUT) {
          context.hide();
        }
      }, context.config.delay.hide);
    };

    _proto._isWithActiveTrigger = function _isWithActiveTrigger() {
      for (var trigger in this._activeTrigger) {
        if (this._activeTrigger[trigger]) {
          return true;
        }
      }

      return false;
    };

    _proto._getConfig = function _getConfig(config) {
      var dataAttributes = $(this.element).data();
      Object.keys(dataAttributes).forEach(function (dataAttr) {
        if (DISALLOWED_ATTRIBUTES.indexOf(dataAttr) !== -1) {
          delete dataAttributes[dataAttr];
        }
      });
      config = _objectSpread({}, this.constructor.Default, dataAttributes, typeof config === 'object' && config ? config : {});

      if (typeof config.delay === 'number') {
        config.delay = {
          show: config.delay,
          hide: config.delay
        };
      }

      if (typeof config.title === 'number') {
        config.title = config.title.toString();
      }

      if (typeof config.content === 'number') {
        config.content = config.content.toString();
      }

      Util.typeCheckConfig(NAME$6, config, this.constructor.DefaultType);

      if (config.sanitize) {
        config.template = sanitizeHtml(config.template, config.whiteList, config.sanitizeFn);
      }

      return config;
    };

    _proto._getDelegateConfig = function _getDelegateConfig() {
      var config = {};

      if (this.config) {
        for (var key in this.config) {
          if (this.constructor.Default[key] !== this.config[key]) {
            config[key] = this.config[key];
          }
        }
      }

      return config;
    };

    _proto._cleanTipClass = function _cleanTipClass() {
      var $tip = $(this.getTipElement());
      var tabClass = $tip.attr('class').match(BSCLS_PREFIX_REGEX);

      if (tabClass !== null && tabClass.length) {
        $tip.removeClass(tabClass.join(''));
      }
    };

    _proto._handlePopperPlacementChange = function _handlePopperPlacementChange(popperData) {
      var popperInstance = popperData.instance;
      this.tip = popperInstance.popper;

      this._cleanTipClass();

      this.addAttachmentClass(this._getAttachment(popperData.placement));
    };

    _proto._fixTransition = function _fixTransition() {
      var tip = this.getTipElement();
      var initConfigAnimation = this.config.animation;

      if (tip.getAttribute('x-placement') !== null) {
        return;
      }

      $(tip).removeClass(ClassName$6.FADE);
      this.config.animation = false;
      this.hide();
      this.show();
      this.config.animation = initConfigAnimation;
    } // Static
    ;

    Tooltip._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$6);

        var _config = typeof config === 'object' && config;

        if (!data && /dispose|hide/.test(config)) {
          return;
        }

        if (!data) {
          data = new Tooltip(this, _config);
          $(this).data(DATA_KEY$6, data);
        }

        if (typeof config === 'string') {
          if (typeof data[config] === 'undefined') {
            throw new TypeError("No method named \"" + config + "\"");
          }

          data[config]();
        }
      });
    };

    _createClass(Tooltip, null, [{
      key: "VERSION",
      get: function get() {
        return VERSION$6;
      }
    }, {
      key: "Default",
      get: function get() {
        return Default$4;
      }
    }, {
      key: "NAME",
      get: function get() {
        return NAME$6;
      }
    }, {
      key: "DATA_KEY",
      get: function get() {
        return DATA_KEY$6;
      }
    }, {
      key: "Event",
      get: function get() {
        return Event$6;
      }
    }, {
      key: "EVENT_KEY",
      get: function get() {
        return EVENT_KEY$6;
      }
    }, {
      key: "DefaultType",
      get: function get() {
        return DefaultType$4;
      }
    }]);

    return Tooltip;
  }();
  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */


  $.fn[NAME$6] = Tooltip._jQueryInterface;
  $.fn[NAME$6].Constructor = Tooltip;

  $.fn[NAME$6].noConflict = function () {
    $.fn[NAME$6] = JQUERY_NO_CONFLICT$6;
    return Tooltip._jQueryInterface;
  };

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME$7 = 'popover';
  var VERSION$7 = '4.3.1';
  var DATA_KEY$7 = 'bs.popover';
  var EVENT_KEY$7 = "." + DATA_KEY$7;
  var JQUERY_NO_CONFLICT$7 = $.fn[NAME$7];
  var CLASS_PREFIX$1 = 'bs-popover';
  var BSCLS_PREFIX_REGEX$1 = new RegExp("(^|\\s)" + CLASS_PREFIX$1 + "\\S+", 'g');

  var Default$5 = _objectSpread({}, Tooltip.Default, {
    placement: 'right',
    trigger: 'click',
    content: '',
    template: '<div class="popover" role="tooltip">' + '<div class="arrow"></div>' + '<h3 class="popover-header"></h3>' + '<div class="popover-body"></div></div>'
  });

  var DefaultType$5 = _objectSpread({}, Tooltip.DefaultType, {
    content: '(string|element|function)'
  });

  var ClassName$7 = {
    FADE: 'fade',
    SHOW: 'show'
  };
  var Selector$7 = {
    TITLE: '.popover-header',
    CONTENT: '.popover-body'
  };
  var Event$7 = {
    HIDE: "hide" + EVENT_KEY$7,
    HIDDEN: "hidden" + EVENT_KEY$7,
    SHOW: "show" + EVENT_KEY$7,
    SHOWN: "shown" + EVENT_KEY$7,
    INSERTED: "inserted" + EVENT_KEY$7,
    CLICK: "click" + EVENT_KEY$7,
    FOCUSIN: "focusin" + EVENT_KEY$7,
    FOCUSOUT: "focusout" + EVENT_KEY$7,
    MOUSEENTER: "mouseenter" + EVENT_KEY$7,
    MOUSELEAVE: "mouseleave" + EVENT_KEY$7
    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

  };

  var Popover =
  /*#__PURE__*/
  function (_Tooltip) {
    _inheritsLoose(Popover, _Tooltip);

    function Popover() {
      return _Tooltip.apply(this, arguments) || this;
    }

    var _proto = Popover.prototype;

    // Overrides
    _proto.isWithContent = function isWithContent() {
      return this.getTitle() || this._getContent();
    };

    _proto.addAttachmentClass = function addAttachmentClass(attachment) {
      $(this.getTipElement()).addClass(CLASS_PREFIX$1 + "-" + attachment);
    };

    _proto.getTipElement = function getTipElement() {
      this.tip = this.tip || $(this.config.template)[0];
      return this.tip;
    };

    _proto.setContent = function setContent() {
      var $tip = $(this.getTipElement()); // We use append for html objects to maintain js events

      this.setElementContent($tip.find(Selector$7.TITLE), this.getTitle());

      var content = this._getContent();

      if (typeof content === 'function') {
        content = content.call(this.element);
      }

      this.setElementContent($tip.find(Selector$7.CONTENT), content);
      $tip.removeClass(ClassName$7.FADE + " " + ClassName$7.SHOW);
    } // Private
    ;

    _proto._getContent = function _getContent() {
      return this.element.getAttribute('data-content') || this.config.content;
    };

    _proto._cleanTipClass = function _cleanTipClass() {
      var $tip = $(this.getTipElement());
      var tabClass = $tip.attr('class').match(BSCLS_PREFIX_REGEX$1);

      if (tabClass !== null && tabClass.length > 0) {
        $tip.removeClass(tabClass.join(''));
      }
    } // Static
    ;

    Popover._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$7);

        var _config = typeof config === 'object' ? config : null;

        if (!data && /dispose|hide/.test(config)) {
          return;
        }

        if (!data) {
          data = new Popover(this, _config);
          $(this).data(DATA_KEY$7, data);
        }

        if (typeof config === 'string') {
          if (typeof data[config] === 'undefined') {
            throw new TypeError("No method named \"" + config + "\"");
          }

          data[config]();
        }
      });
    };

    _createClass(Popover, null, [{
      key: "VERSION",
      // Getters
      get: function get() {
        return VERSION$7;
      }
    }, {
      key: "Default",
      get: function get() {
        return Default$5;
      }
    }, {
      key: "NAME",
      get: function get() {
        return NAME$7;
      }
    }, {
      key: "DATA_KEY",
      get: function get() {
        return DATA_KEY$7;
      }
    }, {
      key: "Event",
      get: function get() {
        return Event$7;
      }
    }, {
      key: "EVENT_KEY",
      get: function get() {
        return EVENT_KEY$7;
      }
    }, {
      key: "DefaultType",
      get: function get() {
        return DefaultType$5;
      }
    }]);

    return Popover;
  }(Tooltip);
  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */


  $.fn[NAME$7] = Popover._jQueryInterface;
  $.fn[NAME$7].Constructor = Popover;

  $.fn[NAME$7].noConflict = function () {
    $.fn[NAME$7] = JQUERY_NO_CONFLICT$7;
    return Popover._jQueryInterface;
  };

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME$8 = 'scrollspy';
  var VERSION$8 = '4.3.1';
  var DATA_KEY$8 = 'bs.scrollspy';
  var EVENT_KEY$8 = "." + DATA_KEY$8;
  var DATA_API_KEY$6 = '.data-api';
  var JQUERY_NO_CONFLICT$8 = $.fn[NAME$8];
  var Default$6 = {
    offset: 10,
    method: 'auto',
    target: ''
  };
  var DefaultType$6 = {
    offset: 'number',
    method: 'string',
    target: '(string|element)'
  };
  var Event$8 = {
    ACTIVATE: "activate" + EVENT_KEY$8,
    SCROLL: "scroll" + EVENT_KEY$8,
    LOAD_DATA_API: "load" + EVENT_KEY$8 + DATA_API_KEY$6
  };
  var ClassName$8 = {
    DROPDOWN_ITEM: 'dropdown-item',
    DROPDOWN_MENU: 'dropdown-menu',
    ACTIVE: 'active'
  };
  var Selector$8 = {
    DATA_SPY: '[data-spy="scroll"]',
    ACTIVE: '.active',
    NAV_LIST_GROUP: '.nav, .list-group',
    NAV_LINKS: '.nav-link',
    NAV_ITEMS: '.nav-item',
    LIST_ITEMS: '.list-group-item',
    DROPDOWN: '.dropdown',
    DROPDOWN_ITEMS: '.dropdown-item',
    DROPDOWN_TOGGLE: '.dropdown-toggle'
  };
  var OffsetMethod = {
    OFFSET: 'offset',
    POSITION: 'position'
    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

  };

  var ScrollSpy =
  /*#__PURE__*/
  function () {
    function ScrollSpy(element, config) {
      var _this = this;

      this._element = element;
      this._scrollElement = element.tagName === 'BODY' ? window : element;
      this._config = this._getConfig(config);
      this._selector = this._config.target + " " + Selector$8.NAV_LINKS + "," + (this._config.target + " " + Selector$8.LIST_ITEMS + ",") + (this._config.target + " " + Selector$8.DROPDOWN_ITEMS);
      this._offsets = [];
      this._targets = [];
      this._activeTarget = null;
      this._scrollHeight = 0;
      $(this._scrollElement).on(Event$8.SCROLL, function (event) {
        return _this._process(event);
      });
      this.refresh();

      this._process();
    } // Getters


    var _proto = ScrollSpy.prototype;

    // Public
    _proto.refresh = function refresh() {
      var _this2 = this;

      var autoMethod = this._scrollElement === this._scrollElement.window ? OffsetMethod.OFFSET : OffsetMethod.POSITION;
      var offsetMethod = this._config.method === 'auto' ? autoMethod : this._config.method;
      var offsetBase = offsetMethod === OffsetMethod.POSITION ? this._getScrollTop() : 0;
      this._offsets = [];
      this._targets = [];
      this._scrollHeight = this._getScrollHeight();
      var targets = [].slice.call(document.querySelectorAll(this._selector));
      targets.map(function (element) {
        var target;
        var targetSelector = Util.getSelectorFromElement(element);

        if (targetSelector) {
          target = document.querySelector(targetSelector);
        }

        if (target) {
          var targetBCR = target.getBoundingClientRect();

          if (targetBCR.width || targetBCR.height) {
            // TODO (fat): remove sketch reliance on jQuery position/offset
            return [$(target)[offsetMethod]().top + offsetBase, targetSelector];
          }
        }

        return null;
      }).filter(function (item) {
        return item;
      }).sort(function (a, b) {
        return a[0] - b[0];
      }).forEach(function (item) {
        _this2._offsets.push(item[0]);

        _this2._targets.push(item[1]);
      });
    };

    _proto.dispose = function dispose() {
      $.removeData(this._element, DATA_KEY$8);
      $(this._scrollElement).off(EVENT_KEY$8);
      this._element = null;
      this._scrollElement = null;
      this._config = null;
      this._selector = null;
      this._offsets = null;
      this._targets = null;
      this._activeTarget = null;
      this._scrollHeight = null;
    } // Private
    ;

    _proto._getConfig = function _getConfig(config) {
      config = _objectSpread({}, Default$6, typeof config === 'object' && config ? config : {});

      if (typeof config.target !== 'string') {
        var id = $(config.target).attr('id');

        if (!id) {
          id = Util.getUID(NAME$8);
          $(config.target).attr('id', id);
        }

        config.target = "#" + id;
      }

      Util.typeCheckConfig(NAME$8, config, DefaultType$6);
      return config;
    };

    _proto._getScrollTop = function _getScrollTop() {
      return this._scrollElement === window ? this._scrollElement.pageYOffset : this._scrollElement.scrollTop;
    };

    _proto._getScrollHeight = function _getScrollHeight() {
      return this._scrollElement.scrollHeight || Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
    };

    _proto._getOffsetHeight = function _getOffsetHeight() {
      return this._scrollElement === window ? window.innerHeight : this._scrollElement.getBoundingClientRect().height;
    };

    _proto._process = function _process() {
      var scrollTop = this._getScrollTop() + this._config.offset;

      var scrollHeight = this._getScrollHeight();

      var maxScroll = this._config.offset + scrollHeight - this._getOffsetHeight();

      if (this._scrollHeight !== scrollHeight) {
        this.refresh();
      }

      if (scrollTop >= maxScroll) {
        var target = this._targets[this._targets.length - 1];

        if (this._activeTarget !== target) {
          this._activate(target);
        }

        return;
      }

      if (this._activeTarget && scrollTop < this._offsets[0] && this._offsets[0] > 0) {
        this._activeTarget = null;

        this._clear();

        return;
      }

      var offsetLength = this._offsets.length;

      for (var i = offsetLength; i--;) {
        var isActiveTarget = this._activeTarget !== this._targets[i] && scrollTop >= this._offsets[i] && (typeof this._offsets[i + 1] === 'undefined' || scrollTop < this._offsets[i + 1]);

        if (isActiveTarget) {
          this._activate(this._targets[i]);
        }
      }
    };

    _proto._activate = function _activate(target) {
      this._activeTarget = target;

      this._clear();

      var queries = this._selector.split(',').map(function (selector) {
        return selector + "[data-target=\"" + target + "\"]," + selector + "[href=\"" + target + "\"]";
      });

      var $link = $([].slice.call(document.querySelectorAll(queries.join(','))));

      if ($link.hasClass(ClassName$8.DROPDOWN_ITEM)) {
        $link.closest(Selector$8.DROPDOWN).find(Selector$8.DROPDOWN_TOGGLE).addClass(ClassName$8.ACTIVE);
        $link.addClass(ClassName$8.ACTIVE);
      } else {
        // Set triggered link as active
        $link.addClass(ClassName$8.ACTIVE); // Set triggered links parents as active
        // With both <ul> and <nav> markup a parent is the previous sibling of any nav ancestor

        $link.parents(Selector$8.NAV_LIST_GROUP).prev(Selector$8.NAV_LINKS + ", " + Selector$8.LIST_ITEMS).addClass(ClassName$8.ACTIVE); // Handle special case when .nav-link is inside .nav-item

        $link.parents(Selector$8.NAV_LIST_GROUP).prev(Selector$8.NAV_ITEMS).children(Selector$8.NAV_LINKS).addClass(ClassName$8.ACTIVE);
      }

      $(this._scrollElement).trigger(Event$8.ACTIVATE, {
        relatedTarget: target
      });
    };

    _proto._clear = function _clear() {
      [].slice.call(document.querySelectorAll(this._selector)).filter(function (node) {
        return node.classList.contains(ClassName$8.ACTIVE);
      }).forEach(function (node) {
        return node.classList.remove(ClassName$8.ACTIVE);
      });
    } // Static
    ;

    ScrollSpy._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$8);

        var _config = typeof config === 'object' && config;

        if (!data) {
          data = new ScrollSpy(this, _config);
          $(this).data(DATA_KEY$8, data);
        }

        if (typeof config === 'string') {
          if (typeof data[config] === 'undefined') {
            throw new TypeError("No method named \"" + config + "\"");
          }

          data[config]();
        }
      });
    };

    _createClass(ScrollSpy, null, [{
      key: "VERSION",
      get: function get() {
        return VERSION$8;
      }
    }, {
      key: "Default",
      get: function get() {
        return Default$6;
      }
    }]);

    return ScrollSpy;
  }();
  /**
   * ------------------------------------------------------------------------
   * Data Api implementation
   * ------------------------------------------------------------------------
   */


  $(window).on(Event$8.LOAD_DATA_API, function () {
    var scrollSpys = [].slice.call(document.querySelectorAll(Selector$8.DATA_SPY));
    var scrollSpysLength = scrollSpys.length;

    for (var i = scrollSpysLength; i--;) {
      var $spy = $(scrollSpys[i]);

      ScrollSpy._jQueryInterface.call($spy, $spy.data());
    }
  });
  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */

  $.fn[NAME$8] = ScrollSpy._jQueryInterface;
  $.fn[NAME$8].Constructor = ScrollSpy;

  $.fn[NAME$8].noConflict = function () {
    $.fn[NAME$8] = JQUERY_NO_CONFLICT$8;
    return ScrollSpy._jQueryInterface;
  };

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME$9 = 'tab';
  var VERSION$9 = '4.3.1';
  var DATA_KEY$9 = 'bs.tab';
  var EVENT_KEY$9 = "." + DATA_KEY$9;
  var DATA_API_KEY$7 = '.data-api';
  var JQUERY_NO_CONFLICT$9 = $.fn[NAME$9];
  var Event$9 = {
    HIDE: "hide" + EVENT_KEY$9,
    HIDDEN: "hidden" + EVENT_KEY$9,
    SHOW: "show" + EVENT_KEY$9,
    SHOWN: "shown" + EVENT_KEY$9,
    CLICK_DATA_API: "click" + EVENT_KEY$9 + DATA_API_KEY$7
  };
  var ClassName$9 = {
    DROPDOWN_MENU: 'dropdown-menu',
    ACTIVE: 'active',
    DISABLED: 'disabled',
    FADE: 'fade',
    SHOW: 'show'
  };
  var Selector$9 = {
    DROPDOWN: '.dropdown',
    NAV_LIST_GROUP: '.nav, .list-group',
    ACTIVE: '.active',
    ACTIVE_UL: '> li > .active',
    DATA_TOGGLE: '[data-toggle="tab"], [data-toggle="pill"], [data-toggle="list"]',
    DROPDOWN_TOGGLE: '.dropdown-toggle',
    DROPDOWN_ACTIVE_CHILD: '> .dropdown-menu .active'
    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

  };

  var Tab =
  /*#__PURE__*/
  function () {
    function Tab(element) {
      this._element = element;
    } // Getters


    var _proto = Tab.prototype;

    // Public
    _proto.show = function show() {
      var _this = this;

      if (this._element.parentNode && this._element.parentNode.nodeType === Node.ELEMENT_NODE && $(this._element).hasClass(ClassName$9.ACTIVE) || $(this._element).hasClass(ClassName$9.DISABLED)) {
        return;
      }

      var target;
      var previous;
      var listElement = $(this._element).closest(Selector$9.NAV_LIST_GROUP)[0];
      var selector = Util.getSelectorFromElement(this._element);

      if (listElement) {
        var itemSelector = listElement.nodeName === 'UL' || listElement.nodeName === 'OL' ? Selector$9.ACTIVE_UL : Selector$9.ACTIVE;
        previous = $.makeArray($(listElement).find(itemSelector));
        previous = previous[previous.length - 1];
      }

      var hideEvent = $.Event(Event$9.HIDE, {
        relatedTarget: this._element
      });
      var showEvent = $.Event(Event$9.SHOW, {
        relatedTarget: previous
      });

      if (previous) {
        $(previous).trigger(hideEvent);
      }

      $(this._element).trigger(showEvent);

      if (showEvent.isDefaultPrevented() || hideEvent.isDefaultPrevented()) {
        return;
      }

      if (selector) {
        target = document.querySelector(selector);
      }

      this._activate(this._element, listElement);

      var complete = function complete() {
        var hiddenEvent = $.Event(Event$9.HIDDEN, {
          relatedTarget: _this._element
        });
        var shownEvent = $.Event(Event$9.SHOWN, {
          relatedTarget: previous
        });
        $(previous).trigger(hiddenEvent);
        $(_this._element).trigger(shownEvent);
      };

      if (target) {
        this._activate(target, target.parentNode, complete);
      } else {
        complete();
      }
    };

    _proto.dispose = function dispose() {
      $.removeData(this._element, DATA_KEY$9);
      this._element = null;
    } // Private
    ;

    _proto._activate = function _activate(element, container, callback) {
      var _this2 = this;

      var activeElements = container && (container.nodeName === 'UL' || container.nodeName === 'OL') ? $(container).find(Selector$9.ACTIVE_UL) : $(container).children(Selector$9.ACTIVE);
      var active = activeElements[0];
      var isTransitioning = callback && active && $(active).hasClass(ClassName$9.FADE);

      var complete = function complete() {
        return _this2._transitionComplete(element, active, callback);
      };

      if (active && isTransitioning) {
        var transitionDuration = Util.getTransitionDurationFromElement(active);
        $(active).removeClass(ClassName$9.SHOW).one(Util.TRANSITION_END, complete).emulateTransitionEnd(transitionDuration);
      } else {
        complete();
      }
    };

    _proto._transitionComplete = function _transitionComplete(element, active, callback) {
      if (active) {
        $(active).removeClass(ClassName$9.ACTIVE);
        var dropdownChild = $(active.parentNode).find(Selector$9.DROPDOWN_ACTIVE_CHILD)[0];

        if (dropdownChild) {
          $(dropdownChild).removeClass(ClassName$9.ACTIVE);
        }

        if (active.getAttribute('role') === 'tab') {
          active.setAttribute('aria-selected', false);
        }
      }

      $(element).addClass(ClassName$9.ACTIVE);

      if (element.getAttribute('role') === 'tab') {
        element.setAttribute('aria-selected', true);
      }

      Util.reflow(element);

      if (element.classList.contains(ClassName$9.FADE)) {
        element.classList.add(ClassName$9.SHOW);
      }

      if (element.parentNode && $(element.parentNode).hasClass(ClassName$9.DROPDOWN_MENU)) {
        var dropdownElement = $(element).closest(Selector$9.DROPDOWN)[0];

        if (dropdownElement) {
          var dropdownToggleList = [].slice.call(dropdownElement.querySelectorAll(Selector$9.DROPDOWN_TOGGLE));
          $(dropdownToggleList).addClass(ClassName$9.ACTIVE);
        }

        element.setAttribute('aria-expanded', true);
      }

      if (callback) {
        callback();
      }
    } // Static
    ;

    Tab._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var $this = $(this);
        var data = $this.data(DATA_KEY$9);

        if (!data) {
          data = new Tab(this);
          $this.data(DATA_KEY$9, data);
        }

        if (typeof config === 'string') {
          if (typeof data[config] === 'undefined') {
            throw new TypeError("No method named \"" + config + "\"");
          }

          data[config]();
        }
      });
    };

    _createClass(Tab, null, [{
      key: "VERSION",
      get: function get() {
        return VERSION$9;
      }
    }]);

    return Tab;
  }();
  /**
   * ------------------------------------------------------------------------
   * Data Api implementation
   * ------------------------------------------------------------------------
   */


  $(document).on(Event$9.CLICK_DATA_API, Selector$9.DATA_TOGGLE, function (event) {
    event.preventDefault();

    Tab._jQueryInterface.call($(this), 'show');
  });
  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */

  $.fn[NAME$9] = Tab._jQueryInterface;
  $.fn[NAME$9].Constructor = Tab;

  $.fn[NAME$9].noConflict = function () {
    $.fn[NAME$9] = JQUERY_NO_CONFLICT$9;
    return Tab._jQueryInterface;
  };

  /**
   * ------------------------------------------------------------------------
   * Constants
   * ------------------------------------------------------------------------
   */

  var NAME$a = 'toast';
  var VERSION$a = '4.3.1';
  var DATA_KEY$a = 'bs.toast';
  var EVENT_KEY$a = "." + DATA_KEY$a;
  var JQUERY_NO_CONFLICT$a = $.fn[NAME$a];
  var Event$a = {
    CLICK_DISMISS: "click.dismiss" + EVENT_KEY$a,
    HIDE: "hide" + EVENT_KEY$a,
    HIDDEN: "hidden" + EVENT_KEY$a,
    SHOW: "show" + EVENT_KEY$a,
    SHOWN: "shown" + EVENT_KEY$a
  };
  var ClassName$a = {
    FADE: 'fade',
    HIDE: 'hide',
    SHOW: 'show',
    SHOWING: 'showing'
  };
  var DefaultType$7 = {
    animation: 'boolean',
    autohide: 'boolean',
    delay: 'number'
  };
  var Default$7 = {
    animation: true,
    autohide: true,
    delay: 500
  };
  var Selector$a = {
    DATA_DISMISS: '[data-dismiss="toast"]'
    /**
     * ------------------------------------------------------------------------
     * Class Definition
     * ------------------------------------------------------------------------
     */

  };

  var Toast =
  /*#__PURE__*/
  function () {
    function Toast(element, config) {
      this._element = element;
      this._config = this._getConfig(config);
      this._timeout = null;

      this._setListeners();
    } // Getters


    var _proto = Toast.prototype;

    // Public
    _proto.show = function show() {
      var _this = this;

      $(this._element).trigger(Event$a.SHOW);

      if (this._config.animation) {
        this._element.classList.add(ClassName$a.FADE);
      }

      var complete = function complete() {
        _this._element.classList.remove(ClassName$a.SHOWING);

        _this._element.classList.add(ClassName$a.SHOW);

        $(_this._element).trigger(Event$a.SHOWN);

        if (_this._config.autohide) {
          _this.hide();
        }
      };

      this._element.classList.remove(ClassName$a.HIDE);

      this._element.classList.add(ClassName$a.SHOWING);

      if (this._config.animation) {
        var transitionDuration = Util.getTransitionDurationFromElement(this._element);
        $(this._element).one(Util.TRANSITION_END, complete).emulateTransitionEnd(transitionDuration);
      } else {
        complete();
      }
    };

    _proto.hide = function hide(withoutTimeout) {
      var _this2 = this;

      if (!this._element.classList.contains(ClassName$a.SHOW)) {
        return;
      }

      $(this._element).trigger(Event$a.HIDE);

      if (withoutTimeout) {
        this._close();
      } else {
        this._timeout = setTimeout(function () {
          _this2._close();
        }, this._config.delay);
      }
    };

    _proto.dispose = function dispose() {
      clearTimeout(this._timeout);
      this._timeout = null;

      if (this._element.classList.contains(ClassName$a.SHOW)) {
        this._element.classList.remove(ClassName$a.SHOW);
      }

      $(this._element).off(Event$a.CLICK_DISMISS);
      $.removeData(this._element, DATA_KEY$a);
      this._element = null;
      this._config = null;
    } // Private
    ;

    _proto._getConfig = function _getConfig(config) {
      config = _objectSpread({}, Default$7, $(this._element).data(), typeof config === 'object' && config ? config : {});
      Util.typeCheckConfig(NAME$a, config, this.constructor.DefaultType);
      return config;
    };

    _proto._setListeners = function _setListeners() {
      var _this3 = this;

      $(this._element).on(Event$a.CLICK_DISMISS, Selector$a.DATA_DISMISS, function () {
        return _this3.hide(true);
      });
    };

    _proto._close = function _close() {
      var _this4 = this;

      var complete = function complete() {
        _this4._element.classList.add(ClassName$a.HIDE);

        $(_this4._element).trigger(Event$a.HIDDEN);
      };

      this._element.classList.remove(ClassName$a.SHOW);

      if (this._config.animation) {
        var transitionDuration = Util.getTransitionDurationFromElement(this._element);
        $(this._element).one(Util.TRANSITION_END, complete).emulateTransitionEnd(transitionDuration);
      } else {
        complete();
      }
    } // Static
    ;

    Toast._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var $element = $(this);
        var data = $element.data(DATA_KEY$a);

        var _config = typeof config === 'object' && config;

        if (!data) {
          data = new Toast(this, _config);
          $element.data(DATA_KEY$a, data);
        }

        if (typeof config === 'string') {
          if (typeof data[config] === 'undefined') {
            throw new TypeError("No method named \"" + config + "\"");
          }

          data[config](this);
        }
      });
    };

    _createClass(Toast, null, [{
      key: "VERSION",
      get: function get() {
        return VERSION$a;
      }
    }, {
      key: "DefaultType",
      get: function get() {
        return DefaultType$7;
      }
    }, {
      key: "Default",
      get: function get() {
        return Default$7;
      }
    }]);

    return Toast;
  }();
  /**
   * ------------------------------------------------------------------------
   * jQuery
   * ------------------------------------------------------------------------
   */


  $.fn[NAME$a] = Toast._jQueryInterface;
  $.fn[NAME$a].Constructor = Toast;

  $.fn[NAME$a].noConflict = function () {
    $.fn[NAME$a] = JQUERY_NO_CONFLICT$a;
    return Toast._jQueryInterface;
  };

  /**
   * --------------------------------------------------------------------------
   * Bootstrap (v4.3.1): index.js
   * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
   * --------------------------------------------------------------------------
   */

  (function () {
    if (typeof $ === 'undefined') {
      throw new TypeError('Bootstrap\'s JavaScript requires jQuery. jQuery must be included before Bootstrap\'s JavaScript.');
    }

    var version = $.fn.jquery.split(' ')[0].split('.');
    var minMajor = 1;
    var ltMajor = 2;
    var minMinor = 9;
    var minPatch = 1;
    var maxMajor = 4;

    if (version[0] < ltMajor && version[1] < minMinor || version[0] === minMajor && version[1] === minMinor && version[2] < minPatch || version[0] >= maxMajor) {
      throw new Error('Bootstrap\'s JavaScript requires at least jQuery v1.9.1 but less than v4.0.0');
    }
  })();

  exports.Util = Util;
  exports.Alert = Alert;
  exports.Button = Button;
  exports.Carousel = Carousel;
  exports.Collapse = Collapse;
  exports.Dropdown = Dropdown;
  exports.Modal = Modal;
  exports.Popover = Popover;
  exports.Scrollspy = ScrollSpy;
  exports.Tab = Tab;
  exports.Toast = Toast;
  exports.Tooltip = Tooltip;

  Object.defineProperty(exports, '__esModule', { value: true });

}));
//# sourceMappingURL=bootstrap.js.map

/*
 * jQuery Easing v1.3 - http://gsgd.co.uk/sandbox/jquery/easing/
 *
 * Uses the built in easing capabilities added In jQuery 1.1
 * to offer multiple easing options
 *
 * TERMS OF USE - jQuery Easing
 *
 * Open source under the BSD License.
 *
 * Copyright © 2008 George McGinley Smith
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list of
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list
 * of conditions and the following disclaimer in the documentation and/or other materials
 * provided with the distribution.
 *
 * Neither the name of the author nor the names of contributors may be used to endorse
 * or promote products derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *  COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 *  EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 *  GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 *  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 *
*/

// t: current time, b: begInnIng value, c: change In value, d: duration
jQuery.easing['jswing'] = jQuery.easing['swing'];

jQuery.extend( jQuery.easing,
    {
        def: 'easeOutQuad',
        swing: function (x, t, b, c, d) {
            //alert(jQuery.easing.default);
            return jQuery.easing[jQuery.easing.def](x, t, b, c, d);
        },
        easeInQuad: function (x, t, b, c, d) {
            return c*(t/=d)*t + b;
        },
        easeOutQuad: function (x, t, b, c, d) {
            return -c *(t/=d)*(t-2) + b;
        },
        easeInOutQuad: function (x, t, b, c, d) {
            if ((t/=d/2) < 1) return c/2*t*t + b;
            return -c/2 * ((--t)*(t-2) - 1) + b;
        },
        easeInCubic: function (x, t, b, c, d) {
            return c*(t/=d)*t*t + b;
        },
        easeOutCubic: function (x, t, b, c, d) {
            return c*((t=t/d-1)*t*t + 1) + b;
        },
        easeInOutCubic: function (x, t, b, c, d) {
            if ((t/=d/2) < 1) return c/2*t*t*t + b;
            return c/2*((t-=2)*t*t + 2) + b;
        },
        easeInQuart: function (x, t, b, c, d) {
            return c*(t/=d)*t*t*t + b;
        },
        easeOutQuart: function (x, t, b, c, d) {
            return -c * ((t=t/d-1)*t*t*t - 1) + b;
        },
        easeInOutQuart: function (x, t, b, c, d) {
            if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
            return -c/2 * ((t-=2)*t*t*t - 2) + b;
        },
        easeInQuint: function (x, t, b, c, d) {
            return c*(t/=d)*t*t*t*t + b;
        },
        easeOutQuint: function (x, t, b, c, d) {
            return c*((t=t/d-1)*t*t*t*t + 1) + b;
        },
        easeInOutQuint: function (x, t, b, c, d) {
            if ((t/=d/2) < 1) return c/2*t*t*t*t*t + b;
            return c/2*((t-=2)*t*t*t*t + 2) + b;
        },
        easeInSine: function (x, t, b, c, d) {
            return -c * Math.cos(t/d * (Math.PI/2)) + c + b;
        },
        easeOutSine: function (x, t, b, c, d) {
            return c * Math.sin(t/d * (Math.PI/2)) + b;
        },
        easeInOutSine: function (x, t, b, c, d) {
            return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
        },
        easeInExpo: function (x, t, b, c, d) {
            return (t==0) ? b : c * Math.pow(2, 10 * (t/d - 1)) + b;
        },
        easeOutExpo: function (x, t, b, c, d) {
            return (t==d) ? b+c : c * (-Math.pow(2, -10 * t/d) + 1) + b;
        },
        easeInOutExpo: function (x, t, b, c, d) {
            if (t==0) return b;
            if (t==d) return b+c;
            if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
            return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
        },
        easeInCirc: function (x, t, b, c, d) {
            return -c * (Math.sqrt(1 - (t/=d)*t) - 1) + b;
        },
        easeOutCirc: function (x, t, b, c, d) {
            return c * Math.sqrt(1 - (t=t/d-1)*t) + b;
        },
        easeInOutCirc: function (x, t, b, c, d) {
            if ((t/=d/2) < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;
            return c/2 * (Math.sqrt(1 - (t-=2)*t) + 1) + b;
        },
        easeInElastic: function (x, t, b, c, d) {
            var s=1.70158;var p=0;var a=c;
            if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
            if (a < Math.abs(c)) { a=c; var s=p/4; }
            else var s = p/(2*Math.PI) * Math.asin (c/a);
            return -(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
        },
        easeOutElastic: function (x, t, b, c, d) {
            var s=1.70158;var p=0;var a=c;
            if (t==0) return b;  if ((t/=d)==1) return b+c;  if (!p) p=d*.3;
            if (a < Math.abs(c)) { a=c; var s=p/4; }
            else var s = p/(2*Math.PI) * Math.asin (c/a);
            return a*Math.pow(2,-10*t) * Math.sin( (t*d-s)*(2*Math.PI)/p ) + c + b;
        },
        easeInOutElastic: function (x, t, b, c, d) {
            var s=1.70158;var p=0;var a=c;
            if (t==0) return b;  if ((t/=d/2)==2) return b+c;  if (!p) p=d*(.3*1.5);
            if (a < Math.abs(c)) { a=c; var s=p/4; }
            else var s = p/(2*Math.PI) * Math.asin (c/a);
            if (t < 1) return -.5*(a*Math.pow(2,10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )) + b;
            return a*Math.pow(2,-10*(t-=1)) * Math.sin( (t*d-s)*(2*Math.PI)/p )*.5 + c + b;
        },
        easeInBack: function (x, t, b, c, d, s) {
            if (s == undefined) s = 1.70158;
            return c*(t/=d)*t*((s+1)*t - s) + b;
        },
        easeOutBack: function (x, t, b, c, d, s) {
            if (s == undefined) s = 1.70158;
            return c*((t=t/d-1)*t*((s+1)*t + s) + 1) + b;
        },
        easeInOutBack: function (x, t, b, c, d, s) {
            if (s == undefined) s = 1.70158;
            if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;
            return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;
        },
        easeInBounce: function (x, t, b, c, d) {
            return c - jQuery.easing.easeOutBounce (x, d-t, 0, c, d) + b;
        },
        easeOutBounce: function (x, t, b, c, d) {
            if ((t/=d) < (1/2.75)) {
                return c*(7.5625*t*t) + b;
            } else if (t < (2/2.75)) {
                return c*(7.5625*(t-=(1.5/2.75))*t + .75) + b;
            } else if (t < (2.5/2.75)) {
                return c*(7.5625*(t-=(2.25/2.75))*t + .9375) + b;
            } else {
                return c*(7.5625*(t-=(2.625/2.75))*t + .984375) + b;
            }
        },
        easeInOutBounce: function (x, t, b, c, d) {
            if (t < d/2) return jQuery.easing.easeInBounce (x, t*2, 0, c, d) * .5 + b;
            return jQuery.easing.easeOutBounce (x, t*2-d, 0, c, d) * .5 + c*.5 + b;
        }
    });

/*
 *
 * TERMS OF USE - EASING EQUATIONS
 *
 * Open source under the BSD License.
 *
 * Copyright © 2001 Robert Penner
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list of
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list
 * of conditions and the following disclaimer in the documentation and/or other materials
 * provided with the distribution.
 *
 * Neither the name of the author nor the names of contributors may be used to endorse
 * or promote products derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *  COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 *  EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 *  GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 *  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

/* =========================================================
 * bootstrap-datepicker.js
 * Repo: https://github.com/uxsolutions/bootstrap-datepicker/
 * Demo: https://uxsolutions.github.io/bootstrap-datepicker/
 * Docs: https://bootstrap-datepicker.readthedocs.org/
 * =========================================================
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================= */

(function(factory){
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        factory(require('jquery'));
    } else {
        factory(jQuery);
    }
}(function($, undefined){
    function UTCDate(){
        return new Date(Date.UTC.apply(Date, arguments));
    }
    function UTCToday(){
        var today = new Date();
        return UTCDate(today.getFullYear(), today.getMonth(), today.getDate());
    }
    function isUTCEquals(date1, date2) {
        return (
            date1.getUTCFullYear() === date2.getUTCFullYear() &&
            date1.getUTCMonth() === date2.getUTCMonth() &&
            date1.getUTCDate() === date2.getUTCDate()
        );
    }
    function alias(method, deprecationMsg){
        return function(){
            if (deprecationMsg !== undefined) {
                $.fn.datepicker.deprecated(deprecationMsg);
            }

            return this[method].apply(this, arguments);
        };
    }
    function isValidDate(d) {
        return d && !isNaN(d.getTime());
    }

    var DateArray = (function(){
        var extras = {
            get: function(i){
                return this.slice(i)[0];
            },
            contains: function(d){
                // Array.indexOf is not cross-browser;
                // $.inArray doesn't work with Dates
                var val = d && d.valueOf();
                for (var i=0, l=this.length; i < l; i++)
                    // Use date arithmetic to allow dates with different times to match
                    if (0 <= this[i].valueOf() - val && this[i].valueOf() - val < 1000*60*60*24)
                        return i;
                return -1;
            },
            remove: function(i){
                this.splice(i,1);
            },
            replace: function(new_array){
                if (!new_array)
                    return;
                if (!$.isArray(new_array))
                    new_array = [new_array];
                this.clear();
                this.push.apply(this, new_array);
            },
            clear: function(){
                this.length = 0;
            },
            copy: function(){
                var a = new DateArray();
                a.replace(this);
                return a;
            }
        };

        return function(){
            var a = [];
            a.push.apply(a, arguments);
            $.extend(a, extras);
            return a;
        };
    })();


    // Picker object

    var Datepicker = function(element, options){
        $.data(element, 'datepicker', this);

        this._events = [];
        this._secondaryEvents = [];

        this._process_options(options);

        this.dates = new DateArray();
        this.viewDate = this.o.defaultViewDate;
        this.focusDate = null;

        this.element = $(element);
        this.isInput = this.element.is('input');
        this.inputField = this.isInput ? this.element : this.element.find('input');
        this.component = this.element.hasClass('date') ? this.element.find('.add-on, .input-group-addon, .input-group-append, .input-group-prepend, .btn') : false;
        if (this.component && this.component.length === 0){
            this.component = false;
        }

        if (this.o.isInline === null){
            this.isInline = !this.component && !this.isInput;
        } else {
            this.isInline = this.o.isInline;
        }

        this.picker = $(DPGlobal.template);

        // Checking templates and inserting
        if (this._check_template(this.o.templates.leftArrow)) {
            this.picker.find('.prev').html(this.o.templates.leftArrow);
        }

        if (this._check_template(this.o.templates.rightArrow)) {
            this.picker.find('.next').html(this.o.templates.rightArrow);
        }

        this._buildEvents();
        this._attachEvents();

        if (this.isInline){
            this.picker.addClass('datepicker-inline').appendTo(this.element);
        }
        else {
            this.picker.addClass('datepicker-dropdown dropdown-menu');
        }

        if (this.o.rtl){
            this.picker.addClass('datepicker-rtl');
        }

        if (this.o.calendarWeeks) {
            this.picker.find('.datepicker-days .datepicker-switch, thead .datepicker-title, tfoot .today, tfoot .clear')
                .attr('colspan', function(i, val){
                    return Number(val) + 1;
                });
        }

        this._process_options({
            startDate: this._o.startDate,
            endDate: this._o.endDate,
            daysOfWeekDisabled: this.o.daysOfWeekDisabled,
            daysOfWeekHighlighted: this.o.daysOfWeekHighlighted,
            datesDisabled: this.o.datesDisabled
        });

        this._allow_update = false;
        this.setViewMode(this.o.startView);
        this._allow_update = true;

        this.fillDow();
        this.fillMonths();

        this.update();

        if (this.isInline){
            this.show();
        }
    };

    Datepicker.prototype = {
        constructor: Datepicker,

        _resolveViewName: function(view){
            $.each(DPGlobal.viewModes, function(i, viewMode){
                if (view === i || $.inArray(view, viewMode.names) !== -1){
                    view = i;
                    return false;
                }
            });

            return view;
        },

        _resolveDaysOfWeek: function(daysOfWeek){
            if (!$.isArray(daysOfWeek))
                daysOfWeek = daysOfWeek.split(/[,\s]*/);
            return $.map(daysOfWeek, Number);
        },

        _check_template: function(tmp){
            try {
                // If empty
                if (tmp === undefined || tmp === "") {
                    return false;
                }
                // If no html, everything ok
                if ((tmp.match(/[<>]/g) || []).length <= 0) {
                    return true;
                }
                // Checking if html is fine
                var jDom = $(tmp);
                return jDom.length > 0;
            }
            catch (ex) {
                return false;
            }
        },

        _process_options: function(opts){
            // Store raw options for reference
            this._o = $.extend({}, this._o, opts);
            // Processed options
            var o = this.o = $.extend({}, this._o);

            // Check if "de-DE" style date is available, if not language should
            // fallback to 2 letter code eg "de"
            var lang = o.language;
            if (!dates[lang]){
                lang = lang.split('-')[0];
                if (!dates[lang])
                    lang = defaults.language;
            }
            o.language = lang;

            // Retrieve view index from any aliases
            o.startView = this._resolveViewName(o.startView);
            o.minViewMode = this._resolveViewName(o.minViewMode);
            o.maxViewMode = this._resolveViewName(o.maxViewMode);

            // Check view is between min and max
            o.startView = Math.max(this.o.minViewMode, Math.min(this.o.maxViewMode, o.startView));

            // true, false, or Number > 0
            if (o.multidate !== true){
                o.multidate = Number(o.multidate) || false;
                if (o.multidate !== false)
                    o.multidate = Math.max(0, o.multidate);
            }
            o.multidateSeparator = String(o.multidateSeparator);

            o.weekStart %= 7;
            o.weekEnd = (o.weekStart + 6) % 7;

            var format = DPGlobal.parseFormat(o.format);
            if (o.startDate !== -Infinity){
                if (!!o.startDate){
                    if (o.startDate instanceof Date)
                        o.startDate = this._local_to_utc(this._zero_time(o.startDate));
                    else
                        o.startDate = DPGlobal.parseDate(o.startDate, format, o.language, o.assumeNearbyYear);
                }
                else {
                    o.startDate = -Infinity;
                }
            }
            if (o.endDate !== Infinity){
                if (!!o.endDate){
                    if (o.endDate instanceof Date)
                        o.endDate = this._local_to_utc(this._zero_time(o.endDate));
                    else
                        o.endDate = DPGlobal.parseDate(o.endDate, format, o.language, o.assumeNearbyYear);
                }
                else {
                    o.endDate = Infinity;
                }
            }

            o.daysOfWeekDisabled = this._resolveDaysOfWeek(o.daysOfWeekDisabled||[]);
            o.daysOfWeekHighlighted = this._resolveDaysOfWeek(o.daysOfWeekHighlighted||[]);

            o.datesDisabled = o.datesDisabled||[];
            if (!$.isArray(o.datesDisabled)) {
                o.datesDisabled = o.datesDisabled.split(',');
            }
            o.datesDisabled = $.map(o.datesDisabled, function(d){
                return DPGlobal.parseDate(d, format, o.language, o.assumeNearbyYear);
            });

            var plc = String(o.orientation).toLowerCase().split(/\s+/g),
                _plc = o.orientation.toLowerCase();
            plc = $.grep(plc, function(word){
                return /^auto|left|right|top|bottom$/.test(word);
            });
            o.orientation = {x: 'auto', y: 'auto'};
            if (!_plc || _plc === 'auto')
                ; // no action
            else if (plc.length === 1){
                switch (plc[0]){
                    case 'top':
                    case 'bottom':
                        o.orientation.y = plc[0];
                        break;
                    case 'left':
                    case 'right':
                        o.orientation.x = plc[0];
                        break;
                }
            }
            else {
                _plc = $.grep(plc, function(word){
                    return /^left|right$/.test(word);
                });
                o.orientation.x = _plc[0] || 'auto';

                _plc = $.grep(plc, function(word){
                    return /^top|bottom$/.test(word);
                });
                o.orientation.y = _plc[0] || 'auto';
            }
            if (o.defaultViewDate instanceof Date || typeof o.defaultViewDate === 'string') {
                o.defaultViewDate = DPGlobal.parseDate(o.defaultViewDate, format, o.language, o.assumeNearbyYear);
            } else if (o.defaultViewDate) {
                var year = o.defaultViewDate.year || new Date().getFullYear();
                var month = o.defaultViewDate.month || 0;
                var day = o.defaultViewDate.day || 1;
                o.defaultViewDate = UTCDate(year, month, day);
            } else {
                o.defaultViewDate = UTCToday();
            }
        },
        _applyEvents: function(evs){
            for (var i=0, el, ch, ev; i < evs.length; i++){
                el = evs[i][0];
                if (evs[i].length === 2){
                    ch = undefined;
                    ev = evs[i][1];
                } else if (evs[i].length === 3){
                    ch = evs[i][1];
                    ev = evs[i][2];
                }
                el.on(ev, ch);
            }
        },
        _unapplyEvents: function(evs){
            for (var i=0, el, ev, ch; i < evs.length; i++){
                el = evs[i][0];
                if (evs[i].length === 2){
                    ch = undefined;
                    ev = evs[i][1];
                } else if (evs[i].length === 3){
                    ch = evs[i][1];
                    ev = evs[i][2];
                }
                el.off(ev, ch);
            }
        },
        _buildEvents: function(){
            var events = {
                keyup: $.proxy(function(e){
                    if ($.inArray(e.keyCode, [27, 37, 39, 38, 40, 32, 13, 9]) === -1)
                        this.update();
                }, this),
                keydown: $.proxy(this.keydown, this),
                paste: $.proxy(this.paste, this)
            };

            if (this.o.showOnFocus === true) {
                events.focus = $.proxy(this.show, this);
            }

            if (this.isInput) { // single input
                this._events = [
                    [this.element, events]
                ];
            }
            // component: input + button
            else if (this.component && this.inputField.length) {
                this._events = [
                    // For components that are not readonly, allow keyboard nav
                    [this.inputField, events],
                    [this.component, {
                        click: $.proxy(this.show, this)
                    }]
                ];
            }
            else {
                this._events = [
                    [this.element, {
                        click: $.proxy(this.show, this),
                        keydown: $.proxy(this.keydown, this)
                    }]
                ];
            }
            this._events.push(
                // Component: listen for blur on element descendants
                [this.element, '*', {
                    blur: $.proxy(function(e){
                        this._focused_from = e.target;
                    }, this)
                }],
                // Input: listen for blur on element
                [this.element, {
                    blur: $.proxy(function(e){
                        this._focused_from = e.target;
                    }, this)
                }]
            );

            if (this.o.immediateUpdates) {
                // Trigger input updates immediately on changed year/month
                this._events.push([this.element, {
                    'changeYear changeMonth': $.proxy(function(e){
                        this.update(e.date);
                    }, this)
                }]);
            }

            this._secondaryEvents = [
                [this.picker, {
                    click: $.proxy(this.click, this)
                }],
                [this.picker, '.prev, .next', {
                    click: $.proxy(this.navArrowsClick, this)
                }],
                [this.picker, '.day:not(.disabled)', {
                    click: $.proxy(this.dayCellClick, this)
                }],
                [$(window), {
                    resize: $.proxy(this.place, this)
                }],
                [$(document), {
                    'mousedown touchstart': $.proxy(function(e){
                        // Clicked outside the datepicker, hide it
                        if (!(
                            this.element.is(e.target) ||
                            this.element.find(e.target).length ||
                            this.picker.is(e.target) ||
                            this.picker.find(e.target).length ||
                            this.isInline
                        )){
                            this.hide();
                        }
                    }, this)
                }]
            ];
        },
        _attachEvents: function(){
            this._detachEvents();
            this._applyEvents(this._events);
        },
        _detachEvents: function(){
            this._unapplyEvents(this._events);
        },
        _attachSecondaryEvents: function(){
            this._detachSecondaryEvents();
            this._applyEvents(this._secondaryEvents);
        },
        _detachSecondaryEvents: function(){
            this._unapplyEvents(this._secondaryEvents);
        },
        _trigger: function(event, altdate){
            var date = altdate || this.dates.get(-1),
                local_date = this._utc_to_local(date);

            this.element.trigger({
                type: event,
                date: local_date,
                viewMode: this.viewMode,
                dates: $.map(this.dates, this._utc_to_local),
                format: $.proxy(function(ix, format){
                    if (arguments.length === 0){
                        ix = this.dates.length - 1;
                        format = this.o.format;
                    } else if (typeof ix === 'string'){
                        format = ix;
                        ix = this.dates.length - 1;
                    }
                    format = format || this.o.format;
                    var date = this.dates.get(ix);
                    return DPGlobal.formatDate(date, format, this.o.language);
                }, this)
            });
        },

        show: function(){
            if (this.inputField.is(':disabled') || (this.inputField.prop('readonly') && this.o.enableOnReadonly === false))
                return;
            if (!this.isInline)
                this.picker.appendTo(this.o.container);
            this.place();
            this.picker.show();
            this._attachSecondaryEvents();
            this._trigger('show');
            if ((window.navigator.msMaxTouchPoints || 'ontouchstart' in document) && this.o.disableTouchKeyboard) {
                $(this.element).blur();
            }
            return this;
        },

        hide: function(){
            if (this.isInline || !this.picker.is(':visible'))
                return this;
            this.focusDate = null;
            this.picker.hide().detach();
            this._detachSecondaryEvents();
            this.setViewMode(this.o.startView);

            if (this.o.forceParse && this.inputField.val())
                this.setValue();
            this._trigger('hide');
            return this;
        },

        destroy: function(){
            this.hide();
            this._detachEvents();
            this._detachSecondaryEvents();
            this.picker.remove();
            delete this.element.data().datepicker;
            if (!this.isInput){
                delete this.element.data().date;
            }
            return this;
        },

        paste: function(e){
            var dateString;
            if (e.originalEvent.clipboardData && e.originalEvent.clipboardData.types
                && $.inArray('text/plain', e.originalEvent.clipboardData.types) !== -1) {
                dateString = e.originalEvent.clipboardData.getData('text/plain');
            } else if (window.clipboardData) {
                dateString = window.clipboardData.getData('Text');
            } else {
                return;
            }
            this.setDate(dateString);
            this.update();
            e.preventDefault();
        },

        _utc_to_local: function(utc){
            if (!utc) {
                return utc;
            }

            var local = new Date(utc.getTime() + (utc.getTimezoneOffset() * 60000));

            if (local.getTimezoneOffset() !== utc.getTimezoneOffset()) {
                local = new Date(utc.getTime() + (local.getTimezoneOffset() * 60000));
            }

            return local;
        },
        _local_to_utc: function(local){
            return local && new Date(local.getTime() - (local.getTimezoneOffset()*60000));
        },
        _zero_time: function(local){
            return local && new Date(local.getFullYear(), local.getMonth(), local.getDate());
        },
        _zero_utc_time: function(utc){
            return utc && UTCDate(utc.getUTCFullYear(), utc.getUTCMonth(), utc.getUTCDate());
        },

        getDates: function(){
            return $.map(this.dates, this._utc_to_local);
        },

        getUTCDates: function(){
            return $.map(this.dates, function(d){
                return new Date(d);
            });
        },

        getDate: function(){
            return this._utc_to_local(this.getUTCDate());
        },

        getUTCDate: function(){
            var selected_date = this.dates.get(-1);
            if (selected_date !== undefined) {
                return new Date(selected_date);
            } else {
                return null;
            }
        },

        clearDates: function(){
            this.inputField.val('');
            this._trigger('changeDate');
            this.update();
            if (this.o.autoclose) {
                this.hide();
            }
        },

        setDates: function(){
            var args = $.isArray(arguments[0]) ? arguments[0] : arguments;
            this.update.apply(this, args);
            this._trigger('changeDate');
            this.setValue();
            return this;
        },

        setUTCDates: function(){
            var args = $.isArray(arguments[0]) ? arguments[0] : arguments;
            this.setDates.apply(this, $.map(args, this._utc_to_local));
            return this;
        },

        setDate: alias('setDates'),
        setUTCDate: alias('setUTCDates'),
        remove: alias('destroy', 'Method `remove` is deprecated and will be removed in version 2.0. Use `destroy` instead'),

        setValue: function(){
            var formatted = this.getFormattedDate();
            this.inputField.val(formatted);
            return this;
        },

        getFormattedDate: function(format){
            if (format === undefined)
                format = this.o.format;

            var lang = this.o.language;
            return $.map(this.dates, function(d){
                return DPGlobal.formatDate(d, format, lang);
            }).join(this.o.multidateSeparator);
        },

        getStartDate: function(){
            return this.o.startDate;
        },

        setStartDate: function(startDate){
            this._process_options({startDate: startDate});
            this.update();
            this.updateNavArrows();
            return this;
        },

        getEndDate: function(){
            return this.o.endDate;
        },

        setEndDate: function(endDate){
            this._process_options({endDate: endDate});
            this.update();
            this.updateNavArrows();
            return this;
        },

        setDaysOfWeekDisabled: function(daysOfWeekDisabled){
            this._process_options({daysOfWeekDisabled: daysOfWeekDisabled});
            this.update();
            return this;
        },

        setDaysOfWeekHighlighted: function(daysOfWeekHighlighted){
            this._process_options({daysOfWeekHighlighted: daysOfWeekHighlighted});
            this.update();
            return this;
        },

        setDatesDisabled: function(datesDisabled){
            this._process_options({datesDisabled: datesDisabled});
            this.update();
            return this;
        },

        place: function(){
            if (this.isInline)
                return this;
            var calendarWidth = this.picker.outerWidth(),
                calendarHeight = this.picker.outerHeight(),
                visualPadding = 10,
                container = $(this.o.container),
                windowWidth = container.width(),
                scrollTop = this.o.container === 'body' ? $(document).scrollTop() : container.scrollTop(),
                appendOffset = container.offset();

            var parentsZindex = [0];
            this.element.parents().each(function(){
                var itemZIndex = $(this).css('z-index');
                if (itemZIndex !== 'auto' && Number(itemZIndex) !== 0) parentsZindex.push(Number(itemZIndex));
            });
            var zIndex = Math.max.apply(Math, parentsZindex) + this.o.zIndexOffset;
            var offset = this.component ? this.component.parent().offset() : this.element.offset();
            var height = this.component ? this.component.outerHeight(true) : this.element.outerHeight(false);
            var width = this.component ? this.component.outerWidth(true) : this.element.outerWidth(false);
            var left = offset.left - appendOffset.left;
            var top = offset.top - appendOffset.top;

            if (this.o.container !== 'body') {
                top += scrollTop;
            }

            this.picker.removeClass(
                'datepicker-orient-top datepicker-orient-bottom '+
                'datepicker-orient-right datepicker-orient-left'
            );

            if (this.o.orientation.x !== 'auto'){
                this.picker.addClass('datepicker-orient-' + this.o.orientation.x);
                if (this.o.orientation.x === 'right')
                    left -= calendarWidth - width;
            }
            // auto x orientation is best-placement: if it crosses a window
            // edge, fudge it sideways
            else {
                if (offset.left < 0) {
                    // component is outside the window on the left side. Move it into visible range
                    this.picker.addClass('datepicker-orient-left');
                    left -= offset.left - visualPadding;
                } else if (left + calendarWidth > windowWidth) {
                    // the calendar passes the widow right edge. Align it to component right side
                    this.picker.addClass('datepicker-orient-right');
                    left += width - calendarWidth;
                } else {
                    if (this.o.rtl) {
                        // Default to right
                        this.picker.addClass('datepicker-orient-right');
                    } else {
                        // Default to left
                        this.picker.addClass('datepicker-orient-left');
                    }
                }
            }

            // auto y orientation is best-situation: top or bottom, no fudging,
            // decision based on which shows more of the calendar
            var yorient = this.o.orientation.y,
                top_overflow;
            if (yorient === 'auto'){
                top_overflow = -scrollTop + top - calendarHeight;
                yorient = top_overflow < 0 ? 'bottom' : 'top';
            }

            this.picker.addClass('datepicker-orient-' + yorient);
            if (yorient === 'top')
                top -= calendarHeight + parseInt(this.picker.css('padding-top'));
            else
                top += height;

            if (this.o.rtl) {
                var right = windowWidth - (left + width);
                this.picker.css({
                    top: top,
                    right: right,
                    zIndex: zIndex
                });
            } else {
                this.picker.css({
                    top: top,
                    left: left,
                    zIndex: zIndex
                });
            }
            return this;
        },

        _allow_update: true,
        update: function(){
            if (!this._allow_update)
                return this;

            var oldDates = this.dates.copy(),
                dates = [],
                fromArgs = false;
            if (arguments.length){
                $.each(arguments, $.proxy(function(i, date){
                    if (date instanceof Date)
                        date = this._local_to_utc(date);
                    dates.push(date);
                }, this));
                fromArgs = true;
            } else {
                dates = this.isInput
                    ? this.element.val()
                    : this.element.data('date') || this.inputField.val();
                if (dates && this.o.multidate)
                    dates = dates.split(this.o.multidateSeparator);
                else
                    dates = [dates];
                delete this.element.data().date;
            }

            dates = $.map(dates, $.proxy(function(date){
                return DPGlobal.parseDate(date, this.o.format, this.o.language, this.o.assumeNearbyYear);
            }, this));
            dates = $.grep(dates, $.proxy(function(date){
                return (
                    !this.dateWithinRange(date) ||
                    !date
                );
            }, this), true);
            this.dates.replace(dates);

            if (this.o.updateViewDate) {
                if (this.dates.length)
                    this.viewDate = new Date(this.dates.get(-1));
                else if (this.viewDate < this.o.startDate)
                    this.viewDate = new Date(this.o.startDate);
                else if (this.viewDate > this.o.endDate)
                    this.viewDate = new Date(this.o.endDate);
                else
                    this.viewDate = this.o.defaultViewDate;
            }

            if (fromArgs){
                // setting date by clicking
                this.setValue();
                this.element.change();
            }
            else if (this.dates.length){
                // setting date by typing
                if (String(oldDates) !== String(this.dates) && fromArgs) {
                    this._trigger('changeDate');
                    this.element.change();
                }
            }
            if (!this.dates.length && oldDates.length) {
                this._trigger('clearDate');
                this.element.change();
            }

            this.fill();
            return this;
        },

        fillDow: function(){
            if (this.o.showWeekDays) {
                var dowCnt = this.o.weekStart,
                    html = '<tr>';
                if (this.o.calendarWeeks){
                    html += '<th class="cw">&#160;</th>';
                }
                while (dowCnt < this.o.weekStart + 7){
                    html += '<th class="dow';
                    if ($.inArray(dowCnt, this.o.daysOfWeekDisabled) !== -1)
                        html += ' disabled';
                    html += '">'+dates[this.o.language].daysMin[(dowCnt++)%7]+'</th>';
                }
                html += '</tr>';
                this.picker.find('.datepicker-days thead').append(html);
            }
        },

        fillMonths: function(){
            var localDate = this._utc_to_local(this.viewDate);
            var html = '';
            var focused;
            for (var i = 0; i < 12; i++){
                focused = localDate && localDate.getMonth() === i ? ' focused' : '';
                html += '<span class="month' + focused + '">' + dates[this.o.language].monthsShort[i] + '</span>';
            }
            this.picker.find('.datepicker-months td').html(html);
        },

        setRange: function(range){
            if (!range || !range.length)
                delete this.range;
            else
                this.range = $.map(range, function(d){
                    return d.valueOf();
                });
            this.fill();
        },

        getClassNames: function(date){
            var cls = [],
                year = this.viewDate.getUTCFullYear(),
                month = this.viewDate.getUTCMonth(),
                today = UTCToday();
            if (date.getUTCFullYear() < year || (date.getUTCFullYear() === year && date.getUTCMonth() < month)){
                cls.push('old');
            } else if (date.getUTCFullYear() > year || (date.getUTCFullYear() === year && date.getUTCMonth() > month)){
                cls.push('new');
            }
            if (this.focusDate && date.valueOf() === this.focusDate.valueOf())
                cls.push('focused');
            // Compare internal UTC date with UTC today, not local today
            if (this.o.todayHighlight && isUTCEquals(date, today)) {
                cls.push('today');
            }
            if (this.dates.contains(date) !== -1)
                cls.push('active');
            if (!this.dateWithinRange(date)){
                cls.push('disabled');
            }
            if (this.dateIsDisabled(date)){
                cls.push('disabled', 'disabled-date');
            }
            if ($.inArray(date.getUTCDay(), this.o.daysOfWeekHighlighted) !== -1){
                cls.push('highlighted');
            }

            if (this.range){
                if (date > this.range[0] && date < this.range[this.range.length-1]){
                    cls.push('range');
                }
                if ($.inArray(date.valueOf(), this.range) !== -1){
                    cls.push('selected');
                }
                if (date.valueOf() === this.range[0]){
                    cls.push('range-start');
                }
                if (date.valueOf() === this.range[this.range.length-1]){
                    cls.push('range-end');
                }
            }
            return cls;
        },

        _fill_yearsView: function(selector, cssClass, factor, year, startYear, endYear, beforeFn){
            var html = '';
            var step = factor / 10;
            var view = this.picker.find(selector);
            var startVal = Math.floor(year / factor) * factor;
            var endVal = startVal + step * 9;
            var focusedVal = Math.floor(this.viewDate.getFullYear() / step) * step;
            var selected = $.map(this.dates, function(d){
                return Math.floor(d.getUTCFullYear() / step) * step;
            });

            var classes, tooltip, before;
            for (var currVal = startVal - step; currVal <= endVal + step; currVal += step) {
                classes = [cssClass];
                tooltip = null;

                if (currVal === startVal - step) {
                    classes.push('old');
                } else if (currVal === endVal + step) {
                    classes.push('new');
                }
                if ($.inArray(currVal, selected) !== -1) {
                    classes.push('active');
                }
                if (currVal < startYear || currVal > endYear) {
                    classes.push('disabled');
                }
                if (currVal === focusedVal) {
                    classes.push('focused');
                }

                if (beforeFn !== $.noop) {
                    before = beforeFn(new Date(currVal, 0, 1));
                    if (before === undefined) {
                        before = {};
                    } else if (typeof before === 'boolean') {
                        before = {enabled: before};
                    } else if (typeof before === 'string') {
                        before = {classes: before};
                    }
                    if (before.enabled === false) {
                        classes.push('disabled');
                    }
                    if (before.classes) {
                        classes = classes.concat(before.classes.split(/\s+/));
                    }
                    if (before.tooltip) {
                        tooltip = before.tooltip;
                    }
                }

                html += '<span class="' + classes.join(' ') + '"' + (tooltip ? ' title="' + tooltip + '"' : '') + '>' + currVal + '</span>';
            }

            view.find('.datepicker-switch').text(startVal + '-' + endVal);
            view.find('td').html(html);
        },

        fill: function(){
            var d = new Date(this.viewDate),
                year = d.getUTCFullYear(),
                month = d.getUTCMonth(),
                startYear = this.o.startDate !== -Infinity ? this.o.startDate.getUTCFullYear() : -Infinity,
                startMonth = this.o.startDate !== -Infinity ? this.o.startDate.getUTCMonth() : -Infinity,
                endYear = this.o.endDate !== Infinity ? this.o.endDate.getUTCFullYear() : Infinity,
                endMonth = this.o.endDate !== Infinity ? this.o.endDate.getUTCMonth() : Infinity,
                todaytxt = dates[this.o.language].today || dates['en'].today || '',
                cleartxt = dates[this.o.language].clear || dates['en'].clear || '',
                titleFormat = dates[this.o.language].titleFormat || dates['en'].titleFormat,
                todayDate = UTCToday(),
                titleBtnVisible = (this.o.todayBtn === true || this.o.todayBtn === 'linked') && todayDate >= this.o.startDate && todayDate <= this.o.endDate && !this.weekOfDateIsDisabled(todayDate),
                tooltip,
                before;
            if (isNaN(year) || isNaN(month))
                return;
            this.picker.find('.datepicker-days .datepicker-switch')
                .text(DPGlobal.formatDate(d, titleFormat, this.o.language));
            this.picker.find('tfoot .today')
                .text(todaytxt)
                .css('display', titleBtnVisible ? 'table-cell' : 'none');
            this.picker.find('tfoot .clear')
                .text(cleartxt)
                .css('display', this.o.clearBtn === true ? 'table-cell' : 'none');
            this.picker.find('thead .datepicker-title')
                .text(this.o.title)
                .css('display', typeof this.o.title === 'string' && this.o.title !== '' ? 'table-cell' : 'none');
            this.updateNavArrows();
            this.fillMonths();
            var prevMonth = UTCDate(year, month, 0),
                day = prevMonth.getUTCDate();
            prevMonth.setUTCDate(day - (prevMonth.getUTCDay() - this.o.weekStart + 7)%7);
            var nextMonth = new Date(prevMonth);
            if (prevMonth.getUTCFullYear() < 100){
                nextMonth.setUTCFullYear(prevMonth.getUTCFullYear());
            }
            nextMonth.setUTCDate(nextMonth.getUTCDate() + 42);
            nextMonth = nextMonth.valueOf();
            var html = [];
            var weekDay, clsName;
            while (prevMonth.valueOf() < nextMonth){
                weekDay = prevMonth.getUTCDay();
                if (weekDay === this.o.weekStart){
                    html.push('<tr>');
                    if (this.o.calendarWeeks){
                        // ISO 8601: First week contains first thursday.
                        // ISO also states week starts on Monday, but we can be more abstract here.
                        var
                            // Start of current week: based on weekstart/current date
                            ws = new Date(+prevMonth + (this.o.weekStart - weekDay - 7) % 7 * 864e5),
                            // Thursday of this week
                            th = new Date(Number(ws) + (7 + 4 - ws.getUTCDay()) % 7 * 864e5),
                            // First Thursday of year, year from thursday
                            yth = new Date(Number(yth = UTCDate(th.getUTCFullYear(), 0, 1)) + (7 + 4 - yth.getUTCDay()) % 7 * 864e5),
                            // Calendar week: ms between thursdays, div ms per day, div 7 days
                            calWeek = (th - yth) / 864e5 / 7 + 1;
                        html.push('<td class="cw">'+ calWeek +'</td>');
                    }
                }
                clsName = this.getClassNames(prevMonth);
                clsName.push('day');

                var content = prevMonth.getUTCDate();

                if (this.o.beforeShowDay !== $.noop){
                    before = this.o.beforeShowDay(this._utc_to_local(prevMonth));
                    if (before === undefined)
                        before = {};
                    else if (typeof before === 'boolean')
                        before = {enabled: before};
                    else if (typeof before === 'string')
                        before = {classes: before};
                    if (before.enabled === false)
                        clsName.push('disabled');
                    if (before.classes)
                        clsName = clsName.concat(before.classes.split(/\s+/));
                    if (before.tooltip)
                        tooltip = before.tooltip;
                    if (before.content)
                        content = before.content;
                }

                //Check if uniqueSort exists (supported by jquery >=1.12 and >=2.2)
                //Fallback to unique function for older jquery versions
                if ($.isFunction($.uniqueSort)) {
                    clsName = $.uniqueSort(clsName);
                } else {
                    clsName = $.unique(clsName);
                }

                html.push('<td class="'+clsName.join(' ')+'"' + (tooltip ? ' title="'+tooltip+'"' : '') + ' data-date="' + prevMonth.getTime().toString() + '">' + content + '</td>');
                tooltip = null;
                if (weekDay === this.o.weekEnd){
                    html.push('</tr>');
                }
                prevMonth.setUTCDate(prevMonth.getUTCDate() + 1);
            }
            this.picker.find('.datepicker-days tbody').html(html.join(''));

            var monthsTitle = dates[this.o.language].monthsTitle || dates['en'].monthsTitle || 'Months';
            var months = this.picker.find('.datepicker-months')
                .find('.datepicker-switch')
                .text(this.o.maxViewMode < 2 ? monthsTitle : year)
                .end()
                .find('tbody span').removeClass('active');

            $.each(this.dates, function(i, d){
                if (d.getUTCFullYear() === year)
                    months.eq(d.getUTCMonth()).addClass('active');
            });

            if (year < startYear || year > endYear){
                months.addClass('disabled');
            }
            if (year === startYear){
                months.slice(0, startMonth).addClass('disabled');
            }
            if (year === endYear){
                months.slice(endMonth+1).addClass('disabled');
            }

            if (this.o.beforeShowMonth !== $.noop){
                var that = this;
                $.each(months, function(i, month){
                    var moDate = new Date(year, i, 1);
                    var before = that.o.beforeShowMonth(moDate);
                    if (before === undefined)
                        before = {};
                    else if (typeof before === 'boolean')
                        before = {enabled: before};
                    else if (typeof before === 'string')
                        before = {classes: before};
                    if (before.enabled === false && !$(month).hasClass('disabled'))
                        $(month).addClass('disabled');
                    if (before.classes)
                        $(month).addClass(before.classes);
                    if (before.tooltip)
                        $(month).prop('title', before.tooltip);
                });
            }

            // Generating decade/years picker
            this._fill_yearsView(
                '.datepicker-years',
                'year',
                10,
                year,
                startYear,
                endYear,
                this.o.beforeShowYear
            );

            // Generating century/decades picker
            this._fill_yearsView(
                '.datepicker-decades',
                'decade',
                100,
                year,
                startYear,
                endYear,
                this.o.beforeShowDecade
            );

            // Generating millennium/centuries picker
            this._fill_yearsView(
                '.datepicker-centuries',
                'century',
                1000,
                year,
                startYear,
                endYear,
                this.o.beforeShowCentury
            );
        },

        updateNavArrows: function(){
            if (!this._allow_update)
                return;

            var d = new Date(this.viewDate),
                year = d.getUTCFullYear(),
                month = d.getUTCMonth(),
                startYear = this.o.startDate !== -Infinity ? this.o.startDate.getUTCFullYear() : -Infinity,
                startMonth = this.o.startDate !== -Infinity ? this.o.startDate.getUTCMonth() : -Infinity,
                endYear = this.o.endDate !== Infinity ? this.o.endDate.getUTCFullYear() : Infinity,
                endMonth = this.o.endDate !== Infinity ? this.o.endDate.getUTCMonth() : Infinity,
                prevIsDisabled,
                nextIsDisabled,
                factor = 1;
            switch (this.viewMode){
                case 4:
                    factor *= 10;
                /* falls through */
                case 3:
                    factor *= 10;
                /* falls through */
                case 2:
                    factor *= 10;
                /* falls through */
                case 1:
                    prevIsDisabled = Math.floor(year / factor) * factor <= startYear;
                    nextIsDisabled = Math.floor(year / factor) * factor + factor > endYear;
                    break;
                case 0:
                    prevIsDisabled = year <= startYear && month <= startMonth;
                    nextIsDisabled = year >= endYear && month >= endMonth;
                    break;
            }

            this.picker.find('.prev').toggleClass('disabled', prevIsDisabled);
            this.picker.find('.next').toggleClass('disabled', nextIsDisabled);
        },

        click: function(e){
            e.preventDefault();
            e.stopPropagation();

            var target, dir, day, year, month;
            target = $(e.target);

            // Clicked on the switch
            if (target.hasClass('datepicker-switch') && this.viewMode !== this.o.maxViewMode){
                this.setViewMode(this.viewMode + 1);
            }

            // Clicked on today button
            if (target.hasClass('today') && !target.hasClass('day')){
                this.setViewMode(0);
                this._setDate(UTCToday(), this.o.todayBtn === 'linked' ? null : 'view');
            }

            // Clicked on clear button
            if (target.hasClass('clear')){
                this.clearDates();
            }

            if (!target.hasClass('disabled')){
                // Clicked on a month, year, decade, century
                if (target.hasClass('month')
                    || target.hasClass('year')
                    || target.hasClass('decade')
                    || target.hasClass('century')) {
                    this.viewDate.setUTCDate(1);

                    day = 1;
                    if (this.viewMode === 1){
                        month = target.parent().find('span').index(target);
                        year = this.viewDate.getUTCFullYear();
                        this.viewDate.setUTCMonth(month);
                    } else {
                        month = 0;
                        year = Number(target.text());
                        this.viewDate.setUTCFullYear(year);
                    }

                    this._trigger(DPGlobal.viewModes[this.viewMode - 1].e, this.viewDate);

                    if (this.viewMode === this.o.minViewMode){
                        this._setDate(UTCDate(year, month, day));
                    } else {
                        this.setViewMode(this.viewMode - 1);
                        this.fill();
                    }
                }
            }

            if (this.picker.is(':visible') && this._focused_from){
                this._focused_from.focus();
            }
            delete this._focused_from;
        },

        dayCellClick: function(e){
            var $target = $(e.currentTarget);
            var timestamp = $target.data('date');
            var date = new Date(timestamp);

            if (this.o.updateViewDate) {
                if (date.getUTCFullYear() !== this.viewDate.getUTCFullYear()) {
                    this._trigger('changeYear', this.viewDate);
                }

                if (date.getUTCMonth() !== this.viewDate.getUTCMonth()) {
                    this._trigger('changeMonth', this.viewDate);
                }
            }
            this._setDate(date);
        },

        // Clicked on prev or next
        navArrowsClick: function(e){
            var $target = $(e.currentTarget);
            var dir = $target.hasClass('prev') ? -1 : 1;
            if (this.viewMode !== 0){
                dir *= DPGlobal.viewModes[this.viewMode].navStep * 12;
            }
            this.viewDate = this.moveMonth(this.viewDate, dir);
            this._trigger(DPGlobal.viewModes[this.viewMode].e, this.viewDate);
            this.fill();
        },

        _toggle_multidate: function(date){
            var ix = this.dates.contains(date);
            if (!date){
                this.dates.clear();
            }

            if (ix !== -1){
                if (this.o.multidate === true || this.o.multidate > 1 || this.o.toggleActive){
                    this.dates.remove(ix);
                }
            } else if (this.o.multidate === false) {
                this.dates.clear();
                this.dates.push(date);
            }
            else {
                this.dates.push(date);
            }

            if (typeof this.o.multidate === 'number')
                while (this.dates.length > this.o.multidate)
                    this.dates.remove(0);
        },

        _setDate: function(date, which){
            if (!which || which === 'date')
                this._toggle_multidate(date && new Date(date));
            if ((!which && this.o.updateViewDate) || which === 'view')
                this.viewDate = date && new Date(date);

            this.fill();
            this.setValue();
            if (!which || which !== 'view') {
                this._trigger('changeDate');
            }
            this.inputField.trigger('change');
            if (this.o.autoclose && (!which || which === 'date')){
                this.hide();
            }
        },

        moveDay: function(date, dir){
            var newDate = new Date(date);
            newDate.setUTCDate(date.getUTCDate() + dir);

            return newDate;
        },

        moveWeek: function(date, dir){
            return this.moveDay(date, dir * 7);
        },

        moveMonth: function(date, dir){
            if (!isValidDate(date))
                return this.o.defaultViewDate;
            if (!dir)
                return date;
            var new_date = new Date(date.valueOf()),
                day = new_date.getUTCDate(),
                month = new_date.getUTCMonth(),
                mag = Math.abs(dir),
                new_month, test;
            dir = dir > 0 ? 1 : -1;
            if (mag === 1){
                test = dir === -1
                    // If going back one month, make sure month is not current month
                    // (eg, Mar 31 -> Feb 31 == Feb 28, not Mar 02)
                    ? function(){
                        return new_date.getUTCMonth() === month;
                    }
                    // If going forward one month, make sure month is as expected
                    // (eg, Jan 31 -> Feb 31 == Feb 28, not Mar 02)
                    : function(){
                        return new_date.getUTCMonth() !== new_month;
                    };
                new_month = month + dir;
                new_date.setUTCMonth(new_month);
                // Dec -> Jan (12) or Jan -> Dec (-1) -- limit expected date to 0-11
                new_month = (new_month + 12) % 12;
            }
            else {
                // For magnitudes >1, move one month at a time...
                for (var i=0; i < mag; i++)
                    // ...which might decrease the day (eg, Jan 31 to Feb 28, etc)...
                    new_date = this.moveMonth(new_date, dir);
                // ...then reset the day, keeping it in the new month
                new_month = new_date.getUTCMonth();
                new_date.setUTCDate(day);
                test = function(){
                    return new_month !== new_date.getUTCMonth();
                };
            }
            // Common date-resetting loop -- if date is beyond end of month, make it
            // end of month
            while (test()){
                new_date.setUTCDate(--day);
                new_date.setUTCMonth(new_month);
            }
            return new_date;
        },

        moveYear: function(date, dir){
            return this.moveMonth(date, dir*12);
        },

        moveAvailableDate: function(date, dir, fn){
            do {
                date = this[fn](date, dir);

                if (!this.dateWithinRange(date))
                    return false;

                fn = 'moveDay';
            }
            while (this.dateIsDisabled(date));

            return date;
        },

        weekOfDateIsDisabled: function(date){
            return $.inArray(date.getUTCDay(), this.o.daysOfWeekDisabled) !== -1;
        },

        dateIsDisabled: function(date){
            return (
                this.weekOfDateIsDisabled(date) ||
                $.grep(this.o.datesDisabled, function(d){
                    return isUTCEquals(date, d);
                }).length > 0
            );
        },

        dateWithinRange: function(date){
            return date >= this.o.startDate && date <= this.o.endDate;
        },

        keydown: function(e){
            if (!this.picker.is(':visible')){
                if (e.keyCode === 40 || e.keyCode === 27) { // allow down to re-show picker
                    this.show();
                    e.stopPropagation();
                }
                return;
            }
            var dateChanged = false,
                dir, newViewDate,
                focusDate = this.focusDate || this.viewDate;
            switch (e.keyCode){
                case 27: // escape
                    if (this.focusDate){
                        this.focusDate = null;
                        this.viewDate = this.dates.get(-1) || this.viewDate;
                        this.fill();
                    }
                    else
                        this.hide();
                    e.preventDefault();
                    e.stopPropagation();
                    break;
                case 37: // left
                case 38: // up
                case 39: // right
                case 40: // down
                    if (!this.o.keyboardNavigation || this.o.daysOfWeekDisabled.length === 7)
                        break;
                    dir = e.keyCode === 37 || e.keyCode === 38 ? -1 : 1;
                    if (this.viewMode === 0) {
                        if (e.ctrlKey){
                            newViewDate = this.moveAvailableDate(focusDate, dir, 'moveYear');

                            if (newViewDate)
                                this._trigger('changeYear', this.viewDate);
                        } else if (e.shiftKey){
                            newViewDate = this.moveAvailableDate(focusDate, dir, 'moveMonth');

                            if (newViewDate)
                                this._trigger('changeMonth', this.viewDate);
                        } else if (e.keyCode === 37 || e.keyCode === 39){
                            newViewDate = this.moveAvailableDate(focusDate, dir, 'moveDay');
                        } else if (!this.weekOfDateIsDisabled(focusDate)){
                            newViewDate = this.moveAvailableDate(focusDate, dir, 'moveWeek');
                        }
                    } else if (this.viewMode === 1) {
                        if (e.keyCode === 38 || e.keyCode === 40) {
                            dir = dir * 4;
                        }
                        newViewDate = this.moveAvailableDate(focusDate, dir, 'moveMonth');
                    } else if (this.viewMode === 2) {
                        if (e.keyCode === 38 || e.keyCode === 40) {
                            dir = dir * 4;
                        }
                        newViewDate = this.moveAvailableDate(focusDate, dir, 'moveYear');
                    }
                    if (newViewDate){
                        this.focusDate = this.viewDate = newViewDate;
                        this.setValue();
                        this.fill();
                        e.preventDefault();
                    }
                    break;
                case 13: // enter
                    if (!this.o.forceParse)
                        break;
                    focusDate = this.focusDate || this.dates.get(-1) || this.viewDate;
                    if (this.o.keyboardNavigation) {
                        this._toggle_multidate(focusDate);
                        dateChanged = true;
                    }
                    this.focusDate = null;
                    this.viewDate = this.dates.get(-1) || this.viewDate;
                    this.setValue();
                    this.fill();
                    if (this.picker.is(':visible')){
                        e.preventDefault();
                        e.stopPropagation();
                        if (this.o.autoclose)
                            this.hide();
                    }
                    break;
                case 9: // tab
                    this.focusDate = null;
                    this.viewDate = this.dates.get(-1) || this.viewDate;
                    this.fill();
                    this.hide();
                    break;
            }
            if (dateChanged){
                if (this.dates.length)
                    this._trigger('changeDate');
                else
                    this._trigger('clearDate');
                this.inputField.trigger('change');
            }
        },

        setViewMode: function(viewMode){
            this.viewMode = viewMode;
            this.picker
                .children('div')
                .hide()
                .filter('.datepicker-' + DPGlobal.viewModes[this.viewMode].clsName)
                .show();
            this.updateNavArrows();
            this._trigger('changeViewMode', new Date(this.viewDate));
        }
    };

    var DateRangePicker = function(element, options){
        $.data(element, 'datepicker', this);
        this.element = $(element);
        this.inputs = $.map(options.inputs, function(i){
            return i.jquery ? i[0] : i;
        });
        delete options.inputs;

        this.keepEmptyValues = options.keepEmptyValues;
        delete options.keepEmptyValues;

        datepickerPlugin.call($(this.inputs), options)
            .on('changeDate', $.proxy(this.dateUpdated, this));

        this.pickers = $.map(this.inputs, function(i){
            return $.data(i, 'datepicker');
        });
        this.updateDates();
    };
    DateRangePicker.prototype = {
        updateDates: function(){
            this.dates = $.map(this.pickers, function(i){
                return i.getUTCDate();
            });
            this.updateRanges();
        },
        updateRanges: function(){
            var range = $.map(this.dates, function(d){
                return d.valueOf();
            });
            $.each(this.pickers, function(i, p){
                p.setRange(range);
            });
        },
        clearDates: function(){
            $.each(this.pickers, function(i, p){
                p.clearDates();
            });
        },
        dateUpdated: function(e){
            // `this.updating` is a workaround for preventing infinite recursion
            // between `changeDate` triggering and `setUTCDate` calling.  Until
            // there is a better mechanism.
            if (this.updating)
                return;
            this.updating = true;

            var dp = $.data(e.target, 'datepicker');

            if (dp === undefined) {
                return;
            }

            var new_date = dp.getUTCDate(),
                keep_empty_values = this.keepEmptyValues,
                i = $.inArray(e.target, this.inputs),
                j = i - 1,
                k = i + 1,
                l = this.inputs.length;
            if (i === -1)
                return;

            $.each(this.pickers, function(i, p){
                if (!p.getUTCDate() && (p === dp || !keep_empty_values))
                    p.setUTCDate(new_date);
            });

            if (new_date < this.dates[j]){
                // Date being moved earlier/left
                while (j >= 0 && new_date < this.dates[j] && (this.pickers[j].element.val() || "").length > 0) {
                    this.pickers[j--].setUTCDate(new_date);
                }
            } else if (new_date > this.dates[k]){
                // Date being moved later/right
                while (k < l && new_date > this.dates[k] && (this.pickers[k].element.val() || "").length > 0) {
                    this.pickers[k++].setUTCDate(new_date);
                }
            }
            this.updateDates();

            delete this.updating;
        },
        destroy: function(){
            $.map(this.pickers, function(p){ p.destroy(); });
            $(this.inputs).off('changeDate', this.dateUpdated);
            delete this.element.data().datepicker;
        },
        remove: alias('destroy', 'Method `remove` is deprecated and will be removed in version 2.0. Use `destroy` instead')
    };

    function opts_from_el(el, prefix){
        // Derive options from element data-attrs
        var data = $(el).data(),
            out = {}, inkey,
            replace = new RegExp('^' + prefix.toLowerCase() + '([A-Z])');
        prefix = new RegExp('^' + prefix.toLowerCase());
        function re_lower(_,a){
            return a.toLowerCase();
        }
        for (var key in data)
            if (prefix.test(key)){
                inkey = key.replace(replace, re_lower);
                out[inkey] = data[key];
            }
        return out;
    }

    function opts_from_locale(lang){
        // Derive options from locale plugins
        var out = {};
        // Check if "de-DE" style date is available, if not language should
        // fallback to 2 letter code eg "de"
        if (!dates[lang]){
            lang = lang.split('-')[0];
            if (!dates[lang])
                return;
        }
        var d = dates[lang];
        $.each(locale_opts, function(i,k){
            if (k in d)
                out[k] = d[k];
        });
        return out;
    }

    var old = $.fn.datepicker;
    var datepickerPlugin = function(option){
        var args = Array.apply(null, arguments);
        args.shift();
        var internal_return;
        this.each(function(){
            var $this = $(this),
                data = $this.data('datepicker'),
                options = typeof option === 'object' && option;
            if (!data){
                var elopts = opts_from_el(this, 'date'),
                    // Preliminary otions
                    xopts = $.extend({}, defaults, elopts, options),
                    locopts = opts_from_locale(xopts.language),
                    // Options priority: js args, data-attrs, locales, defaults
                    opts = $.extend({}, defaults, locopts, elopts, options);
                if ($this.hasClass('input-daterange') || opts.inputs){
                    $.extend(opts, {
                        inputs: opts.inputs || $this.find('input').toArray()
                    });
                    data = new DateRangePicker(this, opts);
                }
                else {
                    data = new Datepicker(this, opts);
                }
                $this.data('datepicker', data);
            }
            if (typeof option === 'string' && typeof data[option] === 'function'){
                internal_return = data[option].apply(data, args);
            }
        });

        if (
            internal_return === undefined ||
            internal_return instanceof Datepicker ||
            internal_return instanceof DateRangePicker
        )
            return this;

        if (this.length > 1)
            throw new Error('Using only allowed for the collection of a single element (' + option + ' function)');
        else
            return internal_return;
    };
    $.fn.datepicker = datepickerPlugin;

    var defaults = $.fn.datepicker.defaults = {
        assumeNearbyYear: false,
        autoclose: false,
        beforeShowDay: $.noop,
        beforeShowMonth: $.noop,
        beforeShowYear: $.noop,
        beforeShowDecade: $.noop,
        beforeShowCentury: $.noop,
        calendarWeeks: false,
        clearBtn: false,
        toggleActive: false,
        daysOfWeekDisabled: [],
        daysOfWeekHighlighted: [],
        datesDisabled: [],
        endDate: Infinity,
        forceParse: true,
        format: 'mm/dd/yyyy',
        isInline: null,
        keepEmptyValues: false,
        keyboardNavigation: true,
        language: 'en',
        minViewMode: 0,
        maxViewMode: 4,
        multidate: false,
        multidateSeparator: ',',
        orientation: "auto",
        rtl: false,
        startDate: -Infinity,
        startView: 0,
        todayBtn: false,
        todayHighlight: false,
        updateViewDate: true,
        weekStart: 0,
        disableTouchKeyboard: false,
        enableOnReadonly: true,
        showOnFocus: true,
        zIndexOffset: 10,
        container: 'body',
        immediateUpdates: false,
        title: '',
        templates: {
            leftArrow: '&#x00AB;',
            rightArrow: '&#x00BB;'
        },
        showWeekDays: true
    };
    var locale_opts = $.fn.datepicker.locale_opts = [
        'format',
        'rtl',
        'weekStart'
    ];
    $.fn.datepicker.Constructor = Datepicker;
    var dates = $.fn.datepicker.dates = {
        en: {
            days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
            daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            daysMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
            months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            today: "Today",
            clear: "Clear",
            titleFormat: "MM yyyy"
        }
    };

    var DPGlobal = {
        viewModes: [
            {
                names: ['days', 'month'],
                clsName: 'days',
                e: 'changeMonth'
            },
            {
                names: ['months', 'year'],
                clsName: 'months',
                e: 'changeYear',
                navStep: 1
            },
            {
                names: ['years', 'decade'],
                clsName: 'years',
                e: 'changeDecade',
                navStep: 10
            },
            {
                names: ['decades', 'century'],
                clsName: 'decades',
                e: 'changeCentury',
                navStep: 100
            },
            {
                names: ['centuries', 'millennium'],
                clsName: 'centuries',
                e: 'changeMillennium',
                navStep: 1000
            }
        ],
        validParts: /dd?|DD?|mm?|MM?|yy(?:yy)?/g,
        nonpunctuation: /[^ -\/:-@\u5e74\u6708\u65e5\[-`{-~\t\n\r]+/g,
        parseFormat: function(format){
            if (typeof format.toValue === 'function' && typeof format.toDisplay === 'function')
                return format;
            // IE treats \0 as a string end in inputs (truncating the value),
            // so it's a bad format delimiter, anyway
            var separators = format.replace(this.validParts, '\0').split('\0'),
                parts = format.match(this.validParts);
            if (!separators || !separators.length || !parts || parts.length === 0){
                throw new Error("Invalid date format.");
            }
            return {separators: separators, parts: parts};
        },
        parseDate: function(date, format, language, assumeNearby){
            if (!date)
                return undefined;
            if (date instanceof Date)
                return date;
            if (typeof format === 'string')
                format = DPGlobal.parseFormat(format);
            if (format.toValue)
                return format.toValue(date, format, language);
            var fn_map = {
                    d: 'moveDay',
                    m: 'moveMonth',
                    w: 'moveWeek',
                    y: 'moveYear'
                },
                dateAliases = {
                    yesterday: '-1d',
                    today: '+0d',
                    tomorrow: '+1d'
                },
                parts, part, dir, i, fn;
            if (date in dateAliases){
                date = dateAliases[date];
            }
            if (/^[\-+]\d+[dmwy]([\s,]+[\-+]\d+[dmwy])*$/i.test(date)){
                parts = date.match(/([\-+]\d+)([dmwy])/gi);
                date = new Date();
                for (i=0; i < parts.length; i++){
                    part = parts[i].match(/([\-+]\d+)([dmwy])/i);
                    dir = Number(part[1]);
                    fn = fn_map[part[2].toLowerCase()];
                    date = Datepicker.prototype[fn](date, dir);
                }
                return Datepicker.prototype._zero_utc_time(date);
            }

            parts = date && date.match(this.nonpunctuation) || [];

            function applyNearbyYear(year, threshold){
                if (threshold === true)
                    threshold = 10;

                // if year is 2 digits or less, than the user most likely is trying to get a recent century
                if (year < 100){
                    year += 2000;
                    // if the new year is more than threshold years in advance, use last century
                    if (year > ((new Date()).getFullYear()+threshold)){
                        year -= 100;
                    }
                }

                return year;
            }

            var parsed = {},
                setters_order = ['yyyy', 'yy', 'M', 'MM', 'm', 'mm', 'd', 'dd'],
                setters_map = {
                    yyyy: function(d,v){
                        return d.setUTCFullYear(assumeNearby ? applyNearbyYear(v, assumeNearby) : v);
                    },
                    m: function(d,v){
                        if (isNaN(d))
                            return d;
                        v -= 1;
                        while (v < 0) v += 12;
                        v %= 12;
                        d.setUTCMonth(v);
                        while (d.getUTCMonth() !== v)
                            d.setUTCDate(d.getUTCDate()-1);
                        return d;
                    },
                    d: function(d,v){
                        return d.setUTCDate(v);
                    }
                },
                val, filtered;
            setters_map['yy'] = setters_map['yyyy'];
            setters_map['M'] = setters_map['MM'] = setters_map['mm'] = setters_map['m'];
            setters_map['dd'] = setters_map['d'];
            date = UTCToday();
            var fparts = format.parts.slice();
            // Remove noop parts
            if (parts.length !== fparts.length){
                fparts = $(fparts).filter(function(i,p){
                    return $.inArray(p, setters_order) !== -1;
                }).toArray();
            }
            // Process remainder
            function match_part(){
                var m = this.slice(0, parts[i].length),
                    p = parts[i].slice(0, m.length);
                return m.toLowerCase() === p.toLowerCase();
            }
            if (parts.length === fparts.length){
                var cnt;
                for (i=0, cnt = fparts.length; i < cnt; i++){
                    val = parseInt(parts[i], 10);
                    part = fparts[i];
                    if (isNaN(val)){
                        switch (part){
                            case 'MM':
                                filtered = $(dates[language].months).filter(match_part);
                                val = $.inArray(filtered[0], dates[language].months) + 1;
                                break;
                            case 'M':
                                filtered = $(dates[language].monthsShort).filter(match_part);
                                val = $.inArray(filtered[0], dates[language].monthsShort) + 1;
                                break;
                        }
                    }
                    parsed[part] = val;
                }
                var _date, s;
                for (i=0; i < setters_order.length; i++){
                    s = setters_order[i];
                    if (s in parsed && !isNaN(parsed[s])){
                        _date = new Date(date);
                        setters_map[s](_date, parsed[s]);
                        if (!isNaN(_date))
                            date = _date;
                    }
                }
            }
            return date;
        },
        formatDate: function(date, format, language){
            if (!date)
                return '';
            if (typeof format === 'string')
                format = DPGlobal.parseFormat(format);
            if (format.toDisplay)
                return format.toDisplay(date, format, language);
            var val = {
                d: date.getUTCDate(),
                D: dates[language].daysShort[date.getUTCDay()],
                DD: dates[language].days[date.getUTCDay()],
                m: date.getUTCMonth() + 1,
                M: dates[language].monthsShort[date.getUTCMonth()],
                MM: dates[language].months[date.getUTCMonth()],
                yy: date.getUTCFullYear().toString().substring(2),
                yyyy: date.getUTCFullYear()
            };
            val.dd = (val.d < 10 ? '0' : '') + val.d;
            val.mm = (val.m < 10 ? '0' : '') + val.m;
            date = [];
            var seps = $.extend([], format.separators);
            for (var i=0, cnt = format.parts.length; i <= cnt; i++){
                if (seps.length)
                    date.push(seps.shift());
                date.push(val[format.parts[i]]);
            }
            return date.join('');
        },
        headTemplate: '<thead>'+
            '<tr>'+
            '<th colspan="7" class="datepicker-title"></th>'+
            '</tr>'+
            '<tr>'+
            '<th class="prev">'+defaults.templates.leftArrow+'</th>'+
            '<th colspan="5" class="datepicker-switch"></th>'+
            '<th class="next">'+defaults.templates.rightArrow+'</th>'+
            '</tr>'+
            '</thead>',
        contTemplate: '<tbody><tr><td colspan="7"></td></tr></tbody>',
        footTemplate: '<tfoot>'+
            '<tr>'+
            '<th colspan="7" class="today"></th>'+
            '</tr>'+
            '<tr>'+
            '<th colspan="7" class="clear"></th>'+
            '</tr>'+
            '</tfoot>'
    };
    DPGlobal.template = '<div class="datepicker">'+
        '<div class="datepicker-days">'+
        '<table class="table-condensed">'+
        DPGlobal.headTemplate+
        '<tbody></tbody>'+
        DPGlobal.footTemplate+
        '</table>'+
        '</div>'+
        '<div class="datepicker-months">'+
        '<table class="table-condensed">'+
        DPGlobal.headTemplate+
        DPGlobal.contTemplate+
        DPGlobal.footTemplate+
        '</table>'+
        '</div>'+
        '<div class="datepicker-years">'+
        '<table class="table-condensed">'+
        DPGlobal.headTemplate+
        DPGlobal.contTemplate+
        DPGlobal.footTemplate+
        '</table>'+
        '</div>'+
        '<div class="datepicker-decades">'+
        '<table class="table-condensed">'+
        DPGlobal.headTemplate+
        DPGlobal.contTemplate+
        DPGlobal.footTemplate+
        '</table>'+
        '</div>'+
        '<div class="datepicker-centuries">'+
        '<table class="table-condensed">'+
        DPGlobal.headTemplate+
        DPGlobal.contTemplate+
        DPGlobal.footTemplate+
        '</table>'+
        '</div>'+
        '</div>';

    $.fn.datepicker.DPGlobal = DPGlobal;


    /* DATEPICKER NO CONFLICT
    * =================== */

    $.fn.datepicker.noConflict = function(){
        $.fn.datepicker = old;
        return this;
    };

    /* DATEPICKER VERSION
     * =================== */
    $.fn.datepicker.version = '1.9.0';

    $.fn.datepicker.deprecated = function(msg){
        var console = window.console;
        if (console && console.warn) {
            console.warn('DEPRECATED: ' + msg);
        }
    };


    /* DATEPICKER DATA-API
    * ================== */

    $(document).on(
        'focus.datepicker.data-api click.datepicker.data-api',
        '[data-provide="datepicker"]',
        function(e){
            var $this = $(this);
            if ($this.data('datepicker'))
                return;
            e.preventDefault();
            // component click requires us to explicitly show it
            datepickerPlugin.call($this, 'show');
        }
    );
    $(function(){
        datepickerPlugin.call($('[data-provide="datepicker-inline"]'));
    });

}));
!function(t){var o=0;t.fn.scrolled=function(a,n){"function"==typeof a&&(n=a,a=300);var c="scrollTimer"+o++;this.scroll(function(){var o=t(this),e=o.data(c);e&&clearTimeout(e),e=setTimeout(function(){o.removeData(c),n.call(o[0])},a),o.data(c,e)})}}(jQuery);

/** Abstract base class for collection plugins.
 Written by Keith Wood (kbwood{at}iinet.com.au) December 2013.
 Licensed under the MIT (https://github.com/jquery/jquery/blob/master/MIT-LICENSE.txt) license. */
(function(){var j=false;window.JQClass=function(){};JQClass.classes={};JQClass.extend=function extender(f){var g=this.prototype;j=true;var h=new this();j=false;for(var i in f){h[i]=typeof f[i]=='function'&&typeof g[i]=='function'?(function(d,e){return function(){var b=this._super;this._super=function(a){return g[d].apply(this,a)};var c=e.apply(this,arguments);this._super=b;return c}})(i,f[i]):f[i]}function JQClass(){if(!j&&this._init){this._init.apply(this,arguments)}}JQClass.prototype=h;JQClass.prototype.constructor=JQClass;JQClass.extend=extender;return JQClass}})();(function($){JQClass.classes.JQPlugin=JQClass.extend({name:'plugin',defaultOptions:{},regionalOptions:{},_getters:[],_getMarker:function(){return'is-'+this.name},_init:function(){$.extend(this.defaultOptions,(this.regionalOptions&&this.regionalOptions[''])||{});var c=camelCase(this.name);$[c]=this;$.fn[c]=function(a){var b=Array.prototype.slice.call(arguments,1);if($[c]._isNotChained(a,b)){return $[c][a].apply($[c],[this[0]].concat(b))}return this.each(function(){if(typeof a==='string'){if(a[0]==='_'||!$[c][a]){throw'Unknown method: '+a;}$[c][a].apply($[c],[this].concat(b))}else{$[c]._attach(this,a)}})}},setDefaults:function(a){$.extend(this.defaultOptions,a||{})},_isNotChained:function(a,b){if(a==='option'&&(b.length===0||(b.length===1&&typeof b[0]==='string'))){return true}return $.inArray(a,this._getters)>-1},_attach:function(a,b){a=$(a);if(a.hasClass(this._getMarker())){return}a.addClass(this._getMarker());b=$.extend({},this.defaultOptions,this._getMetadata(a),b||{});var c=$.extend({name:this.name,elem:a,options:b},this._instSettings(a,b));a.data(this.name,c);this._postAttach(a,c);this.option(a,b)},_instSettings:function(a,b){return{}},_postAttach:function(a,b){},_getMetadata:function(d){try{var f=d.data(this.name.toLowerCase())||'';f=f.replace(/'/g,'"');f=f.replace(/([a-zA-Z0-9]+):/g,function(a,b,i){var c=f.substring(0,i).match(/"/g);return(!c||c.length%2===0?'"'+b+'":':b+':')});f=$.parseJSON('{'+f+'}');for(var g in f){var h=f[g];if(typeof h==='string'&&h.match(/^new Date\((.*)\)$/)){f[g]=eval(h)}}return f}catch(e){return{}}},_getInst:function(a){return $(a).data(this.name)||{}},option:function(a,b,c){a=$(a);var d=a.data(this.name);if(!b||(typeof b==='string'&&c==null)){var e=(d||{}).options;return(e&&b?e[b]:e)}if(!a.hasClass(this._getMarker())){return}var e=b||{};if(typeof b==='string'){e={};e[b]=c}this._optionsChanged(a,d,e);$.extend(d.options,e)},_optionsChanged:function(a,b,c){},destroy:function(a){a=$(a);if(!a.hasClass(this._getMarker())){return}this._preDestroy(a,this._getInst(a));a.removeData(this.name).removeClass(this._getMarker())},_preDestroy:function(a,b){}});function camelCase(c){return c.replace(/-([a-z])/g,function(a,b){return b.toUpperCase()})}$.JQPlugin={createPlugin:function(a,b){if(typeof a==='object'){b=a;a='JQPlugin'}a=camelCase(a);var c=camelCase(b.name);JQClass.classes[c]=JQClass.classes[a].extend(b);new JQClass.classes[c]()}}})(jQuery);

/*!
* FitVids 1.1
*
* Copyright 2013, Chris Coyier - http://css-tricks.com + Dave Rupert - http://daverupert.com
* Credit to Thierry Koblentz - http://www.alistapart.com/articles/creating-intrinsic-ratios-for-video/
* Released under the WTFPL license - http://sam.zoy.org/wtfpl/
*
*/
!function(t){"use strict";t.fn.fitVids=function(e){var i={customSelector:null,ignore:null};if(!document.getElementById("fit-vids-style")){var r=document.head||document.getElementsByTagName("head")[0],a=".fluid-width-video-wrapper{width:100%;position:relative;padding:0;}.fluid-width-video-wrapper iframe,.fluid-width-video-wrapper object,.fluid-width-video-wrapper embed {position:absolute;top:0;left:0;width:100%;height:100%;}",d=document.createElement("div");d.innerHTML='<p>x</p><style id="fit-vids-style">'+a+"</style>",r.appendChild(d.childNodes[1])}return e&&t.extend(i,e),this.each(function(){var e=['iframe[src*="player.vimeo.com"]','iframe[src*="youtube.com"]','iframe[src*="youtube-nocookie.com"]','iframe[src*="kickstarter.com"][src*="video.html"]',"object","embed"];i.customSelector&&e.push(i.customSelector);var r=".fitvidsignore";i.ignore&&(r=r+", "+i.ignore);var a=t(this).find(e.join(","));a=a.not("object object"),a=a.not(r),a.each(function(e){var i=t(this);if(!(i.parents(r).length>0||"embed"===this.tagName.toLowerCase()&&i.parent("object").length||i.parent(".fluid-width-video-wrapper").length)){i.css("height")||i.css("width")||!isNaN(i.attr("height"))&&!isNaN(i.attr("width"))||(i.attr("height",9),i.attr("width",16));var a="object"===this.tagName.toLowerCase()||i.attr("height")&&!isNaN(parseInt(i.attr("height"),10))?parseInt(i.attr("height"),10):i.height(),d=isNaN(parseInt(i.attr("width"),10))?i.width():parseInt(i.attr("width"),10),o=a/d;if(!i.attr("id")){var h="fitvid"+e;i.attr("id",h)}i.wrap('<div class="fluid-width-video-wrapper"></div>').parent(".fluid-width-video-wrapper").css("padding-top",100*o+"%"),i.removeAttr("height").removeAttr("width")}})})}}(window.jQuery||window.Zepto);

/*
 * jQuery Superfish Menu Plugin - v1.7.10
 * Copyright (c) 2018 Joel Birch
 *
 * Dual licensed under the MIT and GPL licenses:
 *	http://www.opensource.org/licenses/mit-license.php
 *	http://www.gnu.org/licenses/gpl.html
 */
;!function(a,b){"use strict";var c=function(){var c={bcClass:"sf-breadcrumb",menuClass:"sf-js-enabled",anchorClass:"sf-with-ul",menuArrowClass:"sf-arrows"},d=function(){var b=/^(?![\w\W]*Windows Phone)[\w\W]*(iPhone|iPad|iPod)/i.test(navigator.userAgent);return b&&a("html").css("cursor","pointer").on("click",a.noop),b}(),e=function(){var a=document.documentElement.style;return"behavior"in a&&"fill"in a&&/iemobile/i.test(navigator.userAgent)}(),f=function(){return!!b.PointerEvent}(),g=function(a,b,d){var e,f=c.menuClass;b.cssArrows&&(f+=" "+c.menuArrowClass),e=d?"addClass":"removeClass",a[e](f)},h=function(b,d){return b.find("li."+d.pathClass).slice(0,d.pathLevels).addClass(d.hoverClass+" "+c.bcClass).filter(function(){return a(this).children(d.popUpSelector).hide().show().length}).removeClass(d.pathClass)},i=function(a,b){var d=b?"addClass":"removeClass";a.children("a")[d](c.anchorClass)},j=function(a){var b=a.css("ms-touch-action"),c=a.css("touch-action");c=c||b,c="pan-y"===c?"auto":"pan-y",a.css({"ms-touch-action":c,"touch-action":c})},k=function(a){return a.closest("."+c.menuClass)},l=function(a){return k(a).data("sfOptions")},m=function(){var b=a(this),c=l(b);clearTimeout(c.sfTimer),b.siblings().superfish("hide").end().superfish("show")},n=function(b){b.retainPath=a.inArray(this[0],b.$path)>-1,this.superfish("hide"),this.parents("."+b.hoverClass).length||(b.onIdle.call(k(this)),b.$path.length&&a.proxy(m,b.$path)())},o=function(){var b=a(this),c=l(b);d?a.proxy(n,b,c)():(clearTimeout(c.sfTimer),c.sfTimer=setTimeout(a.proxy(n,b,c),c.delay))},p=function(b){var c=a(this),d=l(c),e=c.siblings(b.data.popUpSelector);return d.onHandleTouch.call(e)===!1?this:void(e.length>0&&e.is(":hidden")&&(c.one("click.superfish",!1),"MSPointerDown"===b.type||"pointerdown"===b.type?c.trigger("focus"):a.proxy(m,c.parent("li"))()))},q=function(b,c){var g="li:has("+c.popUpSelector+")";a.fn.hoverIntent&&!c.disableHI?b.hoverIntent(m,o,g):b.on("mouseenter.superfish",g,m).on("mouseleave.superfish",g,o);var h="MSPointerDown.superfish";f&&(h="pointerdown.superfish"),d||(h+=" touchend.superfish"),e&&(h+=" mousedown.superfish"),b.on("focusin.superfish","li",m).on("focusout.superfish","li",o).on(h,"a",c,p)};return{hide:function(b){if(this.length){var c=this,d=l(c);if(!d)return this;var e=d.retainPath===!0?d.$path:"",f=c.find("li."+d.hoverClass).add(this).not(e).removeClass(d.hoverClass).children(d.popUpSelector),g=d.speedOut;if(b&&(f.show(),g=0),d.retainPath=!1,d.onBeforeHide.call(f)===!1)return this;f.stop(!0,!0).animate(d.animationOut,g,function(){var b=a(this);d.onHide.call(b)})}return this},show:function(){var a=l(this);if(!a)return this;var b=this.addClass(a.hoverClass),c=b.children(a.popUpSelector);return a.onBeforeShow.call(c)===!1?this:(c.stop(!0,!0).animate(a.animation,a.speed,function(){a.onShow.call(c)}),this)},destroy:function(){return this.each(function(){var b,d=a(this),e=d.data("sfOptions");return!!e&&(b=d.find(e.popUpSelector).parent("li"),clearTimeout(e.sfTimer),g(d,e),i(b),j(d),d.off(".superfish").off(".hoverIntent"),b.children(e.popUpSelector).attr("style",function(a,b){if("undefined"!=typeof b)return b.replace(/display[^;]+;?/g,"")}),e.$path.removeClass(e.hoverClass+" "+c.bcClass).addClass(e.pathClass),d.find("."+e.hoverClass).removeClass(e.hoverClass),e.onDestroy.call(d),void d.removeData("sfOptions"))})},init:function(b){return this.each(function(){var d=a(this);if(d.data("sfOptions"))return!1;var e=a.extend({},a.fn.superfish.defaults,b),f=d.find(e.popUpSelector).parent("li");e.$path=h(d,e),d.data("sfOptions",e),g(d,e,!0),i(f,!0),j(d),q(d,e),f.not("."+c.bcClass).superfish("hide",!0),e.onInit.call(this)})}}}();a.fn.superfish=function(b,d){return c[b]?c[b].apply(this,Array.prototype.slice.call(arguments,1)):"object"!=typeof b&&b?a.error("Method "+b+" does not exist on jQuery.fn.superfish"):c.init.apply(this,arguments)},a.fn.superfish.defaults={popUpSelector:"ul,.sf-mega",hoverClass:"sfHover",pathClass:"overrideThisToUse",pathLevels:1,delay:800,animation:{opacity:"show"},animationOut:{opacity:"hide"},speed:"normal",speedOut:"fast",cssArrows:!0,disableHI:!1,onInit:a.noop,onBeforeShow:a.noop,onShow:a.noop,onBeforeHide:a.noop,onHide:a.noop,onIdle:a.noop,onDestroy:a.noop,onHandleTouch:a.noop}}(jQuery,window);

/*!
 * hoverIntent v1.9.0 // 2017.09.01 // jQuery v1.7.0+
 * http://briancherne.github.io/jquery-hoverIntent/
 *
 * You may use hoverIntent under the terms of the MIT license. Basically that
 * means you are free to use hoverIntent as long as this header is left intact.
 * Copyright 2007-2017 Brian Cherne
 */
!function(factory){"use strict";"function"==typeof define&&define.amd?define(["jquery"],factory):jQuery&&!jQuery.fn.hoverIntent&&factory(jQuery)}(function($){"use strict";var cX,cY,_cfg={interval:100,sensitivity:6,timeout:0},INSTANCE_COUNT=0,track=function(ev){cX=ev.pageX,cY=ev.pageY},compare=function(ev,$el,s,cfg){if(Math.sqrt((s.pX-cX)*(s.pX-cX)+(s.pY-cY)*(s.pY-cY))<cfg.sensitivity)return $el.off(s.event,track),delete s.timeoutId,s.isActive=!0,ev.pageX=cX,ev.pageY=cY,delete s.pX,delete s.pY,cfg.over.apply($el[0],[ev]);s.pX=cX,s.pY=cY,s.timeoutId=setTimeout(function(){compare(ev,$el,s,cfg)},cfg.interval)},delay=function(ev,$el,s,out){return delete $el.data("hoverIntent")[s.id],out.apply($el[0],[ev])};$.fn.hoverIntent=function(handlerIn,handlerOut,selector){var instanceId=INSTANCE_COUNT++,cfg=$.extend({},_cfg);$.isPlainObject(handlerIn)?(cfg=$.extend(cfg,handlerIn),$.isFunction(cfg.out)||(cfg.out=cfg.over)):cfg=$.isFunction(handlerOut)?$.extend(cfg,{over:handlerIn,out:handlerOut,selector:selector}):$.extend(cfg,{over:handlerIn,out:handlerIn,selector:handlerOut});var handleHover=function(e){var ev=$.extend({},e),$el=$(this),hoverIntentData=$el.data("hoverIntent");hoverIntentData||$el.data("hoverIntent",hoverIntentData={});var state=hoverIntentData[instanceId];state||(hoverIntentData[instanceId]=state={id:instanceId}),state.timeoutId&&(state.timeoutId=clearTimeout(state.timeoutId));var mousemove=state.event="mousemove.hoverIntent.hoverIntent"+instanceId;if("mouseenter"===e.type){if(state.isActive)return;state.pX=ev.pageX,state.pY=ev.pageY,$el.off(mousemove,track).on(mousemove,track),state.timeoutId=setTimeout(function(){compare(ev,$el,state,cfg)},cfg.interval)}else{if(!state.isActive)return;$el.off(mousemove,track),state.timeoutId=setTimeout(function(){delay(ev,$el,state,cfg.out)},cfg.timeout)}};return this.on({"mouseenter.hoverIntent":handleHover,"mouseleave.hoverIntent":handleHover},cfg.selector)}});

/*! jRespond.js v 0.10 | Author: Jeremy Fields [jeremy.fields@viget.com], 2013 | License: MIT */
!function(a,b,c){"object"==typeof module&&module&&"object"==typeof module.exports?module.exports=c:(a[b]=c,"function"==typeof define&&define.amd&&define(b,[],function(){return c}))}(this,"jRespond",function(a,b,c){"use strict";return function(a){var b=[],d=[],e=a,f="",g="",i=0,j=100,k=500,l=k,m=function(){var a=0;return a="number"!=typeof window.innerWidth?0!==document.documentElement.clientWidth?document.documentElement.clientWidth:document.body.clientWidth:window.innerWidth},n=function(a){if(a.length===c)o(a);else for(var b=0;b<a.length;b++)o(a[b])},o=function(a){var e=a.breakpoint,h=a.enter||c;b.push(a),d.push(!1),r(e)&&(h!==c&&h.call(null,{entering:f,exiting:g}),d[b.length-1]=!0)},p=function(){for(var a=[],e=[],h=0;h<b.length;h++){var i=b[h].breakpoint,j=b[h].enter||c,k=b[h].exit||c;"*"===i?(j!==c&&a.push(j),k!==c&&e.push(k)):r(i)?(j===c||d[h]||a.push(j),d[h]=!0):(k!==c&&d[h]&&e.push(k),d[h]=!1)}for(var l={entering:f,exiting:g},m=0;m<e.length;m++)e[m].call(null,l);for(var n=0;n<a.length;n++)a[n].call(null,l)},q=function(a){for(var b=!1,c=0;c<e.length;c++)if(a>=e[c].enter&&a<=e[c].exit){b=!0;break}b&&f!==e[c].label?(g=f,f=e[c].label,p()):b||""===f||(f="",p())},r=function(a){if("object"==typeof a){if(a.join().indexOf(f)>=0)return!0}else{if("*"===a)return!0;if("string"==typeof a&&f===a)return!0}},s=function(){var a=m();a!==i?(l=j,q(a)):l=k,i=a,setTimeout(s,l)};return s(),{addFunc:function(a){n(a)},getBreakpoint:function(){return f}}}}(this,this.document));

/*jquery.mb.YTPlayer 01-12-2018
 _ jquery.mb.components
 _ email: matbicoc@gmail.com
 _ Copyright (c) 2001-2018. Matteo Bicocchi (Pupunzi);
 _ blog: http://pupunzi.open-lab.com
 _ Open Lab s.r.l., Florence - Italy
 */
var ytp=ytp||{};function onYouTubeIframeAPIReady(){ytp.YTAPIReady||(ytp.YTAPIReady=!0,jQuery(document).trigger("YTAPIReady"))}var getYTPVideoID=function(e){var r,t;return 0<e.indexOf("youtu.be")||0<e.indexOf("youtube.com/embed")?r=(t=0<(r=e.substr(e.lastIndexOf("/")+1,e.length)).indexOf("?list=")?r.substr(r.lastIndexOf("="),r.length):null)?r.substr(0,r.lastIndexOf("?")):r:t=-1<e.indexOf("http")?(r=e.match(/[\\?&]v=([^&#]*)/)[1],0<e.indexOf("list=")?e.match(/[\\?&]list=([^&#]*)/)[1]:null):(r=15<e.length?null:e)?null:e,{videoID:r,playlistID:t}};function iOSversion(){if(/iP(hone|od|ad)/.test(navigator.platform)){var e=navigator.appVersion.match(/OS (\d+)_(\d+)_?(\d+)?/);return[parseInt(e[1],10),parseInt(e[2],10),parseInt(e[3]||0,10)]}}!function(jQuery,ytp){jQuery.mbYTPlayer={name:"jquery.mb.YTPlayer",version:"3.2.8",build:"7398",author:"Matteo Bicocchi (pupunzi)",apiKey:"",defaults:{videoURL:null,containment:"body",ratio:"auto",fadeOnStartTime:1500,startAt:0,stopAt:0,autoPlay:!0,coverImage:!1,loop:!0,addRaster:!1,mask:!1,opacity:1,quality:"default",vol:50,mute:!1,showControls:!0,anchor:"center,center",showAnnotations:!1,cc_load_policy:!1,showYTLogo:!0,useOnMobile:!0,mobileFallbackImage:null,playOnlyIfVisible:!1,onScreenPercentage:30,stopMovieOnBlur:!0,realfullscreen:!0,optimizeDisplay:!0,abundance:.3,gaTrack:!0,remember_last_time:!1,addFilters:!1,onReady:function(e){},onError:function(e,r){}},controls:{play:"P",pause:"p",mute:"M",unmute:"A",onlyYT:"O",showSite:"R",ytLogo:"Y"},controlBar:null,locationProtocol:"https:",defaultFilters:{grayscale:{value:0,unit:"%"},hue_rotate:{value:0,unit:"deg"},invert:{value:0,unit:"%"},opacity:{value:0,unit:"%"},saturate:{value:0,unit:"%"},sepia:{value:0,unit:"%"},brightness:{value:0,unit:"%"},contrast:{value:0,unit:"%"},blur:{value:0,unit:"px"}},buildPlayer:function(options){if(ytp.YTAPIReady||void 0!==window.YT)setTimeout(function(){jQuery(document).trigger("YTAPIReady"),ytp.YTAPIReady=!0},100);else{jQuery("#YTAPI").remove();var tag=jQuery("<script><\/script>").attr({src:jQuery.mbYTPlayer.locationProtocol+"//www.youtube.com/iframe_api?v="+jQuery.mbYTPlayer.version,id:"YTAPI"});jQuery("head").prepend(tag)}function isIframe(){var r=!1;try{self.location.href!=top.location.href&&(r=!0)}catch(e){r=!0}return r}return this.each(function(){var YTPlayer=this,$YTPlayer=jQuery(YTPlayer);$YTPlayer.hide(),YTPlayer.loop=0,YTPlayer.state=0,YTPlayer.filters=jQuery.extend(!0,{},jQuery.mbYTPlayer.defaultFilters),YTPlayer.filtersEnabled=!0,YTPlayer.id=YTPlayer.id||"YTP_"+(new Date).getTime(),$YTPlayer.addClass("mb_YTPlayer");var property=$YTPlayer.data("property")&&"string"==typeof $YTPlayer.data("property")?eval("("+$YTPlayer.data("property")+")"):$YTPlayer.data("property");"object"!=typeof property&&(property={}),YTPlayer.opt=jQuery.extend(!0,{},jQuery.mbYTPlayer.defaults,YTPlayer.opt,options,property),YTPlayer.opt.elementId=YTPlayer.id,0===YTPlayer.opt.vol&&(YTPlayer.opt.vol=1,YTPlayer.opt.mute=!0),YTPlayer.opt.autoPlay&&0==YTPlayer.opt.mute&&jQuery.mbBrowser.chrome&&(jQuery(document).one("mousedown.YTPstart",function(){$YTPlayer.YTPPlay()}),console.info("YTPlayer info: On Webkit browsers you can not autoplay the video if the audio is on.")),YTPlayer.opt.loop&&"boolean"==typeof YTPlayer.opt.loop&&(YTPlayer.opt.loop=9999);var fullScreenAvailable=document.fullscreenEnabled||document.webkitFullscreenEnabled||document.mozFullScreenEnabled||document.msFullscreenEnabled;YTPlayer.opt.realfullscreen=!(isIframe()||!fullScreenAvailable)&&YTPlayer.opt.realfullscreen,YTPlayer.opt.showAnnotations=YTPlayer.opt.showAnnotations?"1":"3",YTPlayer.opt.cc_load_policy=YTPlayer.opt.cc_load_policy?"1":"0",YTPlayer.opt.coverImage=YTPlayer.opt.coverImage||YTPlayer.opt.backgroundImage,jQuery.mbBrowser.msie&&jQuery.mbBrowser.version<9&&(YTPlayer.opt.opacity=1),YTPlayer.opt.containment="self"===YTPlayer.opt.containment?$YTPlayer:jQuery(YTPlayer.opt.containment),YTPlayer.isRetina=window.retina||1<window.devicePixelRatio,YTPlayer.opt.ratio="auto"===YTPlayer.opt.ratio?16/9:YTPlayer.opt.ratio,YTPlayer.opt.ratio=eval(YTPlayer.opt.ratio),YTPlayer.orig_containment_background=YTPlayer.opt.containment.css("background-image"),$YTPlayer.attr("id")||$YTPlayer.attr("id","ytp_"+(new Date).getTime()),YTPlayer.playerID="iframe_"+YTPlayer.id,YTPlayer.isAlone=!1,YTPlayer.hasFocus=!0,YTPlayer.videoID=YTPlayer.opt.videoURL?getYTPVideoID(YTPlayer.opt.videoURL).videoID:!!$YTPlayer.attr("href")&&getYTPVideoID($YTPlayer.attr("href")).videoID,YTPlayer.playlistID=YTPlayer.opt.videoURL?getYTPVideoID(YTPlayer.opt.videoURL).playlistID:!!$YTPlayer.attr("href")&&getYTPVideoID($YTPlayer.attr("href")).playlistID;var start_from_last=0;if(jQuery.mbCookie.get("YTPlayer_start_from"+YTPlayer.videoID)&&(start_from_last=parseFloat(jQuery.mbCookie.get("YTPlayer_start_from"+YTPlayer.videoID))),YTPlayer.opt.remember_last_time&&start_from_last&&(YTPlayer.start_from_last=start_from_last,jQuery.mbCookie.remove("YTPlayer_start_from"+YTPlayer.videoID)),YTPlayer.isPlayer=$YTPlayer.is(YTPlayer.opt.containment),YTPlayer.isBackground=YTPlayer.opt.containment.is("body"),!YTPlayer.isBackground||!ytp.backgroundIsInited){if(YTPlayer.isPlayer&&$YTPlayer.show(),YTPlayer.overlay=jQuery("<div/>").css({position:"absolute",top:0,left:0,width:"100%",height:"100%"}).addClass("YTPOverlay"),YTPlayer.opt.coverImage||"none"!=YTPlayer.orig_containment_background){var bgndURL=YTPlayer.opt.coverImage?"url("+YTPlayer.opt.coverImage+") center center":YTPlayer.orig_containment_background;YTPlayer.opt.containment.css({background:bgndURL,backgroundColor:"#000",backgroundSize:"cover",backgroundRepeat:"no-repeat"})}YTPlayer.wrapper=jQuery("<div/>").attr("id","wrapper_"+YTPlayer.id).css({position:"absolute",zIndex:0,minWidth:"100%",minHeight:"100%",left:0,top:0,overflow:"hidden",opacity:0}).addClass("mbYTP_wrapper"),YTPlayer.isPlayer&&(YTPlayer.inlinePlayButton=jQuery("<div/>").addClass("inlinePlayButton").html(jQuery.mbYTPlayer.controls.play),$YTPlayer.append(YTPlayer.inlinePlayButton),YTPlayer.inlinePlayButton.on("click",function(e){$YTPlayer.YTPPlay(),e.stopPropagation()}),YTPlayer.opt.autoPlay&&YTPlayer.inlinePlayButton.hide(),YTPlayer.overlay.on("click",function(){$YTPlayer.YTPTogglePlay()}).css({cursor:"pointer"}));var playerBox=jQuery("<div/>").attr("id",YTPlayer.playerID).addClass("playerBox");if(playerBox.css({position:"absolute",zIndex:0,width:"100%",height:"100%",top:0,left:0,overflow:"hidden",opacity:1}),YTPlayer.wrapper.append(playerBox),playerBox.after(YTPlayer.overlay),YTPlayer.isPlayer&&(YTPlayer.inlineWrapper=jQuery("<div/>").addClass("inline-YTPlayer"),YTPlayer.inlineWrapper.css({position:"relative",maxWidth:YTPlayer.opt.containment.css("width")}),YTPlayer.opt.containment.css({position:"relative",paddingBottom:"56.25%",overflow:"hidden",height:0}),YTPlayer.opt.containment.wrap(YTPlayer.inlineWrapper)),YTPlayer.opt.containment.children().not("script, style").each(function(){"static"==jQuery(this).css("position")&&jQuery(this).css("position","relative")}),YTPlayer.isBackground?(jQuery("body").css({boxSizing:"border-box"}),YTPlayer.wrapper.css({position:"fixed",top:0,left:0,zIndex:0})):"static"==YTPlayer.opt.containment.css("position")&&(YTPlayer.opt.containment.css({position:"relative"}),$YTPlayer.show()),YTPlayer.opt.containment.prepend(YTPlayer.wrapper),YTPlayer.isBackground||YTPlayer.overlay.on("mouseenter",function(){YTPlayer.controlBar&&YTPlayer.controlBar.length&&YTPlayer.controlBar.addClass("visible")}).on("mouseleave",function(){YTPlayer.controlBar&&YTPlayer.controlBar.length&&YTPlayer.controlBar.removeClass("visible")}),jQuery.mbBrowser.mobile&&!YTPlayer.opt.useOnMobile)return YTPlayer.opt.mobileFallbackImage&&(YTPlayer.wrapper.css({backgroundImage:"url("+YTPlayer.opt.mobileFallbackImage+")",backgroundPosition:"center center",backgroundSize:"cover",backgroundRepeat:"no-repeat",opacity:1}),YTPlayer.wrapper.css({opacity:1})),$YTPlayer;jQuery.mbBrowser.mobile&&YTPlayer.opt.autoPlay&&YTPlayer.opt.useOnMobile&&jQuery("body").one("touchstart",function(){YTPlayer.player.playVideo()}),jQuery(document).one("YTAPIReady",function(){$YTPlayer.trigger("YTAPIReady_"+YTPlayer.id),ytp.YTAPIReady=!0}),YTPlayer.isOnScreen=jQuery.mbYTPlayer.isOnScreen(YTPlayer,YTPlayer.opt.onScreenPercentage),$YTPlayer.one("YTAPIReady_"+YTPlayer.id,function(){var o=this,t=jQuery(o);o.isBackground&&ytp.backgroundIsInited||o.isInit||(o.isBackground&&(ytp.backgroundIsInited=!0),o.opt.autoPlay=void 0===o.opt.autoPlay?!!o.isBackground:o.opt.autoPlay,o.opt.vol=o.opt.vol?o.opt.vol:100,jQuery.mbYTPlayer.getDataFromAPI(o),jQuery(o).on("YTPChanged",function(e){if(!o.isInit){o.isInit=!0;var r={modestbranding:1,autoplay:0,controls:0,showinfo:0,rel:0,enablejsapi:1,version:3,playerapiid:o.playerID,origin:"*",allowfullscreen:!0,wmode:"transparent",iv_load_policy:o.opt.showAnnotations,cc_load_policy:o.opt.cc_load_policy,playsinline:jQuery.mbBrowser.mobile?1:0,html5:document.createElement("video").canPlayType?1:0};new YT.Player(o.playerID,{playerVars:r,events:{onReady:function(e){o.player=e.target,o.player.loadVideoById({videoId:o.videoID.toString(),suggestedQuality:o.opt.quality}),t.trigger("YTPlayerIsReady_"+o.id)},onStateChange:function(e){if("function"==typeof e.target.getPlayerState){var r=e.target.getPlayerState();if(o.preventTrigger||o.isStarting)o.preventTrigger=!1;else{var t;switch(o.state=r){case-1:t="YTPUnstarted";break;case 0:t="YTPRealEnd";break;case 1:t="YTPPlay",o.controlBar.length&&o.controlBar.find(".mb_YTPPlayPause").html(jQuery.mbYTPlayer.controls.pause),o.isPlayer&&o.inlinePlayButton.hide(),jQuery(document).off("mousedown.YTPstart");break;case 2:t="YTPPause",o.controlBar.length&&o.controlBar.find(".mb_YTPPlayPause").html(jQuery.mbYTPlayer.controls.play),o.isPlayer&&o.inlinePlayButton.show();break;case 3:o.player.setPlaybackQuality(o.opt.quality),t="YTPBuffering",o.controlBar.length&&o.controlBar.find(".mb_YTPPlayPause").html(jQuery.mbYTPlayer.controls.play);break;case 5:t="YTPCued"}var a=jQuery.Event(t);a.time=o.currentTime,jQuery(o).trigger(a)}}},onPlaybackQualityChange:function(e){var r=e.target.getPlaybackQuality(),t=jQuery.Event("YTPQualityChange");t.quality=r,jQuery(o).trigger(t)},onError:function(e){switch("function"==typeof o.opt.onError&&o.opt.onError(t,e),e.data){case 2:console.error("video ID:: "+o.videoID+": The request contains an invalid parameter value. For example, this error occurs if you specify a video ID that does not have 11 characters, or if the video ID contains invalid characters, such as exclamation points or asterisks.");break;case 5:console.error("video ID:: "+o.videoID+": The requested content cannot be played in an HTML5 player or another error related to the HTML5 player has occurred.");break;case 100:console.error("video ID:: "+o.videoID+": The video requested was not found. This error occurs when a video has been removed (for any reason) or has been marked as private.");break;case 101:case 150:console.error("video ID:: "+o.videoID+": The owner of the requested video does not allow it to be played in embedded players.")}o.isList&&jQuery(o).YTPPlayNext()}}}),t.on("YTPlayerIsReady_"+o.id,function(){if(o.isReady)return this;o.playerEl=o.player.getIframe(),jQuery(o.playerEl).unselectable(),t.optimizeDisplay(),jQuery(window).off("resize.YTP_"+o.id).on("resize.YTP_"+o.id,function(){t.optimizeDisplay()}),o.opt.remember_last_time&&jQuery(window).on("unload.YTP_"+o.id,function(){var e=o.player.getCurrentTime();jQuery.mbCookie.set("YTPlayer_start_from"+o.videoID,e,0)}),t.YTPCheckForState()})}}))}),$YTPlayer.off("YTPTime.mask"),jQuery.mbYTPlayer.applyMask(YTPlayer)}})},isOnScreen:function(e,r){r=r||10;var t=e.wrapper,a=jQuery(window).scrollTop(),o=a+jQuery(window).height(),n=t.height()*r/100,i=t.offset().top+n;return t.offset().top+(t.height()-n)<=o&&a<=i},getDataFromAPI:function(n){n.videoData=jQuery.mbStorage.get("YTPlayer_data_"+n.videoID),n.videoData?(setTimeout(function(){n.dataReceived=!0;var e=jQuery.Event("YTPChanged");e.time=n.currentTime,e.videoId=n.videoID,e.opt=n.opt,jQuery(n).trigger(e);var r=jQuery.Event("YTPData");for(var t in r.prop={},n.videoData)r.prop[t]=n.videoData[t];jQuery(n).trigger(r)},n.opt.fadeOnStartTime),n.hasData=!0):jQuery.mbYTPlayer.apiKey?jQuery.getJSON(jQuery.mbYTPlayer.locationProtocol+"//www.googleapis.com/youtube/v3/videos?id="+n.videoID+"&key="+jQuery.mbYTPlayer.apiKey+"&part=snippet",function(e){n.dataReceived=!0;var r,t=jQuery.Event("YTPChanged");t.time=n.currentTime,t.videoId=n.videoID,jQuery(n).trigger(t),e.items[0]?(r=e.items[0].snippet,n.videoData={},n.videoData.id=n.videoID,n.videoData.channelTitle=r.channelTitle,n.videoData.title=r.title,n.videoData.description=r.description.length<400?r.description:r.description.substring(0,400)+" ...",n.videoData.thumb_max=r.thumbnails.maxres?r.thumbnails.maxres.url:null,n.videoData.thumb_high=r.thumbnails.high?r.thumbnails.high.url:null,n.videoData.thumb_medium=r.thumbnails.medium?r.thumbnails.medium.url:null,jQuery.mbStorage.set("YTPlayer_data_"+n.videoID,n.videoData),n.hasData=!0):(n.videoData={},n.hasData=!1);var a=jQuery.Event("YTPData");for(var o in a.prop={},n.videoData)a.prop[o]=n.videoData[o];jQuery(n).trigger(a)}):(setTimeout(function(){var e=jQuery.Event("YTPChanged");e.time=n.currentTime,e.videoId=n.videoID,jQuery(n).trigger(e)},10),n.videoData=null),n.opt.ratio="auto"==n.opt.ratio?16/9:n.opt.ratio,n.isPlayer&&!n.opt.autoPlay&&(n.loading=jQuery("<div/>").addClass("loading").html("Loading").hide(),jQuery(n).append(n.loading),n.loading.fadeIn())},removeStoredData:function(){jQuery.mbStorage.remove()},getVideoData:function(){return this.get(0).videoData},getVideoID:function(){return this.get(0).videoID||!1},getPlaylistID:function(){return this.get(0).playlistID||!1},setVideoQuality:function(e){return this.get(0).player.setPlaybackQuality(e),this},playlist:function(e,r,t){var a=this.get(0);return a.isList=!0,r&&(e=jQuery.shuffle(e)),a.videoID||(a.videos=e,a.videoCounter=1,a.videoLength=e.length,jQuery(a).data("property",e[0]),jQuery(a).YTPlayer()),"function"==typeof t&&jQuery(a).one("YTPChanged",function(){t(a)}),jQuery(a).on("YTPEnd",function(){jQuery(a).YTPPlayNext()}),this},playNext:function(){var e=this.get(0);return e.videoCounter++,e.videoCounter>e.videoLength&&(e.videoCounter=1),jQuery(e).YTPPlayIndex(e.videoCounter),this},playPrev:function(){var e=this.get(0);return e.videoCounter--,e.videoCounter<=0&&(e.videoCounter=e.videoLength),jQuery(e).YTPPlayIndex(e.videoCounter),this},playIndex:function(e){var r=this.get(0);r.checkForStartAt&&(clearInterval(r.checkForStartAt),clearInterval(r.getState)),r.videoCounter=e,r.videoCounter>=r.videoLength&&(r.videoCounter=r.videoLength);var t=r.videos[r.videoCounter-1];return jQuery(r).YTPChangeVideo(t),this},changeVideo:function(e){var r=this,t=r.get(0);t.opt.startAt=0,t.opt.stopAt=0,t.opt.mask=!1,t.opt.mute=!0,t.opt.autoPlay=!0,t.opt.addFilters=!1,t.opt.coverImage=!1,t.hasData=!1,t.hasChanged=!0,t.player.loopTime=void 0,e&&jQuery.extend(t.opt,e),t.videoID=getYTPVideoID(t.opt.videoURL).videoID,t.opt.loop&&"boolean"==typeof t.opt.loop&&(t.opt.loop=9999),t.wrapper.css({background:"none"}),jQuery(t.playerEl).CSSAnimate({opacity:0},t.opt.fadeOnStartTime,function(){jQuery.mbYTPlayer.getDataFromAPI(t),r.YTPGetPlayer().loadVideoById({videoId:t.videoID,suggestedQuality:t.opt.quality}),r.YTPPause(),r.optimizeDisplay(),r.YTPCheckForState()});var a=jQuery.Event("YTPChangeVideo");return a.time=t.currentTime,jQuery(t).trigger(a),jQuery.mbYTPlayer.applyMask(t),this},getPlayer:function(){var e=this.get(0);return e.isReady&&e.player||null},playerDestroy:function(){var e=this.get(0);return e.isReady&&(ytp.YTAPIReady=!0,ytp.backgroundIsInited=!1,e.isInit=!1,e.videoID=null,e.isReady=!1,e.wrapper.remove(),jQuery("#controlBar_"+e.id).remove(),clearInterval(e.checkForStartAt),clearInterval(e.getState)),this},fullscreen:function(real){var YTPlayer=this.get(0);void 0===real&&(real=eval(YTPlayer.opt.realfullscreen));var controls=jQuery("#controlBar_"+YTPlayer.id),fullScreenBtn=controls.find(".mb_OnlyYT"),videoWrapper=YTPlayer.isPlayer?YTPlayer.opt.containment:YTPlayer.wrapper;if(real){var fullscreenchange=jQuery.mbBrowser.mozilla?"mozfullscreenchange":jQuery.mbBrowser.webkit?"webkitfullscreenchange":"fullscreenchange";jQuery(document).off(fullscreenchange).on(fullscreenchange,function(){RunPrefixMethod(document,"IsFullScreen")||RunPrefixMethod(document,"FullScreen")?(jQuery(YTPlayer).YTPSetVideoQuality("default"),jQuery(YTPlayer).trigger("YTPFullScreenStart")):(YTPlayer.isAlone=!1,fullScreenBtn.html(jQuery.mbYTPlayer.controls.onlyYT),jQuery(YTPlayer).YTPSetVideoQuality(YTPlayer.opt.quality),videoWrapper.removeClass("YTPFullscreen"),videoWrapper.CSSAnimate({opacity:YTPlayer.opt.opacity},YTPlayer.opt.fadeOnStartTime),videoWrapper.css({zIndex:0}),YTPlayer.isBackground?jQuery("body").after(controls):YTPlayer.wrapper.before(controls),jQuery(window).resize(),jQuery(YTPlayer).trigger("YTPFullScreenEnd"))})}if(YTPlayer.isAlone)jQuery(document).off("mousemove.YTPlayer"),clearTimeout(YTPlayer.hideCursor),YTPlayer.overlay.css({cursor:"auto"}),real?cancelFullscreen():(videoWrapper.CSSAnimate({opacity:YTPlayer.opt.opacity},YTPlayer.opt.fadeOnStartTime),videoWrapper.css({zIndex:0})),fullScreenBtn.html(jQuery.mbYTPlayer.controls.onlyYT),YTPlayer.isAlone=!1;else{function hideMouse(){YTPlayer.overlay.css({cursor:"none"})}jQuery(document).on("mousemove.YTPlayer",function(e){YTPlayer.overlay.css({cursor:"auto"}),clearTimeout(YTPlayer.hideCursor),jQuery(e.target).parents().is(".mb_YTPBar")||(YTPlayer.hideCursor=setTimeout(hideMouse,3e3))}),hideMouse(),real?(videoWrapper.css({opacity:0}),videoWrapper.addClass("YTPFullscreen"),launchFullscreen(videoWrapper.get(0)),setTimeout(function(){videoWrapper.CSSAnimate({opacity:1},2*YTPlayer.opt.fadeOnStartTime),videoWrapper.append(controls),jQuery(YTPlayer).optimizeDisplay(),YTPlayer.player.seekTo(YTPlayer.player.getCurrentTime()+.1,!0)},YTPlayer.opt.fadeOnStartTime)):videoWrapper.css({zIndex:1e4}).CSSAnimate({opacity:1},2*YTPlayer.opt.fadeOnStartTime),fullScreenBtn.html(jQuery.mbYTPlayer.controls.showSite),YTPlayer.isAlone=!0}function RunPrefixMethod(e,r){for(var t,a,o=["webkit","moz","ms","o",""],n=0;n<o.length&&!e[t];){if(t=r,""==o[n]&&(t=t.substr(0,1).toLowerCase()+t.substr(1)),"undefined"!=(a=typeof e[t=o[n]+t]))return o=[o[n]],"function"==a?e[t]():e[t];n++}}function launchFullscreen(e){RunPrefixMethod(e,"RequestFullScreen")}function cancelFullscreen(){(RunPrefixMethod(document,"FullScreen")||RunPrefixMethod(document,"IsFullScreen"))&&RunPrefixMethod(document,"CancelFullScreen")}return this},toggleLoops:function(){var e=this.get(0),r=e.opt;return 1==r.loop?r.loop=0:(r.startAt?e.player.seekTo(r.startAt):e.player.playVideo(),r.loop=1),this},play:function(){var e=this.get(0),r=jQuery(e);return e.isReady&&(setTimeout(function(){r.YTPSetAbundance(e.opt.abundance)},300),e.player.playVideo(),jQuery(e.playerEl).css({opacity:1}),e.wrapper.css({backgroundImage:"none"}),e.wrapper.CSSAnimate({opacity:e.isAlone?1:e.opt.opacity},e.opt.fadeOnStartTime),jQuery("#controlBar_"+e.id).find(".mb_YTPPlayPause").html(jQuery.mbYTPlayer.controls.pause),e.state=1),this},togglePlay:function(e){var r=this.get(0);return r.isReady&&(1==r.state?this.YTPPause():this.YTPPlay(),"function"==typeof e&&e(r.state)),this},stop:function(){var e=this.get(0);return e.isReady&&(jQuery("#controlBar_"+e.id).find(".mb_YTPPlayPause").html(jQuery.mbYTPlayer.controls.play),e.player.stopVideo()),this},pause:function(){var e=this.get(0);return e.isReady&&(e.opt.abundance<.2&&this.YTPSetAbundance(.2),e.player.pauseVideo(),e.state=2),this},seekTo:function(e){var r=this.get(0);return r.isReady&&r.player.seekTo(e,!0),this},setVolume:function(e){var r=this.get(0);return r.isReady&&(r.opt.vol=e,r.player.setVolume(r.opt.vol),r.volumeBar&&r.volumeBar.length&&r.volumeBar.updateSliderVal(e)),this},getVolume:function(){var e=this.get(0);return e.isReady?e.player.getVolume():this},toggleVolume:function(){var e=this.get(0);return e.isReady&&(e.isMute?(jQuery.mbBrowser.mobile||this.YTPSetVolume(e.opt.vol),this.YTPUnmute()):this.YTPMute()),this},mute:function(){var e=this.get(0);if(!e.isReady)return this;if(e.isMute)return this;e.player.mute(),e.isMute=!0,e.player.setVolume(0),e.volumeBar&&e.volumeBar.length&&10<e.volumeBar.width()&&e.volumeBar.updateSliderVal(0),jQuery("#controlBar_"+e.id).find(".mb_YTPMuteUnmute").html(jQuery.mbYTPlayer.controls.unmute),jQuery(e).addClass("isMuted"),e.volumeBar&&e.volumeBar.length&&e.volumeBar.addClass("muted");var r=jQuery.Event("YTPMuted");return r.time=e.currentTime,e.preventTrigger||jQuery(e).trigger(r),this},unmute:function(){var e=this.get(0);if(!e.isReady)return this;if(!e.isMute)return this;e.player.unMute(),e.isMute=!1,jQuery(e).YTPSetVolume(e.opt.vol),e.volumeBar&&e.volumeBar.length&&e.volumeBar.updateSliderVal(10<e.opt.vol?e.opt.vol:10),jQuery("#controlBar_"+e.id).find(".mb_YTPMuteUnmute").html(jQuery.mbYTPlayer.controls.mute),jQuery(e).removeClass("isMuted"),e.volumeBar&&e.volumeBar.length&&e.volumeBar.removeClass("muted");var r=jQuery.Event("YTPUnmuted");return r.time=e.currentTime,e.preventTrigger||jQuery(e).trigger(r),this},applyFilter:function(e,r){var t=this.get(0);if(!t.isReady)return this;t.filters[e].value=r,t.filtersEnabled&&this.YTPEnableFilters()},applyFilters:function(e){var r=this,t=r.get(0);if(!t.isReady)return this;if(!t.isReady)return jQuery(t).on("YTPReady",function(){r.YTPApplyFilters(e)}),this;for(var a in e)r.YTPApplyFilter(a,e[a]);r.trigger("YTPFiltersApplied")},toggleFilter:function(e,r){var t=this.get(0);return t.isReady&&(t.filters[e].value?t.filters[e].value=0:t.filters[e].value=r,t.filtersEnabled&&jQuery(t).YTPEnableFilters()),this},toggleFilters:function(e){var r=this.get(0);return r.isReady&&(r.filtersEnabled?(jQuery(r).trigger("YTPDisableFilters"),jQuery(r).YTPDisableFilters()):(jQuery(r).YTPEnableFilters(),jQuery(r).trigger("YTPEnableFilters")),"function"==typeof e&&e(r.filtersEnabled)),this},disableFilters:function(){var e=this.get(0);if(!e.isReady)return this;var r=jQuery(e.playerEl);return r.css("-webkit-filter",""),r.css("filter",""),e.filtersEnabled=!1,this},enableFilters:function(){var e=this.get(0);if(!e.isReady)return this;var r=jQuery(e.playerEl),t="";for(var a in e.filters)e.filters[a].value&&(t+=a.replace("_","-")+"("+e.filters[a].value+e.filters[a].unit+") ");return r.css("-webkit-filter",t),r.css("filter",t),e.filtersEnabled=!0,this},removeFilter:function(e,r){var t=this.get(0);if(!t.isReady)return this;if("function"==typeof e&&(r=e,e=null),e)this.YTPApplyFilter(e,0),"function"==typeof r&&r(e);else{for(var a in t.filters)this.YTPApplyFilter(a,0);"function"==typeof r&&r(a),t.filters=jQuery.extend(!0,{},jQuery.mbYTPlayer.defaultFilters)}var o=jQuery.Event("YTPFiltersApplied");return this.trigger(o),this},getFilters:function(){var e=this.get(0);return e.isReady?e.filters:this},addMask:function(e){var r=this.get(0);if(!r.isReady)return this;e||(e=r.actualMask);var t=jQuery("<img/>").attr("src",e).on("load",function(){r.overlay.CSSAnimate({opacity:0},r.opt.fadeOnStartTime,function(){r.hasMask=!0,t.remove(),r.overlay.css({backgroundImage:"url("+e+")",backgroundRepeat:"no-repeat",backgroundPosition:"center center",backgroundSize:"cover"}),r.overlay.CSSAnimate({opacity:1},r.opt.fadeOnStartTime)})});return this},removeMask:function(){var e=this.get(0);return e.isReady&&e.overlay.CSSAnimate({opacity:0},e.opt.fadeOnStartTime,function(){e.hasMask=!1,e.overlay.css({backgroundImage:"",backgroundRepeat:"",backgroundPosition:"",backgroundSize:""}),e.overlay.CSSAnimate({opacity:1},e.opt.fadeOnStartTime)}),this},applyMask:function(t){var a=jQuery(t);if(!t.isReady)return this;if(a.off("YTPTime.mask"),t.opt.mask)if("string"==typeof t.opt.mask)a.YTPAddMask(t.opt.mask),t.actualMask=t.opt.mask;else if("object"==typeof t.opt.mask){for(var e in t.opt.mask)if(t.opt.mask[e])jQuery("<img/>").attr("src",t.opt.mask[e]);t.opt.mask[0]&&a.YTPAddMask(t.opt.mask[0]),a.on("YTPTime.mask",function(e){for(var r in t.opt.mask)e.time==r&&(t.opt.mask[r]?(a.YTPAddMask(t.opt.mask[r]),t.actualMask=t.opt.mask[r]):a.YTPRemoveMask())})}},toggleMask:function(){var e=this.get(0);if(!e.isReady)return this;var r=jQuery(e);return e.hasMask?r.YTPRemoveMask():r.YTPAddMask(),this},manageProgress:function(){var e=this.get(0),r=jQuery("#controlBar_"+e.id),t=r.find(".mb_YTPProgress"),a=r.find(".mb_YTPLoaded"),o=r.find(".mb_YTPseekbar"),n=t.outerWidth(),i=Math.floor(e.player.getCurrentTime()),l=Math.floor(e.player.getDuration()),s=i*n/l,u=100*e.player.getVideoLoadedFraction();return a.css({left:0,width:u+"%"}),o.css({left:0,width:s}),{totalTime:l,currentTime:i}},buildControls:function(YTPlayer){if(jQuery("#controlBar_"+YTPlayer.id).remove(),YTPlayer.opt.showControls){if(YTPlayer.opt.showYTLogo=YTPlayer.opt.showYTLogo||YTPlayer.opt.printUrl,!jQuery("#controlBar_"+YTPlayer.id).length){YTPlayer.controlBar=jQuery("<span/>").attr("id","controlBar_"+YTPlayer.id).addClass("mb_YTPBar").css({whiteSpace:"noWrap",position:YTPlayer.isBackground?"fixed":"absolute",zIndex:YTPlayer.isBackground?1e4:1e3}).hide().on("click",function(e){e.stopPropagation()});var buttonBar=jQuery("<div/>").addClass("buttonBar"),playpause=jQuery("<span>"+jQuery.mbYTPlayer.controls.play+"</span>").addClass("mb_YTPPlayPause ytpicon").on("click",function(e){e.stopPropagation(),jQuery(YTPlayer).YTPTogglePlay()}),MuteUnmute=jQuery("<span>"+jQuery.mbYTPlayer.controls.mute+"</span>").addClass("mb_YTPMuteUnmute ytpicon").on("click",function(e){e.stopPropagation(),jQuery(YTPlayer).YTPToggleVolume()}),volumeBar=jQuery("<div/>").addClass("mb_YTPVolumeBar").css({display:"inline-block"});YTPlayer.volumeBar=volumeBar;var idx=jQuery("<span/>").addClass("mb_YTPTime"),vURL=YTPlayer.opt.videoURL?YTPlayer.opt.videoURL:"";vURL.indexOf("http")<0&&(vURL=jQuery.mbYTPlayer.locationProtocol+"//www.youtube.com/watch?v="+YTPlayer.opt.videoURL);var movieUrl=jQuery("<span/>").html(jQuery.mbYTPlayer.controls.ytLogo).addClass("mb_YTPUrl ytpicon").attr("title","view on YouTube").on("click",function(){window.open(vURL,"viewOnYT")}),onlyVideo=jQuery("<span/>").html(jQuery.mbYTPlayer.controls.onlyYT).addClass("mb_OnlyYT ytpicon").on("click",function(e){e.stopPropagation(),jQuery(YTPlayer).YTPFullscreen(YTPlayer.opt.realfullscreen)}),progressBar=jQuery("<div/>").addClass("mb_YTPProgress").css("position","absolute").on("click",function(e){e.stopPropagation(),timeBar.css({width:e.clientX-timeBar.offset().left}),YTPlayer.timeW=e.clientX-timeBar.offset().left,YTPlayer.controlBar.find(".mb_YTPLoaded").css({width:0});var r=Math.floor(YTPlayer.player.getDuration());YTPlayer.goto=timeBar.outerWidth()*r/progressBar.outerWidth(),YTPlayer.player.seekTo(parseFloat(YTPlayer.goto),!0),YTPlayer.controlBar.find(".mb_YTPLoaded").css({width:0})}),loadedBar=jQuery("<div/>").addClass("mb_YTPLoaded").css("position","absolute"),timeBar=jQuery("<div/>").addClass("mb_YTPseekbar").css("position","absolute");progressBar.append(loadedBar).append(timeBar),buttonBar.append(playpause).append(MuteUnmute).append(volumeBar).append(idx),YTPlayer.opt.showYTLogo&&buttonBar.append(movieUrl),(YTPlayer.isBackground||eval(YTPlayer.opt.realfullscreen)&&!YTPlayer.isBackground)&&buttonBar.append(onlyVideo),YTPlayer.controlBar.append(buttonBar).append(progressBar),YTPlayer.isBackground?jQuery("body").after(YTPlayer.controlBar):(YTPlayer.controlBar.addClass("inlinePlayer"),YTPlayer.wrapper.before(YTPlayer.controlBar)),volumeBar.simpleSlider({initialval:YTPlayer.opt.vol,scale:100,orientation:"h",callback:function(e){0==e.value?jQuery(YTPlayer).YTPMute():jQuery(YTPlayer).YTPUnmute(),YTPlayer.player.setVolume(e.value),YTPlayer.isMute||(YTPlayer.opt.vol=e.value)}})}}else YTPlayer.controlBar=!1},checkForState:function(){var YTPlayer=this.get(0),$YTPlayer=jQuery(YTPlayer);clearInterval(YTPlayer.getState);var interval=100;if(!jQuery.contains(document,YTPlayer))return $YTPlayer.YTPPlayerDestroy(),clearInterval(YTPlayer.getState),void clearInterval(YTPlayer.checkForStartAt);jQuery.mbYTPlayer.checkForStart(YTPlayer),YTPlayer.getState=setInterval(function(){var $YTPlayer=jQuery(YTPlayer);if(YTPlayer.isReady){var prog=jQuery(YTPlayer).YTPManageProgress(),stopAt=YTPlayer.opt.stopAt>YTPlayer.opt.startAt?YTPlayer.opt.stopAt:0;if(stopAt=stopAt<YTPlayer.player.getDuration()?stopAt:0,YTPlayer.currentTime!=prog.currentTime){var YTPEvent=jQuery.Event("YTPTime");YTPEvent.time=YTPlayer.currentTime,jQuery(YTPlayer).trigger(YTPEvent)}if(YTPlayer.currentTime=prog.currentTime,YTPlayer.totalTime=YTPlayer.player.getDuration(),0==YTPlayer.player.getVolume()?$YTPlayer.addClass("isMuted"):$YTPlayer.removeClass("isMuted"),YTPlayer.opt.showControls&&(prog.totalTime?YTPlayer.controlBar.find(".mb_YTPTime").html(jQuery.mbYTPlayer.formatTime(prog.currentTime)+" / "+jQuery.mbYTPlayer.formatTime(prog.totalTime)):YTPlayer.controlBar.find(".mb_YTPTime").html("-- : -- / -- : --")),eval(YTPlayer.opt.stopMovieOnBlur)&&(document.hasFocus()?document.hasFocus()&&!YTPlayer.hasFocus&&-1!=YTPlayer.state&&0!=YTPlayer.state&&(YTPlayer.hasFocus=!0,YTPlayer.preventTrigger=!0,$YTPlayer.YTPPlay()):1==YTPlayer.state&&(YTPlayer.hasFocus=!1,YTPlayer.preventTrigger=!0,$YTPlayer.YTPPause())),YTPlayer.opt.playOnlyIfVisible){var isOnScreen=jQuery.mbYTPlayer.isOnScreen(YTPlayer,YTPlayer.opt.onScreenPercentage);isOnScreen||1!=YTPlayer.state?isOnScreen&&!YTPlayer.isOnScreen&&(YTPlayer.isOnScreen=!0,YTPlayer.player.playVideo()):(YTPlayer.isOnScreen=!1,$YTPlayer.YTPPause())}if(YTPlayer.controlBar.length&&YTPlayer.controlBar.outerWidth()<=400&&!YTPlayer.isCompact?(YTPlayer.controlBar.addClass("compact"),YTPlayer.isCompact=!0,!YTPlayer.isMute&&YTPlayer.volumeBar&&YTPlayer.volumeBar.updateSliderVal(YTPlayer.opt.vol)):YTPlayer.controlBar.length&&400<YTPlayer.controlBar.outerWidth()&&YTPlayer.isCompact&&(YTPlayer.controlBar.removeClass("compact"),YTPlayer.isCompact=!1,!YTPlayer.isMute&&YTPlayer.volumeBar&&YTPlayer.volumeBar.updateSliderVal(YTPlayer.opt.vol)),0<YTPlayer.player.getPlayerState()&&(parseFloat(YTPlayer.player.getDuration()-.5)<YTPlayer.player.getCurrentTime()||0<stopAt&&parseFloat(YTPlayer.player.getCurrentTime())>stopAt)){if(YTPlayer.isEnded)return;if(YTPlayer.isEnded=!0,setTimeout(function(){YTPlayer.isEnded=!1},1e3),YTPlayer.isList){if(!YTPlayer.opt.loop||0<YTPlayer.opt.loop&&YTPlayer.player.loopTime===YTPlayer.opt.loop-1){YTPlayer.player.loopTime=void 0,clearInterval(YTPlayer.getState);var YTPEnd=jQuery.Event("YTPEnd");return YTPEnd.time=YTPlayer.currentTime,void jQuery(YTPlayer).trigger(YTPEnd)}}else if(!YTPlayer.opt.loop||0<YTPlayer.opt.loop&&YTPlayer.player.loopTime===YTPlayer.opt.loop-1){YTPlayer.player.loopTime=void 0,YTPlayer.state=2;var bgndURL=YTPlayer.opt.coverImage?"url("+YTPlayer.opt.coverImage+") center center":YTPlayer.orig_containment_background;return YTPlayer.opt.containment.css({background:bgndURL,backgroundSize:"cover"}),jQuery(YTPlayer).YTPPause(),void YTPlayer.wrapper.CSSAnimate({opacity:0},YTPlayer.opt.fadeOnStartTime,function(){YTPlayer.controlBar.length&&YTPlayer.controlBar.find(".mb_YTPPlayPause").html(jQuery.mbYTPlayer.controls.play);var e=jQuery.Event("YTPEnd");e.time=YTPlayer.currentTime,jQuery(YTPlayer).trigger(e),YTPlayer.player.seekTo(YTPlayer.opt.startAt,!0);var r=YTPlayer.opt.coverImage?"url("+YTPlayer.opt.coverImage+") center center":YTPlayer.orig_containment_background;YTPlayer.opt.containment.css({background:r,backgroundSize:"cover"})})}YTPlayer.player.loopTime=YTPlayer.player.loopTime?++YTPlayer.player.loopTime:1,YTPlayer.opt.startAt=YTPlayer.opt.startAt||1,YTPlayer.preventTrigger=!0,YTPlayer.state=2,YTPlayer.player.seekTo(YTPlayer.opt.startAt,!0)}}},interval)},checkForStart:function(YTPlayer){var $YTPlayer=jQuery(YTPlayer);if(jQuery.contains(document,YTPlayer)){if(jQuery.mbYTPlayer.buildControls(YTPlayer),YTPlayer.overlay)if(YTPlayer.opt.addRaster){var classN="dot"==YTPlayer.opt.addRaster?"raster-dot":"raster";YTPlayer.overlay.addClass(YTPlayer.isRetina?classN+" retina":classN)}else YTPlayer.overlay.removeClass(function(e,r){var t=r.split(" "),a=[];return jQuery.each(t,function(e,r){/raster.*/.test(r)&&a.push(r)}),a.push("retina"),a.join(" ")});YTPlayer.preventTrigger=!0,YTPlayer.state=2,YTPlayer.preventTrigger=!0,YTPlayer.player.mute(),YTPlayer.player.playVideo(),YTPlayer.isStarting=!0;var startAt=YTPlayer.start_from_last?YTPlayer.start_from_last:YTPlayer.opt.startAt?YTPlayer.opt.startAt:1;return YTPlayer.preventTrigger=!0,YTPlayer.checkForStartAt=setInterval(function(){YTPlayer.player.mute(),YTPlayer.player.seekTo(startAt,!0);var canPlayVideo=YTPlayer.player.getVideoLoadedFraction()>=startAt/YTPlayer.player.getDuration();if(0<YTPlayer.player.getDuration()&&YTPlayer.player.getCurrentTime()>=startAt&&canPlayVideo){YTPlayer.start_from_last=null,YTPlayer.preventTrigger=!0,$YTPlayer.YTPPause(),clearInterval(YTPlayer.checkForStartAt),"function"==typeof YTPlayer.opt.onReady&&YTPlayer.opt.onReady(YTPlayer),YTPlayer.isReady=!0,$YTPlayer.YTPRemoveFilter(),YTPlayer.opt.addFilters?$YTPlayer.YTPApplyFilters(YTPlayer.opt.addFilters):$YTPlayer.YTPApplyFilters(),$YTPlayer.YTPEnableFilters();var YTPready=jQuery.Event("YTPReady");if(YTPready.time=YTPlayer.currentTime,$YTPlayer.trigger(YTPready),YTPlayer.state=2,YTPlayer.opt.mute?YTPlayer.player.mute():(YTPlayer.player.unMute(),YTPlayer.opt.autoPlay&&console.debug("To make the video 'auto-play' you must mute the audio according with the new vendor policy")),"undefined"!=typeof _gaq&&eval(YTPlayer.opt.gaTrack)?_gaq.push(["_trackEvent","YTPlayer","Play",YTPlayer.hasData?YTPlayer.videoData.title:YTPlayer.videoID.toString()]):"undefined"!=typeof ga&&eval(YTPlayer.opt.gaTrack)&&ga("send","event","YTPlayer","play",YTPlayer.hasData?YTPlayer.videoData.title:YTPlayer.videoID.toString()),YTPlayer.opt.autoPlay){var YTPStart=jQuery.Event("YTPStart");YTPStart.time=YTPlayer.currentTime,jQuery(YTPlayer).trigger(YTPStart),YTPlayer.isStarting=!1,"mac"==jQuery.mbBrowser.os.name&&jQuery.mbBrowser.safari&&jQuery("body").one("mousedown.YTPstart",function(){$YTPlayer.YTPPlay()}),$YTPlayer.YTPPlay()}else YTPlayer.preventTrigger=!0,$YTPlayer.YTPPause(),YTPlayer.start_from_last&&YTPlayer.player.seekTo(startAt,!0),setTimeout(function(){YTPlayer.preventTrigger=!0,$YTPlayer.YTPPause(),console.debug("YTPPause"),YTPlayer.isPlayer||(YTPlayer.opt.coverImage?(YTPlayer.wrapper.css({opacity:0}),setTimeout(function(){var e=YTPlayer.opt.coverImage?"url("+YTPlayer.opt.coverImage+") center center":YTPlayer.orig_containment_background;YTPlayer.wrapper.css({background:e,backgroundSize:"cover",backgroundRepeat:"no-repeat"})},YTPlayer.opt.fadeOnStartTime)):(jQuery(YTPlayer.playerEl).CSSAnimate({opacity:1},YTPlayer.opt.fadeOnStartTime),YTPlayer.wrapper.CSSAnimate({opacity:YTPlayer.isAlone?1:YTPlayer.opt.opacity},YTPlayer.opt.fadeOnStartTime))),YTPlayer.isStarting=!1},500),YTPlayer.controlBar.length&&YTPlayer.controlBar.find(".mb_YTPPlayPause").html(jQuery.mbYTPlayer.controls.play);YTPlayer.isPlayer&&!YTPlayer.opt.autoPlay&&YTPlayer.loading&&YTPlayer.loading.length&&(YTPlayer.loading.html("Ready"),setTimeout(function(){YTPlayer.loading.fadeOut()},100)),YTPlayer.controlBar&&YTPlayer.controlBar.length&&YTPlayer.controlBar.slideDown(1e3)}"mac"==jQuery.mbBrowser.os.name&&jQuery.mbBrowser.safari&&(YTPlayer.player.playVideo(),0<=startAt&&YTPlayer.player.seekTo(startAt,!0))},100),$YTPlayer}$YTPlayer.YTPPlayerDestroy()},getTime:function(){var e=this.get(0);return jQuery.mbYTPlayer.formatTime(e.currentTime)},getTotalTime:function(e){var r=this.get(0);return jQuery.mbYTPlayer.formatTime(r.totalTime)},formatTime:function(e){var r=Math.floor(e/60),t=Math.floor(e-60*r);return(r<=9?"0"+r:r)+" : "+(t<=9?"0"+t:t)},setAnchor:function(e){this.optimizeDisplay(e)},getAnchor:function(){return this.get(0).opt.anchor},setAbundance:function(e,r){var t=this.get(0);return r&&(t.opt.abundance=e),this.optimizeDisplay(t.opt.anchor,e),this},getAbundance:function(){return this.get(0).opt.abundance},setOption:function(e,r){var t=this.get(0);return t.opt[e]=r,this}},jQuery.fn.optimizeDisplay=function(anchor,abundanceX){var YTPlayer=this.get(0),vid={},el=YTPlayer.wrapper,iframe=jQuery(YTPlayer.playerEl);YTPlayer.opt.anchor=anchor||YTPlayer.opt.anchor,YTPlayer.opt.anchor="undefined "!=typeof YTPlayer.opt.anchor?YTPlayer.opt.anchor:"center,center";var YTPAlign=YTPlayer.opt.anchor.split(","),ab=abundanceX||YTPlayer.opt.abundance;if(YTPlayer.opt.optimizeDisplay){var abundance=el.height()*ab,win={};win.width=el.outerWidth(),win.height=el.outerHeight()+abundance,YTPlayer.opt.ratio="auto"===YTPlayer.opt.ratio?16/9:YTPlayer.opt.ratio,YTPlayer.opt.ratio=eval(YTPlayer.opt.ratio),vid.width=win.width+abundance,vid.height=Math.ceil(vid.width/YTPlayer.opt.ratio),vid.marginTop=Math.ceil(-(vid.height-win.height)/2),vid.marginLeft=-abundance/2;var lowest=vid.height<win.height;for(var a in lowest&&(vid.height=win.height+abundance,vid.width=Math.ceil(vid.height*YTPlayer.opt.ratio),vid.marginTop=-abundance/2,vid.marginLeft=Math.ceil(-(vid.width-win.width)/2)),YTPAlign)if(YTPAlign.hasOwnProperty(a)){var al=YTPAlign[a].replace(/ /g,"");switch(al){case"top":vid.marginTop=-abundance/2;break;case"bottom":vid.marginTop=Math.ceil(-(vid.height-win.height)-abundance/2);break;case"left":vid.marginLeft=-abundance/2;break;case"right":vid.marginLeft=Math.ceil(-(vid.width-win.width)+abundance/2);break;default:vid.width>win.width&&(vid.marginLeft=-(vid.width-win.width)/2+abundance/2)}}}else vid.width="100%",vid.height="100%",vid.marginTop=0,vid.marginLeft=0;iframe.css({width:vid.width,height:vid.height,marginTop:vid.marginTop,marginLeft:vid.marginLeft,maxWidth:"initial"})},jQuery.shuffle=function(e){for(var r=e.slice(),t=r.length,a=t;a--;){var o=parseInt(Math.random()*t),n=r[a];r[a]=r[o],r[o]=n}return r},jQuery.fn.unselectable=function(){return this.each(function(){jQuery(this).css({"-moz-user-select":"none","-webkit-user-select":"none","user-select":"none"}).attr("unselectable","on")})},jQuery.fn.YTPlayer=jQuery.mbYTPlayer.buildPlayer,jQuery.fn.mb_YTPlayer=jQuery.mbYTPlayer.buildPlayer,jQuery.fn.YTPCheckForState=jQuery.mbYTPlayer.checkForState,jQuery.fn.YTPGetPlayer=jQuery.mbYTPlayer.getPlayer,jQuery.fn.YTPGetVideoID=jQuery.mbYTPlayer.getVideoID,jQuery.fn.YTPGetPlaylistID=jQuery.mbYTPlayer.getPlaylistID,jQuery.fn.YTPChangeVideo=jQuery.fn.YTPChangeMovie=jQuery.mbYTPlayer.changeVideo,jQuery.fn.YTPPlayerDestroy=jQuery.mbYTPlayer.playerDestroy,jQuery.fn.YTPPlay=jQuery.mbYTPlayer.play,jQuery.fn.YTPTogglePlay=jQuery.mbYTPlayer.togglePlay,jQuery.fn.YTPStop=jQuery.mbYTPlayer.stop,jQuery.fn.YTPPause=jQuery.mbYTPlayer.pause,jQuery.fn.YTPSeekTo=jQuery.mbYTPlayer.seekTo,jQuery.fn.YTPlaylist=jQuery.mbYTPlayer.playlist,jQuery.fn.YTPPlayNext=jQuery.mbYTPlayer.playNext,jQuery.fn.YTPPlayPrev=jQuery.mbYTPlayer.playPrev,jQuery.fn.YTPPlayIndex=jQuery.mbYTPlayer.playIndex,jQuery.fn.YTPMute=jQuery.mbYTPlayer.mute,jQuery.fn.YTPUnmute=jQuery.mbYTPlayer.unmute,jQuery.fn.YTPToggleVolume=jQuery.mbYTPlayer.toggleVolume,jQuery.fn.YTPSetVolume=jQuery.mbYTPlayer.setVolume,jQuery.fn.YTPGetVolume=jQuery.mbYTPlayer.getVolume,jQuery.fn.YTPGetVideoData=jQuery.mbYTPlayer.getVideoData,jQuery.fn.YTPFullscreen=jQuery.mbYTPlayer.fullscreen,jQuery.fn.YTPToggleLoops=jQuery.mbYTPlayer.toggleLoops,jQuery.fn.YTPSetVideoQuality=jQuery.mbYTPlayer.setVideoQuality,jQuery.fn.YTPManageProgress=jQuery.mbYTPlayer.manageProgress,jQuery.fn.YTPApplyFilter=jQuery.mbYTPlayer.applyFilter,jQuery.fn.YTPApplyFilters=jQuery.mbYTPlayer.applyFilters,jQuery.fn.YTPToggleFilter=jQuery.mbYTPlayer.toggleFilter,jQuery.fn.YTPToggleFilters=jQuery.mbYTPlayer.toggleFilters,jQuery.fn.YTPRemoveFilter=jQuery.mbYTPlayer.removeFilter,jQuery.fn.YTPDisableFilters=jQuery.mbYTPlayer.disableFilters,jQuery.fn.YTPEnableFilters=jQuery.mbYTPlayer.enableFilters,jQuery.fn.YTPGetFilters=jQuery.mbYTPlayer.getFilters,jQuery.fn.YTPGetTime=jQuery.mbYTPlayer.getTime,jQuery.fn.YTPGetTotalTime=jQuery.mbYTPlayer.getTotalTime,jQuery.fn.YTPAddMask=jQuery.mbYTPlayer.addMask,jQuery.fn.YTPRemoveMask=jQuery.mbYTPlayer.removeMask,jQuery.fn.YTPToggleMask=jQuery.mbYTPlayer.toggleMask,jQuery.fn.YTPGetAbundance=jQuery.mbYTPlayer.getAbundance,jQuery.fn.YTPSetAbundance=jQuery.mbYTPlayer.setAbundance,jQuery.fn.YTPSetAnchor=jQuery.mbYTPlayer.setAnchor,jQuery.fn.YTPGetAnchor=jQuery.mbYTPlayer.getAnchor,jQuery.fn.YTPSetOption=jQuery.mbYTPlayer.setOption}(jQuery,ytp);var nAgt=navigator.userAgent;function isTouchSupported(){var e=nAgt.msMaxTouchPoints,r="ontouchstart"in document.createElement("div");return!(!e&&!r)}jQuery.browser=jQuery.browser||{},jQuery.browser.mozilla=!1,jQuery.browser.webkit=!1,jQuery.browser.opera=!1,jQuery.browser.safari=!1,jQuery.browser.chrome=!1,jQuery.browser.androidStock=!1,jQuery.browser.msie=!1,jQuery.browser.edge=!1,jQuery.browser.ua=nAgt;var getOS=function(){var e={version:"Unknown version",name:"Unknown OS"};return-1!=navigator.appVersion.indexOf("Win")&&(e.name="Windows"),-1!=navigator.appVersion.indexOf("Mac")&&navigator.appVersion.indexOf("Mobile")<0&&(e.name="Mac"),-1!=navigator.appVersion.indexOf("Linux")&&(e.name="Linux"),/Mac OS X/.test(nAgt)&&!/Mobile/.test(nAgt)&&(e.version=/Mac OS X (10[\.\_\d]+)/.exec(nAgt)[1],e.version=e.version.replace(/_/g,".").substring(0,5)),/Windows/.test(nAgt)&&(e.version="Unknown.Unknown"),/Windows NT 5.1/.test(nAgt)&&(e.version="5.1"),/Windows NT 6.0/.test(nAgt)&&(e.version="6.0"),/Windows NT 6.1/.test(nAgt)&&(e.version="6.1"),/Windows NT 6.2/.test(nAgt)&&(e.version="6.2"),/Windows NT 10.0/.test(nAgt)&&(e.version="10.0"),/Linux/.test(nAgt)&&/Linux/.test(nAgt)&&(e.version="Unknown.Unknown"),e.name=e.name.toLowerCase(),e.major_version="Unknown",e.minor_version="Unknown","Unknown.Unknown"!=e.version&&(e.major_version=parseFloat(e.version.split(".")[0]),e.minor_version=parseFloat(e.version.split(".")[1])),e},nameOffset,verOffset,ix;if(jQuery.browser.os=getOS(),jQuery.browser.hasTouch=isTouchSupported(),jQuery.browser.name=navigator.appName,jQuery.browser.fullVersion=""+parseFloat(navigator.appVersion),jQuery.browser.majorVersion=parseInt(navigator.appVersion,10),-1!=(verOffset=nAgt.indexOf("Opera")))jQuery.browser.opera=!0,jQuery.browser.name="Opera",jQuery.browser.fullVersion=nAgt.substring(verOffset+6),-1!=(verOffset=nAgt.indexOf("Version"))&&(jQuery.browser.fullVersion=nAgt.substring(verOffset+8));else if(-1!=(verOffset=nAgt.indexOf("OPR")))jQuery.browser.opera=!0,jQuery.browser.name="Opera",jQuery.browser.fullVersion=nAgt.substring(verOffset+4);else if(-1!=(verOffset=nAgt.indexOf("MSIE")))jQuery.browser.msie=!0,jQuery.browser.name="Microsoft Internet Explorer",jQuery.browser.fullVersion=nAgt.substring(verOffset+5);else if(-1!=nAgt.indexOf("Trident")){jQuery.browser.msie=!0,jQuery.browser.name="Microsoft Internet Explorer";var start=nAgt.indexOf("rv:")+3,end=start+4;jQuery.browser.fullVersion=nAgt.substring(start,end)}else-1!=(verOffset=nAgt.indexOf("Edge"))?(jQuery.browser.edge=!0,jQuery.browser.name="Microsoft Edge",jQuery.browser.fullVersion=nAgt.substring(verOffset+5)):-1!=(verOffset=nAgt.indexOf("Chrome"))?(jQuery.browser.webkit=!0,jQuery.browser.chrome=!0,jQuery.browser.name="Chrome",jQuery.browser.fullVersion=nAgt.substring(verOffset+7)):-1<nAgt.indexOf("mozilla/5.0")&&-1<nAgt.indexOf("android ")&&-1<nAgt.indexOf("applewebkit")&&!(-1<nAgt.indexOf("chrome"))?(verOffset=nAgt.indexOf("Chrome"),jQuery.browser.webkit=!0,jQuery.browser.androidStock=!0,jQuery.browser.name="androidStock",jQuery.browser.fullVersion=nAgt.substring(verOffset+7)):-1!=(verOffset=nAgt.indexOf("Safari"))?(jQuery.browser.webkit=!0,jQuery.browser.safari=!0,jQuery.browser.name="Safari",jQuery.browser.fullVersion=nAgt.substring(verOffset+7),-1!=(verOffset=nAgt.indexOf("Version"))&&(jQuery.browser.fullVersion=nAgt.substring(verOffset+8))):-1!=(verOffset=nAgt.indexOf("AppleWebkit"))?(jQuery.browser.webkit=!0,jQuery.browser.safari=!0,jQuery.browser.name="Safari",jQuery.browser.fullVersion=nAgt.substring(verOffset+7),-1!=(verOffset=nAgt.indexOf("Version"))&&(jQuery.browser.fullVersion=nAgt.substring(verOffset+8))):-1!=(verOffset=nAgt.indexOf("Firefox"))?(jQuery.browser.mozilla=!0,jQuery.browser.name="Firefox",jQuery.browser.fullVersion=nAgt.substring(verOffset+8)):(nameOffset=nAgt.lastIndexOf(" ")+1)<(verOffset=nAgt.lastIndexOf("/"))&&(jQuery.browser.name=nAgt.substring(nameOffset,verOffset),jQuery.browser.fullVersion=nAgt.substring(verOffset+1),jQuery.browser.name.toLowerCase()==jQuery.browser.name.toUpperCase()&&(jQuery.browser.name=navigator.appName));function uncamel(e){return e.replace(/([A-Z])/g,function(e){return"-"+e.toLowerCase()})}function setUnit(e,r){return"string"!=typeof e||e.match(/^[\-0-9\.]+jQuery/)?""+e+r:e}function setFilter(e,r,t){var a=uncamel(r),o=jQuery.browser.mozilla?"":jQuery.CSS.sfx;e[o+"filter"]=e[o+"filter"]||"",t=setUnit(t>jQuery.CSS.filters[r].max?jQuery.CSS.filters[r].max:t,jQuery.CSS.filters[r].unit),e[o+"filter"]+=a+"("+t+") ",delete e[r]}-1!=(ix=jQuery.browser.fullVersion.indexOf(";"))&&(jQuery.browser.fullVersion=jQuery.browser.fullVersion.substring(0,ix)),-1!=(ix=jQuery.browser.fullVersion.indexOf(" "))&&(jQuery.browser.fullVersion=jQuery.browser.fullVersion.substring(0,ix)),jQuery.browser.majorVersion=parseInt(""+jQuery.browser.fullVersion,10),isNaN(jQuery.browser.majorVersion)&&(jQuery.browser.fullVersion=""+parseFloat(navigator.appVersion),jQuery.browser.majorVersion=parseInt(navigator.appVersion,10)),jQuery.browser.version=jQuery.browser.majorVersion,jQuery.browser.android=/Android/i.test(nAgt),jQuery.browser.blackberry=/BlackBerry|BB|PlayBook/i.test(nAgt),jQuery.browser.ios=/iPhone|iPad|iPod|webOS/i.test(nAgt),jQuery.browser.operaMobile=/Opera Mini/i.test(nAgt),jQuery.browser.windowsMobile=/IEMobile|Windows Phone/i.test(nAgt),jQuery.browser.kindle=/Kindle|Silk/i.test(nAgt),jQuery.browser.mobile=jQuery.browser.android||jQuery.browser.blackberry||jQuery.browser.ios||jQuery.browser.windowsMobile||jQuery.browser.operaMobile||jQuery.browser.kindle,jQuery.isMobile=jQuery.browser.mobile,jQuery.isTablet=jQuery.browser.mobile&&765<jQuery(window).width(),jQuery.isAndroidDefault=jQuery.browser.android&&!/chrome/i.test(nAgt),jQuery.mbBrowser=jQuery.browser,jQuery.browser.versionCompare=function(e,r){if("stringstring"!=typeof e+typeof r)return!1;for(var t=e.split("."),a=r.split("."),o=0,n=Math.max(t.length,a.length);o<n;o++){if(t[o]&&!a[o]&&0<parseInt(t[o])||parseInt(t[o])>parseInt(a[o]))return 1;if(a[o]&&!t[o]&&0<parseInt(a[o])||parseInt(t[o])<parseInt(a[o]))return-1}return 0},jQuery.support.CSStransition=function(){var e=(document.body||document.documentElement).style;return void 0!==e.transition||void 0!==e.WebkitTransition||void 0!==e.MozTransition||void 0!==e.MsTransition||void 0!==e.OTransition}(),jQuery.CSS={name:"mb.CSSAnimate",author:"Matteo Bicocchi",version:"2.0.0",transitionEnd:"transitionEnd",sfx:"",filters:{blur:{min:0,max:100,unit:"px"},brightness:{min:0,max:400,unit:"%"},contrast:{min:0,max:400,unit:"%"},grayscale:{min:0,max:100,unit:"%"},hueRotate:{min:0,max:360,unit:"deg"},invert:{min:0,max:100,unit:"%"},saturate:{min:0,max:400,unit:"%"},sepia:{min:0,max:100,unit:"%"}},normalizeCss:function(e){var r=jQuery.extend(!0,{},e);for(var t in jQuery.browser.webkit||jQuery.browser.opera?jQuery.CSS.sfx="-webkit-":jQuery.browser.mozilla?jQuery.CSS.sfx="-moz-":jQuery.browser.msie&&(jQuery.CSS.sfx="-ms-"),jQuery.CSS.sfx="",r){if("transform"===t&&(r[jQuery.CSS.sfx+"transform"]=r[t],delete r[t]),"transform-origin"===t&&(r[jQuery.CSS.sfx+"transform-origin"]=e[t],delete r[t]),"filter"!==t||jQuery.browser.mozilla||(r[jQuery.CSS.sfx+"filter"]=e[t],delete r[t]),"blur"===t&&setFilter(r,"blur",e[t]),"brightness"===t&&setFilter(r,"brightness",e[t]),"contrast"===t&&setFilter(r,"contrast",e[t]),"grayscale"===t&&setFilter(r,"grayscale",e[t]),"hueRotate"===t&&setFilter(r,"hueRotate",e[t]),"invert"===t&&setFilter(r,"invert",e[t]),"saturate"===t&&setFilter(r,"saturate",e[t]),"sepia"===t&&setFilter(r,"sepia",e[t]),"x"===t){var a=jQuery.CSS.sfx+"transform";r[a]=r[a]||"",r[a]+=" translateX("+setUnit(e[t],"px")+")",delete r[t]}"y"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" translateY("+setUnit(e[t],"px")+")",delete r[t]),"z"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" translateZ("+setUnit(e[t],"px")+")",delete r[t]),"rotate"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" rotate("+setUnit(e[t],"deg")+")",delete r[t]),"rotateX"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" rotateX("+setUnit(e[t],"deg")+")",delete r[t]),"rotateY"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" rotateY("+setUnit(e[t],"deg")+")",delete r[t]),"rotateZ"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" rotateZ("+setUnit(e[t],"deg")+")",delete r[t]),"scale"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" scale("+setUnit(e[t],"")+")",delete r[t]),"scaleX"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" scaleX("+setUnit(e[t],"")+")",delete r[t]),"scaleY"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" scaleY("+setUnit(e[t],"")+")",delete r[t]),"scaleZ"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" scaleZ("+setUnit(e[t],"")+")",delete r[t]),"skew"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" skew("+setUnit(e[t],"deg")+")",delete r[t]),"skewX"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" skewX("+setUnit(e[t],"deg")+")",delete r[t]),"skewY"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" skewY("+setUnit(e[t],"deg")+")",delete r[t]),"perspective"===t&&(r[a=jQuery.CSS.sfx+"transform"]=r[a]||"",r[a]+=" perspective("+setUnit(e[t],"px")+")",delete r[t])}return r},getProp:function(e){var r,t=[];for(r in e)t.indexOf(r)<0&&t.push(uncamel(r));return t.join(",")},animate:function(l,s,u,y,d){return this.each(function(){function e(){r.called=!0,r.CSSAIsRunning=!1,t.off(jQuery.CSS.transitionEnd+"."+r.id),clearTimeout(r.timeout),t.css(jQuery.CSS.sfx+"transition",""),"function"==typeof d&&d.apply(r),"function"==typeof r.CSSqueue&&(r.CSSqueue(),r.CSSqueue=null)}var r=this,t=jQuery(this);r.id=r.id||"CSSA_"+(new Date).getTime();var a=a||{type:"noEvent"};if(r.CSSAIsRunning&&r.eventType==a.type&&!jQuery.browser.msie&&jQuery.browser.version<=9)r.CSSqueue=function(){t.CSSAnimate(l,s,u,y,d)};else if(r.CSSqueue=null,r.eventType=a.type,0!==t.length&&l){if(l=jQuery.normalizeCss(l),r.CSSAIsRunning=!0,"function"==typeof s&&(d=s,s=jQuery.fx.speeds._default),"function"==typeof u&&(y=u,u=0),"string"==typeof u&&(d=u,u=0),"function"==typeof y&&(d=y,y="cubic-bezier(0.65,0.03,0.36,0.72)"),"string"==typeof s)for(var o in jQuery.fx.speeds){if(s==o){s=jQuery.fx.speeds[o];break}s=jQuery.fx.speeds._default}if(s||(s=jQuery.fx.speeds._default),"string"==typeof d&&(y=d,d=null),jQuery.support.CSStransition){var n={default:"ease",in:"ease-in",out:"ease-out","in-out":"ease-in-out",snap:"cubic-bezier(0,1,.5,1)",easeOutCubic:"cubic-bezier(.215,.61,.355,1)",easeInOutCubic:"cubic-bezier(.645,.045,.355,1)",easeInCirc:"cubic-bezier(.6,.04,.98,.335)",easeOutCirc:"cubic-bezier(.075,.82,.165,1)",easeInOutCirc:"cubic-bezier(.785,.135,.15,.86)",easeInExpo:"cubic-bezier(.95,.05,.795,.035)",easeOutExpo:"cubic-bezier(.19,1,.22,1)",easeInOutExpo:"cubic-bezier(1,0,0,1)",easeInQuad:"cubic-bezier(.55,.085,.68,.53)",easeOutQuad:"cubic-bezier(.25,.46,.45,.94)",easeInOutQuad:"cubic-bezier(.455,.03,.515,.955)",easeInQuart:"cubic-bezier(.895,.03,.685,.22)",easeOutQuart:"cubic-bezier(.165,.84,.44,1)",easeInOutQuart:"cubic-bezier(.77,0,.175,1)",easeInQuint:"cubic-bezier(.755,.05,.855,.06)",easeOutQuint:"cubic-bezier(.23,1,.32,1)",easeInOutQuint:"cubic-bezier(.86,0,.07,1)",easeInSine:"cubic-bezier(.47,0,.745,.715)",easeOutSine:"cubic-bezier(.39,.575,.565,1)",easeInOutSine:"cubic-bezier(.445,.05,.55,.95)",easeInBack:"cubic-bezier(.6,-.28,.735,.045)",easeOutBack:"cubic-bezier(.175, .885,.32,1.275)",easeInOutBack:"cubic-bezier(.68,-.55,.265,1.55)"};n[y]&&(y=n[y]),t.off(jQuery.CSS.transitionEnd+"."+r.id),n=jQuery.CSS.getProp(l);var i={};jQuery.extend(i,l),i[jQuery.CSS.sfx+"transition-property"]=n,i[jQuery.CSS.sfx+"transition-duration"]=s+"ms",i[jQuery.CSS.sfx+"transition-delay"]=u+"ms",i[jQuery.CSS.sfx+"transition-timing-function"]=y,setTimeout(function(){t.one(jQuery.CSS.transitionEnd+"."+r.id,e),t.css(i)},1),r.timeout=setTimeout(function(){r.called||!d?(r.called=!1,r.CSSAIsRunning=!1):(t.css(jQuery.CSS.sfx+"transition",""),d.apply(r),r.CSSAIsRunning=!1,"function"==typeof r.CSSqueue&&(r.CSSqueue(),r.CSSqueue=null))},s+u+10)}else{for(n in l)"transform"===n&&delete l[n],"filter"===n&&delete l[n],"transform-origin"===n&&delete l[n],"auto"===l[n]&&delete l[n],"x"===n&&(a=l[n],l[o="left"]=a,delete l[n]),"y"===n&&(a=l[n],l[o="top"]=a,delete l[n]),"-ms-transform"!==n&&"-ms-filter"!==n||delete l[n];t.delay(u).animate(l,s,d)}}})}},jQuery.fn.CSSAnimate=jQuery.CSS.animate,jQuery.normalizeCss=jQuery.CSS.normalizeCss,jQuery.fn.css3=function(t){return this.each(function(){var e=jQuery(this),r=jQuery.normalizeCss(t);e.css(r)})};var nAgt=navigator.userAgent;function isTouchSupported(){var e=nAgt.msMaxTouchPoints,r="ontouchstart"in document.createElement("div");return!(!e&&!r)}jQuery.browser=jQuery.browser||{},jQuery.browser.mozilla=!1,jQuery.browser.webkit=!1,jQuery.browser.opera=!1,jQuery.browser.safari=!1,jQuery.browser.chrome=!1,jQuery.browser.androidStock=!1,jQuery.browser.msie=!1,jQuery.browser.edge=!1,jQuery.browser.ua=nAgt;var getOS=function(){var e={version:"Unknown version",name:"Unknown OS"};return-1!=navigator.appVersion.indexOf("Win")&&(e.name="Windows"),-1!=navigator.appVersion.indexOf("Mac")&&navigator.appVersion.indexOf("Mobile")<0&&(e.name="Mac"),-1!=navigator.appVersion.indexOf("Linux")&&(e.name="Linux"),/Mac OS X/.test(nAgt)&&!/Mobile/.test(nAgt)&&(e.version=/Mac OS X (10[\.\_\d]+)/.exec(nAgt)[1],e.version=e.version.replace(/_/g,".").substring(0,5)),/Windows/.test(nAgt)&&(e.version="Unknown.Unknown"),/Windows NT 5.1/.test(nAgt)&&(e.version="5.1"),/Windows NT 6.0/.test(nAgt)&&(e.version="6.0"),/Windows NT 6.1/.test(nAgt)&&(e.version="6.1"),/Windows NT 6.2/.test(nAgt)&&(e.version="6.2"),/Windows NT 10.0/.test(nAgt)&&(e.version="10.0"),/Linux/.test(nAgt)&&/Linux/.test(nAgt)&&(e.version="Unknown.Unknown"),e.name=e.name.toLowerCase(),e.major_version="Unknown",e.minor_version="Unknown","Unknown.Unknown"!=e.version&&(e.major_version=parseFloat(e.version.split(".")[0]),e.minor_version=parseFloat(e.version.split(".")[1])),e},nameOffset,verOffset,ix;if(jQuery.browser.os=getOS(),jQuery.browser.hasTouch=isTouchSupported(),jQuery.browser.name=navigator.appName,jQuery.browser.fullVersion=""+parseFloat(navigator.appVersion),jQuery.browser.majorVersion=parseInt(navigator.appVersion,10),-1!=(verOffset=nAgt.indexOf("Opera")))jQuery.browser.opera=!0,jQuery.browser.name="Opera",jQuery.browser.fullVersion=nAgt.substring(verOffset+6),-1!=(verOffset=nAgt.indexOf("Version"))&&(jQuery.browser.fullVersion=nAgt.substring(verOffset+8));else if(-1!=(verOffset=nAgt.indexOf("OPR")))jQuery.browser.opera=!0,jQuery.browser.name="Opera",jQuery.browser.fullVersion=nAgt.substring(verOffset+4);else if(-1!=(verOffset=nAgt.indexOf("MSIE")))jQuery.browser.msie=!0,jQuery.browser.name="Microsoft Internet Explorer",jQuery.browser.fullVersion=nAgt.substring(verOffset+5);else if(-1!=nAgt.indexOf("Trident")){jQuery.browser.msie=!0,jQuery.browser.name="Microsoft Internet Explorer";var start=nAgt.indexOf("rv:")+3,end=start+4;jQuery.browser.fullVersion=nAgt.substring(start,end)}else-1!=(verOffset=nAgt.indexOf("Edge"))?(jQuery.browser.edge=!0,jQuery.browser.name="Microsoft Edge",jQuery.browser.fullVersion=nAgt.substring(verOffset+5)):-1!=(verOffset=nAgt.indexOf("Chrome"))?(jQuery.browser.webkit=!0,jQuery.browser.chrome=!0,jQuery.browser.name="Chrome",jQuery.browser.fullVersion=nAgt.substring(verOffset+7)):-1<nAgt.indexOf("mozilla/5.0")&&-1<nAgt.indexOf("android ")&&-1<nAgt.indexOf("applewebkit")&&!(-1<nAgt.indexOf("chrome"))?(verOffset=nAgt.indexOf("Chrome"),jQuery.browser.webkit=!0,jQuery.browser.androidStock=!0,jQuery.browser.name="androidStock",jQuery.browser.fullVersion=nAgt.substring(verOffset+7)):-1!=(verOffset=nAgt.indexOf("Safari"))?(jQuery.browser.webkit=!0,jQuery.browser.safari=!0,jQuery.browser.name="Safari",jQuery.browser.fullVersion=nAgt.substring(verOffset+7),-1!=(verOffset=nAgt.indexOf("Version"))&&(jQuery.browser.fullVersion=nAgt.substring(verOffset+8))):-1!=(verOffset=nAgt.indexOf("AppleWebkit"))?(jQuery.browser.webkit=!0,jQuery.browser.safari=!0,jQuery.browser.name="Safari",jQuery.browser.fullVersion=nAgt.substring(verOffset+7),-1!=(verOffset=nAgt.indexOf("Version"))&&(jQuery.browser.fullVersion=nAgt.substring(verOffset+8))):-1!=(verOffset=nAgt.indexOf("Firefox"))?(jQuery.browser.mozilla=!0,jQuery.browser.name="Firefox",jQuery.browser.fullVersion=nAgt.substring(verOffset+8)):(nameOffset=nAgt.lastIndexOf(" ")+1)<(verOffset=nAgt.lastIndexOf("/"))&&(jQuery.browser.name=nAgt.substring(nameOffset,verOffset),jQuery.browser.fullVersion=nAgt.substring(verOffset+1),jQuery.browser.name.toLowerCase()==jQuery.browser.name.toUpperCase()&&(jQuery.browser.name=navigator.appName));-1!=(ix=jQuery.browser.fullVersion.indexOf(";"))&&(jQuery.browser.fullVersion=jQuery.browser.fullVersion.substring(0,ix)),-1!=(ix=jQuery.browser.fullVersion.indexOf(" "))&&(jQuery.browser.fullVersion=jQuery.browser.fullVersion.substring(0,ix)),jQuery.browser.majorVersion=parseInt(""+jQuery.browser.fullVersion,10),isNaN(jQuery.browser.majorVersion)&&(jQuery.browser.fullVersion=""+parseFloat(navigator.appVersion),jQuery.browser.majorVersion=parseInt(navigator.appVersion,10)),jQuery.browser.version=jQuery.browser.majorVersion,jQuery.browser.android=/Android/i.test(nAgt),jQuery.browser.blackberry=/BlackBerry|BB|PlayBook/i.test(nAgt),jQuery.browser.ios=/iPhone|iPad|iPod|webOS/i.test(nAgt),jQuery.browser.operaMobile=/Opera Mini/i.test(nAgt),jQuery.browser.windowsMobile=/IEMobile|Windows Phone/i.test(nAgt),jQuery.browser.kindle=/Kindle|Silk/i.test(nAgt),jQuery.browser.mobile=jQuery.browser.android||jQuery.browser.blackberry||jQuery.browser.ios||jQuery.browser.windowsMobile||jQuery.browser.operaMobile||jQuery.browser.kindle,jQuery.isMobile=jQuery.browser.mobile,jQuery.isTablet=jQuery.browser.mobile&&765<jQuery(window).width(),jQuery.isAndroidDefault=jQuery.browser.android&&!/chrome/i.test(nAgt),jQuery.mbBrowser=jQuery.browser,jQuery.browser.versionCompare=function(e,r){if("stringstring"!=typeof e+typeof r)return!1;for(var t=e.split("."),a=r.split("."),o=0,n=Math.max(t.length,a.length);o<n;o++){if(t[o]&&!a[o]&&0<parseInt(t[o])||parseInt(t[o])>parseInt(a[o]))return 1;if(a[o]&&!t[o]&&0<parseInt(a[o])||parseInt(t[o])<parseInt(a[o]))return-1}return 0},function(o){o.simpleSlider={defaults:{initialval:0,scale:100,orientation:"h",readonly:!1,callback:!1},events:{start:o.browser.mobile?"touchstart":"mousedown",end:o.browser.mobile?"touchend":"mouseup",move:o.browser.mobile?"touchmove":"mousemove"},init:function(a){return this.each(function(){var r=this,t=o(r);t.addClass("simpleSlider"),r.opt={},o.extend(r.opt,o.simpleSlider.defaults,a),o.extend(r.opt,t.data());var e="h"==r.opt.orientation?"horizontal":"vertical";e=o("<div/>").addClass("level").addClass(e),t.prepend(e),r.level=e,t.css({cursor:"default"}),"auto"==r.opt.scale&&(r.opt.scale=o(r).outerWidth()),t.updateSliderVal(),r.opt.readonly||(t.on(o.simpleSlider.events.start,function(e){o.browser.mobile&&(e=e.changedTouches[0]),r.canSlide=!0,t.updateSliderVal(e),"h"==r.opt.orientation?t.css({cursor:"col-resize"}):t.css({cursor:"row-resize"}),o.browser.mobile||(e.preventDefault(),e.stopPropagation())}),o(document).on(o.simpleSlider.events.move,function(e){o.browser.mobile&&(e=e.changedTouches[0]),r.canSlide&&(o(document).css({cursor:"default"}),t.updateSliderVal(e),o.browser.mobile||(e.preventDefault(),e.stopPropagation()))}).on(o.simpleSlider.events.end,function(){o(document).css({cursor:"auto"}),r.canSlide=!1,t.css({cursor:"auto"})}))})},updateSliderVal:function(e){var r=this.get(0);if(r.opt){r.opt.initialval="number"==typeof r.opt.initialval?r.opt.initialval:r.opt.initialval(r);var t=o(r).outerWidth(),a=o(r).outerHeight();r.x="object"==typeof e?e.clientX+document.body.scrollLeft-this.offset().left:"number"==typeof e?e*t/r.opt.scale:r.opt.initialval*t/r.opt.scale,r.y="object"==typeof e?e.clientY+document.body.scrollTop-this.offset().top:"number"==typeof e?(r.opt.scale-r.opt.initialval-e)*a/r.opt.scale:r.opt.initialval*a/r.opt.scale,r.y=this.outerHeight()-r.y,r.scaleX=r.x*r.opt.scale/t,r.scaleY=r.y*r.opt.scale/a,r.outOfRangeX=r.scaleX>r.opt.scale?r.scaleX-r.opt.scale:r.scaleX<0?r.scaleX:0,r.outOfRangeY=r.scaleY>r.opt.scale?r.scaleY-r.opt.scale:r.scaleY<0?r.scaleY:0,r.outOfRange="h"==r.opt.orientation?r.outOfRangeX:r.outOfRangeY,r.value=void 0!==e?"h"==r.opt.orientation?r.x>=this.outerWidth()?r.opt.scale:r.x<=0?0:r.scaleX:r.y>=this.outerHeight()?r.opt.scale:r.y<=0?0:r.scaleY:"h"==r.opt.orientation?r.scaleX:r.scaleY,"h"==r.opt.orientation?r.level.width(Math.floor(100*r.x/t)+"%"):r.level.height(Math.floor(100*r.y/a)),"function"==typeof r.opt.callback&&r.opt.callback(r)}}},o.fn.simpleSlider=o.simpleSlider.init,o.fn.updateSliderVal=o.simpleSlider.updateSliderVal}(jQuery),function(r){r.mbCookie={set:function(e,r,t,a){"object"==typeof r&&(r=JSON.stringify(r)),a=a?"; domain="+a:"";var o=new Date,n="";0<t&&(o.setTime(o.getTime()+864e5*t),n="; expires="+o.toGMTString()),document.cookie=e+"="+r+n+"; path=/"+a},get:function(r){r+="=";for(var e=document.cookie.split(";"),t=0;t<e.length;t++){for(var a=e[t];" "==a.charAt(0);)a=a.substring(1,a.length);if(0==a.indexOf(r))try{return JSON.parse(a.substring(r.length,a.length))}catch(e){return a.substring(r.length,a.length)}}return null},remove:function(e){r.mbCookie.set(e,"",-1)}},r.mbStorage={set:function(e,r){"object"==typeof r&&(r=JSON.stringify(r)),localStorage.setItem(e,r)},get:function(r){if(!localStorage[r])return null;try{return JSON.parse(localStorage[r])}catch(e){return localStorage[r]}},remove:function(e){e?localStorage.removeItem(e):localStorage.clear()}}}(jQuery);

/*!
 * JavaScript Cookie v2.2.0
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */
!function(e){var n;if("function"==typeof define&&define.amd&&(define(e),n=!0),"object"==typeof exports&&(module.exports=e(),n=!0),!n){var t=window.Cookies,o=window.Cookies=e();o.noConflict=function(){return window.Cookies=t,o}}}(function(){function e(){for(var e=0,n={};e<arguments.length;e++){var t=arguments[e];for(var o in t)n[o]=t[o]}return n}function n(e){return e.replace(/(%[0-9A-Z]{2})+/g,decodeURIComponent)}return function t(o){function r(){}function i(n,t,i){if("undefined"!=typeof document){"number"==typeof(i=e({path:"/"},r.defaults,i)).expires&&(i.expires=new Date(1*new Date+864e5*i.expires)),i.expires=i.expires?i.expires.toUTCString():"";try{var c=JSON.stringify(t);/^[\{\[]/.test(c)&&(t=c)}catch(e){}t=o.write?o.write(t,n):encodeURIComponent(String(t)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g,decodeURIComponent),n=encodeURIComponent(String(n)).replace(/%(23|24|26|2B|5E|60|7C)/g,decodeURIComponent).replace(/[\(\)]/g,escape);var f="";for(var u in i)i[u]&&(f+="; "+u,!0!==i[u]&&(f+="="+i[u].split(";")[0]));return document.cookie=n+"="+t+f}}function c(e,t){if("undefined"!=typeof document){for(var r={},i=document.cookie?document.cookie.split("; "):[],c=0;c<i.length;c++){var f=i[c].split("="),u=f.slice(1).join("=");t||'"'!==u.charAt(0)||(u=u.slice(1,-1));try{var a=n(f[0]);if(u=(o.read||o)(u,a)||n(u),t)try{u=JSON.parse(u)}catch(e){}if(r[a]=u,e===a)break}catch(e){}}return e?r[e]:r}}return r.set=i,r.get=function(e){return c(e,!1)},r.getJSON=function(e){return c(e,!0)},r.remove=function(n,t){i(n,"",e(t,{expires:-1}))},r.defaults={},r.withConverter=t,r}(function(){})});

/*
 * jQuery.appear
 * https://github.com/bas2k/jquery.appear/
 * http://code.google.com/p/jquery-appear/
 *
 * Copyright (c) 2009 Michael Hixson
 * Copyright (c) 2012 Alexander Brovikov
 * Licensed under the MIT license (http://www.opensource.org/licenses/mit-license.php)
 */
(function(e){e.fn.appear=function(t,n){var r=e.extend({data:undefined,one:true,accX:0,accY:0},n);return this.each(function(){var n=e(this);n.appeared=false;if(!t){n.trigger("appear",r.data);return}var i=e(window);var s=function(){if(!n.is(":visible")){n.appeared=false;return}var e=i.scrollLeft();var t=i.scrollTop();var s=n.offset();var o=s.left;var u=s.top;var a=r.accX;var f=r.accY;var l=n.height();var c=i.height();var h=n.width();var p=i.width();if(u+l+f>=t&&u<=t+c+f&&o+h+a>=e&&o<=e+p+a){if(!n.appeared)n.trigger("appear",r.data)}else{n.appeared=false}};var o=function(){n.appeared=true;if(r.one){i.unbind("scroll",s);var o=e.inArray(s,e.fn.appear.checks);if(o>=0)e.fn.appear.checks.splice(o,1)}t.apply(this,arguments)};if(r.one)n.one("appear",r.data,o);else n.bind("appear",r.data,o);i.scroll(s);e.fn.appear.checks.push(s);s()})};e.extend(e.fn.appear,{checks:[],timeout:null,checkAll:function(){var t=e.fn.appear.checks.length;if(t>0)while(t--)e.fn.appear.checks[t]()},run:function(){if(e.fn.appear.timeout)clearTimeout(e.fn.appear.timeout);e.fn.appear.timeout=setTimeout(e.fn.appear.checkAll,20)}});e.each(["append","prepend","after","before","attr","removeAttr","addClass","removeClass","toggleClass","remove","css","show","hide"],function(t,n){var r=e.fn[n];if(r){e.fn[n]=function(){var t=r.apply(this,arguments);e.fn.appear.run();return t}}})})(jQuery);

/*!
 * animsition v4.0.2
 * A simple and easy jQuery plugin for CSS animated page transitions.
 * http://blivesta.github.io/animsition
 * License : MIT
 * Author : blivesta (http://blivesta.com/)
 */
!function(t){"use strict";"function"==typeof define&&define.amd?define(["jquery"],t):"object"==typeof exports?module.exports=t(require("jquery")):t(jQuery)}(function(t){"use strict";var n=!1;t(window).on("load",function(){n=!0});var i="animsition",a={init:function(o){o=t.extend({inClass:"fade-in",outClass:"fade-out",inDuration:1500,outDuration:800,linkElement:".animsition-link",loading:!0,loadingParentElement:"body",loadingClass:"animsition-loading",loadingInner:"",timeout:!1,timeoutCountdown:5e3,onLoadEvent:!0,browser:["animation-duration","-webkit-animation-duration"],overlay:!1,overlayClass:"animsition-overlay-slide",overlayParentElement:"body",transition:function(t){window.location.href=t}},o),a.settings={timer:!1,data:{inClass:"animsition-in-class",inDuration:"animsition-in-duration",outClass:"animsition-out-class",outDuration:"animsition-out-duration",overlay:"animsition-overlay"},events:{inStart:"animsition.inStart",inEnd:"animsition.inEnd",outStart:"animsition.outStart",outEnd:"animsition.outEnd"}};var e=a.supportCheck.call(this,o);if(!e&&o.browser.length>0&&(!e||!this.length))return"console"in window||(window.console={},window.console.log=function(t){return t}),this.length||console.log("Animsition: Element does not exist on page."),e||console.log("Animsition: Does not support this browser."),a.destroy.call(this);var s=a.optionCheck.call(this,o);return s&&t("."+o.overlayClass).length<=0&&a.addOverlay.call(this,o),o.loading&&t("."+o.loadingClass).length<=0&&a.addLoading.call(this,o),this.each(function(){var e=this,s=t(this),r=t(window),l=t(document),d=s.data(i);d||(o=t.extend({},o),s.data(i,{options:o}),o.timeout&&a.addTimer.call(e),o.onLoadEvent&&(n?(a.settings.timer&&clearTimeout(a.settings.timer),a["in"].call(e)):r.on("load."+i,function(){a.settings.timer&&clearTimeout(a.settings.timer),a["in"].call(e)})),r.on("pageshow."+i,function(t){t.originalEvent.persisted&&a["in"].call(e)}),r.on("unload."+i,function(){}),l.on("click."+i,o.linkElement,function(n){n.preventDefault();var i=t(this),o=i.attr("href");2===n.which||n.metaKey||n.shiftKey||-1!==navigator.platform.toUpperCase().indexOf("WIN")&&n.ctrlKey?window.open(o,"_blank"):a.out.call(e,i,o)}))})},addOverlay:function(n){t(n.overlayParentElement).prepend('<div class="'+n.overlayClass+'"></div>')},addLoading:function(n){t(n.loadingParentElement).append('<div class="'+n.loadingClass+'">'+n.loadingInner+"</div>")},removeLoading:function(){var n=t(this),a=n.data(i).options,o=t(a.loadingParentElement).children("."+a.loadingClass);o.fadeOut().remove()},addTimer:function(){var n=this,o=t(this),e=o.data(i).options;a.settings.timer=setTimeout(function(){a["in"].call(n),t(window).off("load."+i)},e.timeoutCountdown)},supportCheck:function(n){var i=t(this),a=n.browser,o=a.length,e=!1;0===o&&(e=!0);for(var s=0;o>s;s++)if("string"==typeof i.css(a[s])){e=!0;break}return e},optionCheck:function(n){var i,o=t(this);return i=n.overlay||o.data(a.settings.data.overlay)?!0:!1},animationCheck:function(n,a,o){var e=t(this),s=e.data(i).options,r=typeof n,l=!a&&"number"===r,d=a&&"string"===r&&n.length>0;return l||d?n=n:a&&o?n=s.inClass:!a&&o?n=s.inDuration:a&&!o?n=s.outClass:a||o||(n=s.outDuration),n},"in":function(){var n=this,o=t(this),e=o.data(i).options,s=o.data(a.settings.data.inDuration),r=o.data(a.settings.data.inClass),l=a.animationCheck.call(n,s,!1,!0),d=a.animationCheck.call(n,r,!0,!0),u=a.optionCheck.call(n,e),c=o.data(i).outClass;e.loading&&a.removeLoading.call(n),c&&o.removeClass(c),u?a.inOverlay.call(n,d,l):a.inDefault.call(n,d,l)},inDefault:function(n,i){var o=t(this);o.css({"animation-duration":i+"ms"}).addClass(n).trigger(a.settings.events.inStart).animateCallback(function(){o.removeClass(n).css({opacity:1}).trigger(a.settings.events.inEnd)})},inOverlay:function(n,o){var e=t(this),s=e.data(i).options;e.css({opacity:1}).trigger(a.settings.events.inStart),t(s.overlayParentElement).children("."+s.overlayClass).css({"animation-duration":o+"ms"}).addClass(n).animateCallback(function(){e.trigger(a.settings.events.inEnd)})},out:function(n,o){var e=this,s=t(this),r=s.data(i).options,l=n.data(a.settings.data.outClass),d=s.data(a.settings.data.outClass),u=n.data(a.settings.data.outDuration),c=s.data(a.settings.data.outDuration),m=l?l:d,g=u?u:c,f=a.animationCheck.call(e,m,!0,!1),v=a.animationCheck.call(e,g,!1,!1),h=a.optionCheck.call(e,r);s.data(i).outClass=f,h?a.outOverlay.call(e,f,v,o):a.outDefault.call(e,f,v,o)},outDefault:function(n,o,e){var s=t(this),r=s.data(i).options;s.css({"animation-duration":o+1+"ms"}).addClass(n).trigger(a.settings.events.outStart).animateCallback(function(){s.trigger(a.settings.events.outEnd),r.transition(e)})},outOverlay:function(n,o,e){var s=this,r=t(this),l=r.data(i).options,d=r.data(a.settings.data.inClass),u=a.animationCheck.call(s,d,!0,!0);t(l.overlayParentElement).children("."+l.overlayClass).css({"animation-duration":o+1+"ms"}).removeClass(u).addClass(n).trigger(a.settings.events.outStart).animateCallback(function(){r.trigger(a.settings.events.outEnd),l.transition(e)})},destroy:function(){return this.each(function(){var n=t(this);t(window).off("."+i),n.css({opacity:1}).removeData(i)})}};t.fn.animateCallback=function(n){var i="animationend webkitAnimationEnd";return this.each(function(){var a=t(this);a.on(i,function(){return a.off(i),n.call(this)})})},t.fn.animsition=function(n){return a[n]?a[n].apply(this,Array.prototype.slice.call(arguments,1)):"object"!=typeof n&&n?void t.error("Method "+n+" does not exist on jQuery."+i):a.init.apply(this,arguments)}});

/*! skrollr 0.6.30 (2015-06-19) | Alexander Prinzhorn - https://github.com/Prinzhorn/skrollr | Free to use under terms of MIT license */
!function(a,b,c){"use strict";function d(c){if(e=b.documentElement,f=b.body,T(),ha=this,c=c||{},ma=c.constants||{},c.easing)for(var d in c.easing)W[d]=c.easing[d];ta=c.edgeStrategy||"set",ka={beforerender:c.beforerender,render:c.render,keyframe:c.keyframe},la=c.forceHeight!==!1,la&&(Ka=c.scale||1),na=c.mobileDeceleration||y,pa=c.smoothScrolling!==!1,qa=c.smoothScrollingDuration||A,ra={targetTop:ha.getScrollTop()},Sa=(c.mobileCheck||function(){return/Android|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent||navigator.vendor||a.opera)})(),Sa?(ja=b.getElementById(c.skrollrBody||z),ja&&ga(),X(),Ea(e,[s,v],[t])):Ea(e,[s,u],[t]),ha.refresh(),wa(a,"resize orientationchange",function(){var a=e.clientWidth,b=e.clientHeight;(b!==Pa||a!==Oa)&&(Pa=b,Oa=a,Qa=!0)});var g=U();return function h(){$(),va=g(h)}(),ha}var e,f,g={get:function(){return ha},init:function(a){return ha||new d(a)},VERSION:"0.6.29"},h=Object.prototype.hasOwnProperty,i=a.Math,j=a.getComputedStyle,k="touchstart",l="touchmove",m="touchcancel",n="touchend",o="skrollable",p=o+"-before",q=o+"-between",r=o+"-after",s="skrollr",t="no-"+s,u=s+"-desktop",v=s+"-mobile",w="linear",x=1e3,y=.004,z="skrollr-body",A=200,B="start",C="end",D="center",E="bottom",F="___skrollable_id",G=/^(?:input|textarea|button|select)$/i,H=/^\s+|\s+$/g,I=/^data(?:-(_\w+))?(?:-?(-?\d*\.?\d+p?))?(?:-?(start|end|top|center|bottom))?(?:-?(top|center|bottom))?$/,J=/\s*(@?[\w\-\[\]]+)\s*:\s*(.+?)\s*(?:;|$)/gi,K=/^(@?[a-z\-]+)\[(\w+)\]$/,L=/-([a-z0-9_])/g,M=function(a,b){return b.toUpperCase()},N=/[\-+]?[\d]*\.?[\d]+/g,O=/\{\?\}/g,P=/rgba?\(\s*-?\d+\s*,\s*-?\d+\s*,\s*-?\d+/g,Q=/[a-z\-]+-gradient/g,R="",S="",T=function(){var a=/^(?:O|Moz|webkit|ms)|(?:-(?:o|moz|webkit|ms)-)/;if(j){var b=j(f,null);for(var c in b)if(R=c.match(a)||+c==c&&b[c].match(a))break;if(!R)return void(R=S="");R=R[0],"-"===R.slice(0,1)?(S=R,R={"-webkit-":"webkit","-moz-":"Moz","-ms-":"ms","-o-":"O"}[R]):S="-"+R.toLowerCase()+"-"}},U=function(){var b=a.requestAnimationFrame||a[R.toLowerCase()+"RequestAnimationFrame"],c=Ha();return(Sa||!b)&&(b=function(b){var d=Ha()-c,e=i.max(0,1e3/60-d);return a.setTimeout(function(){c=Ha(),b()},e)}),b},V=function(){var b=a.cancelAnimationFrame||a[R.toLowerCase()+"CancelAnimationFrame"];return(Sa||!b)&&(b=function(b){return a.clearTimeout(b)}),b},W={begin:function(){return 0},end:function(){return 1},linear:function(a){return a},quadratic:function(a){return a*a},cubic:function(a){return a*a*a},swing:function(a){return-i.cos(a*i.PI)/2+.5},sqrt:function(a){return i.sqrt(a)},outCubic:function(a){return i.pow(a-1,3)+1},bounce:function(a){var b;if(.5083>=a)b=3;else if(.8489>=a)b=9;else if(.96208>=a)b=27;else{if(!(.99981>=a))return 1;b=91}return 1-i.abs(3*i.cos(a*b*1.028)/b)}};d.prototype.refresh=function(a){var d,e,f=!1;for(a===c?(f=!0,ia=[],Ra=0,a=b.getElementsByTagName("*")):a.length===c&&(a=[a]),d=0,e=a.length;e>d;d++){var g=a[d],h=g,i=[],j=pa,k=ta,l=!1;if(f&&F in g&&delete g[F],g.attributes){for(var m=0,n=g.attributes.length;n>m;m++){var p=g.attributes[m];if("data-anchor-target"!==p.name)if("data-smooth-scrolling"!==p.name)if("data-edge-strategy"!==p.name)if("data-emit-events"!==p.name){var q=p.name.match(I);if(null!==q){var r={props:p.value,element:g,eventType:p.name.replace(L,M)};i.push(r);var s=q[1];s&&(r.constant=s.substr(1));var t=q[2];/p$/.test(t)?(r.isPercentage=!0,r.offset=(0|t.slice(0,-1))/100):r.offset=0|t;var u=q[3],v=q[4]||u;u&&u!==B&&u!==C?(r.mode="relative",r.anchors=[u,v]):(r.mode="absolute",u===C?r.isEnd=!0:r.isPercentage||(r.offset=r.offset*Ka))}}else l=!0;else k=p.value;else j="off"!==p.value;else if(h=b.querySelector(p.value),null===h)throw'Unable to find anchor target "'+p.value+'"'}if(i.length){var w,x,y;!f&&F in g?(y=g[F],w=ia[y].styleAttr,x=ia[y].classAttr):(y=g[F]=Ra++,w=g.style.cssText,x=Da(g)),ia[y]={element:g,styleAttr:w,classAttr:x,anchorTarget:h,keyFrames:i,smoothScrolling:j,edgeStrategy:k,emitEvents:l,lastFrameIndex:-1},Ea(g,[o],[])}}}for(Aa(),d=0,e=a.length;e>d;d++){var z=ia[a[d][F]];z!==c&&(_(z),ba(z))}return ha},d.prototype.relativeToAbsolute=function(a,b,c){var d=e.clientHeight,f=a.getBoundingClientRect(),g=f.top,h=f.bottom-f.top;return b===E?g-=d:b===D&&(g-=d/2),c===E?g+=h:c===D&&(g+=h/2),g+=ha.getScrollTop(),g+.5|0},d.prototype.animateTo=function(a,b){b=b||{};var d=Ha(),e=ha.getScrollTop(),f=b.duration===c?x:b.duration;return oa={startTop:e,topDiff:a-e,targetTop:a,duration:f,startTime:d,endTime:d+f,easing:W[b.easing||w],done:b.done},oa.topDiff||(oa.done&&oa.done.call(ha,!1),oa=c),ha},d.prototype.stopAnimateTo=function(){oa&&oa.done&&oa.done.call(ha,!0),oa=c},d.prototype.isAnimatingTo=function(){return!!oa},d.prototype.isMobile=function(){return Sa},d.prototype.setScrollTop=function(b,c){return sa=c===!0,Sa?Ta=i.min(i.max(b,0),Ja):a.scrollTo(0,b),ha},d.prototype.getScrollTop=function(){return Sa?Ta:a.pageYOffset||e.scrollTop||f.scrollTop||0},d.prototype.getMaxScrollTop=function(){return Ja},d.prototype.on=function(a,b){return ka[a]=b,ha},d.prototype.off=function(a){return delete ka[a],ha},d.prototype.destroy=function(){var a=V();a(va),ya(),Ea(e,[t],[s,u,v]);for(var b=0,d=ia.length;d>b;b++)fa(ia[b].element);e.style.overflow=f.style.overflow="",e.style.height=f.style.height="",ja&&g.setStyle(ja,"transform","none"),ha=c,ja=c,ka=c,la=c,Ja=0,Ka=1,ma=c,na=c,La="down",Ma=-1,Oa=0,Pa=0,Qa=!1,oa=c,pa=c,qa=c,ra=c,sa=c,Ra=0,ta=c,Sa=!1,Ta=0,ua=c};var X=function(){var d,g,h,j,o,p,q,r,s,t,u,v;wa(e,[k,l,m,n].join(" "),function(a){var e=a.changedTouches[0];for(j=a.target;3===j.nodeType;)j=j.parentNode;switch(o=e.clientY,p=e.clientX,t=a.timeStamp,G.test(j.tagName)||a.preventDefault(),a.type){case k:d&&d.blur(),ha.stopAnimateTo(),d=j,g=q=o,h=p,s=t;break;case l:G.test(j.tagName)&&b.activeElement!==j&&a.preventDefault(),r=o-q,v=t-u,ha.setScrollTop(Ta-r,!0),q=o,u=t;break;default:case m:case n:var f=g-o,w=h-p,x=w*w+f*f;if(49>x){if(!G.test(d.tagName)){d.focus();var y=b.createEvent("MouseEvents");y.initMouseEvent("click",!0,!0,a.view,1,e.screenX,e.screenY,e.clientX,e.clientY,a.ctrlKey,a.altKey,a.shiftKey,a.metaKey,0,null),d.dispatchEvent(y)}return}d=c;var z=r/v;z=i.max(i.min(z,3),-3);var A=i.abs(z/na),B=z*A+.5*na*A*A,C=ha.getScrollTop()-B,D=0;C>Ja?(D=(Ja-C)/B,C=Ja):0>C&&(D=-C/B,C=0),A*=1-D,ha.animateTo(C+.5|0,{easing:"outCubic",duration:A})}}),a.scrollTo(0,0),e.style.overflow=f.style.overflow="hidden"},Y=function(){var a,b,c,d,f,g,h,j,k,l,m,n=e.clientHeight,o=Ba();for(j=0,k=ia.length;k>j;j++)for(a=ia[j],b=a.element,c=a.anchorTarget,d=a.keyFrames,f=0,g=d.length;g>f;f++)h=d[f],l=h.offset,m=o[h.constant]||0,h.frame=l,h.isPercentage&&(l*=n,h.frame=l),"relative"===h.mode&&(fa(b),h.frame=ha.relativeToAbsolute(c,h.anchors[0],h.anchors[1])-l,fa(b,!0)),h.frame+=m,la&&!h.isEnd&&h.frame>Ja&&(Ja=h.frame);for(Ja=i.max(Ja,Ca()),j=0,k=ia.length;k>j;j++){for(a=ia[j],d=a.keyFrames,f=0,g=d.length;g>f;f++)h=d[f],m=o[h.constant]||0,h.isEnd&&(h.frame=Ja-h.offset+m);a.keyFrames.sort(Ia)}},Z=function(a,b){for(var c=0,d=ia.length;d>c;c++){var e,f,i=ia[c],j=i.element,k=i.smoothScrolling?a:b,l=i.keyFrames,m=l.length,n=l[0],s=l[l.length-1],t=k<n.frame,u=k>s.frame,v=t?n:s,w=i.emitEvents,x=i.lastFrameIndex;if(t||u){if(t&&-1===i.edge||u&&1===i.edge)continue;switch(t?(Ea(j,[p],[r,q]),w&&x>-1&&(za(j,n.eventType,La),i.lastFrameIndex=-1)):(Ea(j,[r],[p,q]),w&&m>x&&(za(j,s.eventType,La),i.lastFrameIndex=m)),i.edge=t?-1:1,i.edgeStrategy){case"reset":fa(j);continue;case"ease":k=v.frame;break;default:case"set":var y=v.props;for(e in y)h.call(y,e)&&(f=ea(y[e].value),0===e.indexOf("@")?j.setAttribute(e.substr(1),f):g.setStyle(j,e,f));continue}}else 0!==i.edge&&(Ea(j,[o,q],[p,r]),i.edge=0);for(var z=0;m-1>z;z++)if(k>=l[z].frame&&k<=l[z+1].frame){var A=l[z],B=l[z+1];for(e in A.props)if(h.call(A.props,e)){var C=(k-A.frame)/(B.frame-A.frame);C=A.props[e].easing(C),f=da(A.props[e].value,B.props[e].value,C),f=ea(f),0===e.indexOf("@")?j.setAttribute(e.substr(1),f):g.setStyle(j,e,f)}w&&x!==z&&("down"===La?za(j,A.eventType,La):za(j,B.eventType,La),i.lastFrameIndex=z);break}}},$=function(){Qa&&(Qa=!1,Aa());var a,b,d=ha.getScrollTop(),e=Ha();if(oa)e>=oa.endTime?(d=oa.targetTop,a=oa.done,oa=c):(b=oa.easing((e-oa.startTime)/oa.duration),d=oa.startTop+b*oa.topDiff|0),ha.setScrollTop(d,!0);else if(!sa){var f=ra.targetTop-d;f&&(ra={startTop:Ma,topDiff:d-Ma,targetTop:d,startTime:Na,endTime:Na+qa}),e<=ra.endTime&&(b=W.sqrt((e-ra.startTime)/qa),d=ra.startTop+b*ra.topDiff|0)}if(sa||Ma!==d){La=d>Ma?"down":Ma>d?"up":La,sa=!1;var h={curTop:d,lastTop:Ma,maxTop:Ja,direction:La},i=ka.beforerender&&ka.beforerender.call(ha,h);i!==!1&&(Z(d,ha.getScrollTop()),Sa&&ja&&g.setStyle(ja,"transform","translate(0, "+-Ta+"px) "+ua),Ma=d,ka.render&&ka.render.call(ha,h)),a&&a.call(ha,!1)}Na=e},_=function(a){for(var b=0,c=a.keyFrames.length;c>b;b++){for(var d,e,f,g,h=a.keyFrames[b],i={};null!==(g=J.exec(h.props));)f=g[1],e=g[2],d=f.match(K),null!==d?(f=d[1],d=d[2]):d=w,e=e.indexOf("!")?aa(e):[e.slice(1)],i[f]={value:e,easing:W[d]};h.props=i}},aa=function(a){var b=[];return P.lastIndex=0,a=a.replace(P,function(a){return a.replace(N,function(a){return a/255*100+"%"})}),S&&(Q.lastIndex=0,a=a.replace(Q,function(a){return S+a})),a=a.replace(N,function(a){return b.push(+a),"{?}"}),b.unshift(a),b},ba=function(a){var b,c,d={};for(b=0,c=a.keyFrames.length;c>b;b++)ca(a.keyFrames[b],d);for(d={},b=a.keyFrames.length-1;b>=0;b--)ca(a.keyFrames[b],d)},ca=function(a,b){var c;for(c in b)h.call(a.props,c)||(a.props[c]=b[c]);for(c in a.props)b[c]=a.props[c]},da=function(a,b,c){var d,e=a.length;if(e!==b.length)throw"Can't interpolate between \""+a[0]+'" and "'+b[0]+'"';var f=[a[0]];for(d=1;e>d;d++)f[d]=a[d]+(b[d]-a[d])*c;return f},ea=function(a){var b=1;return O.lastIndex=0,a[0].replace(O,function(){return a[b++]})},fa=function(a,b){a=[].concat(a);for(var c,d,e=0,f=a.length;f>e;e++)d=a[e],c=ia[d[F]],c&&(b?(d.style.cssText=c.dirtyStyleAttr,Ea(d,c.dirtyClassAttr)):(c.dirtyStyleAttr=d.style.cssText,c.dirtyClassAttr=Da(d),d.style.cssText=c.styleAttr,Ea(d,c.classAttr)))},ga=function(){ua="translateZ(0)",g.setStyle(ja,"transform",ua);var a=j(ja),b=a.getPropertyValue("transform"),c=a.getPropertyValue(S+"transform"),d=b&&"none"!==b||c&&"none"!==c;d||(ua="")};g.setStyle=function(a,b,c){var d=a.style;if(b=b.replace(L,M).replace("-",""),"zIndex"===b)isNaN(c)?d[b]=c:d[b]=""+(0|c);else if("float"===b)d.styleFloat=d.cssFloat=c;else try{R&&(d[R+b.slice(0,1).toUpperCase()+b.slice(1)]=c),d[b]=c}catch(e){}};var ha,ia,ja,ka,la,ma,na,oa,pa,qa,ra,sa,ta,ua,va,wa=g.addEvent=function(b,c,d){var e=function(b){return b=b||a.event,b.target||(b.target=b.srcElement),b.preventDefault||(b.preventDefault=function(){b.returnValue=!1,b.defaultPrevented=!0}),d.call(this,b)};c=c.split(" ");for(var f,g=0,h=c.length;h>g;g++)f=c[g],b.addEventListener?b.addEventListener(f,d,!1):b.attachEvent("on"+f,e),Ua.push({element:b,name:f,listener:d})},xa=g.removeEvent=function(a,b,c){b=b.split(" ");for(var d=0,e=b.length;e>d;d++)a.removeEventListener?a.removeEventListener(b[d],c,!1):a.detachEvent("on"+b[d],c)},ya=function(){for(var a,b=0,c=Ua.length;c>b;b++)a=Ua[b],xa(a.element,a.name,a.listener);Ua=[]},za=function(a,b,c){ka.keyframe&&ka.keyframe.call(ha,a,b,c)},Aa=function(){var a=ha.getScrollTop();Ja=0,la&&!Sa&&(f.style.height=""),Y(),la&&!Sa&&(f.style.height=Ja+e.clientHeight+"px"),Sa?ha.setScrollTop(i.min(ha.getScrollTop(),Ja)):ha.setScrollTop(a,!0),sa=!0},Ba=function(){var a,b,c=e.clientHeight,d={};for(a in ma)b=ma[a],"function"==typeof b?b=b.call(ha):/p$/.test(b)&&(b=b.slice(0,-1)/100*c),d[a]=b;return d},Ca=function(){var a,b=0;return ja&&(b=i.max(ja.offsetHeight,ja.scrollHeight)),a=i.max(b,f.scrollHeight,f.offsetHeight,e.scrollHeight,e.offsetHeight,e.clientHeight),a-e.clientHeight},Da=function(b){var c="className";return a.SVGElement&&b instanceof a.SVGElement&&(b=b[c],c="baseVal"),b[c]},Ea=function(b,d,e){var f="className";if(a.SVGElement&&b instanceof a.SVGElement&&(b=b[f],f="baseVal"),e===c)return void(b[f]=d);for(var g=b[f],h=0,i=e.length;i>h;h++)g=Ga(g).replace(Ga(e[h])," ");g=Fa(g);for(var j=0,k=d.length;k>j;j++)-1===Ga(g).indexOf(Ga(d[j]))&&(g+=" "+d[j]);b[f]=Fa(g)},Fa=function(a){return a.replace(H,"")},Ga=function(a){return" "+a+" "},Ha=Date.now||function(){return+new Date},Ia=function(a,b){return a.frame-b.frame},Ja=0,Ka=1,La="down",Ma=-1,Na=Ha(),Oa=0,Pa=0,Qa=!1,Ra=0,Sa=!1,Ta=0,Ua=[];"function"==typeof define&&define.amd?define([],function(){return g}):"undefined"!=typeof module&&module.exports?module.exports=g:a.skrollr=g}(window,document);

/**
 * Owl Carousel v2.3.4
 * Copyright 2013-2018 David Deutsch
 * Licensed under: SEE LICENSE IN https://github.com/OwlCarousel2/OwlCarousel2/blob/master/LICENSE
 */
!function(a,b,c,d){function e(b,c){this.settings=null,this.options=a.extend({},e.Defaults,c),this.$element=a(b),this._handlers={},this._plugins={},this._supress={},this._current=null,this._speed=null,this._coordinates=[],this._breakpoint=null,this._width=null,this._items=[],this._clones=[],this._mergers=[],this._widths=[],this._invalidated={},this._pipe=[],this._drag={time:null,target:null,pointer:null,stage:{start:null,current:null},direction:null},this._states={current:{},tags:{initializing:["busy"],animating:["busy"],dragging:["interacting"]}},a.each(["onResize","onThrottledResize"],a.proxy(function(b,c){this._handlers[c]=a.proxy(this[c],this)},this)),a.each(e.Plugins,a.proxy(function(a,b){this._plugins[a.charAt(0).toLowerCase()+a.slice(1)]=new b(this)},this)),a.each(e.Workers,a.proxy(function(b,c){this._pipe.push({filter:c.filter,run:a.proxy(c.run,this)})},this)),this.setup(),this.initialize()}e.Defaults={items:3,loop:!1,center:!1,rewind:!1,checkVisibility:!0,mouseDrag:!0,touchDrag:!0,pullDrag:!0,freeDrag:!1,margin:0,stagePadding:0,merge:!1,mergeFit:!0,autoWidth:!1,startPosition:0,rtl:!1,smartSpeed:250,fluidSpeed:!1,dragEndSpeed:!1,responsive:{},responsiveRefreshRate:200,responsiveBaseElement:b,fallbackEasing:"swing",slideTransition:"",info:!1,nestedItemSelector:!1,itemElement:"div",stageElement:"div",refreshClass:"owl-refresh",loadedClass:"owl-loaded",loadingClass:"owl-loading",rtlClass:"owl-rtl",responsiveClass:"owl-responsive",dragClass:"owl-drag",itemClass:"owl-item",stageClass:"owl-stage",stageOuterClass:"owl-stage-outer",grabClass:"owl-grab"},e.Width={Default:"default",Inner:"inner",Outer:"outer"},e.Type={Event:"event",State:"state"},e.Plugins={},e.Workers=[{filter:["width","settings"],run:function(){this._width=this.$element.width()}},{filter:["width","items","settings"],run:function(a){a.current=this._items&&this._items[this.relative(this._current)]}},{filter:["items","settings"],run:function(){this.$stage.children(".cloned").remove()}},{filter:["width","items","settings"],run:function(a){var b=this.settings.margin||"",c=!this.settings.autoWidth,d=this.settings.rtl,e={width:"auto","margin-left":d?b:"","margin-right":d?"":b};!c&&this.$stage.children().css(e),a.css=e}},{filter:["width","items","settings"],run:function(a){var b=(this.width()/this.settings.items).toFixed(3)-this.settings.margin,c=null,d=this._items.length,e=!this.settings.autoWidth,f=[];for(a.items={merge:!1,width:b};d--;)c=this._mergers[d],c=this.settings.mergeFit&&Math.min(c,this.settings.items)||c,a.items.merge=c>1||a.items.merge,f[d]=e?b*c:this._items[d].width();this._widths=f}},{filter:["items","settings"],run:function(){var b=[],c=this._items,d=this.settings,e=Math.max(2*d.items,4),f=2*Math.ceil(c.length/2),g=d.loop&&c.length?d.rewind?e:Math.max(e,f):0,h="",i="";for(g/=2;g>0;)b.push(this.normalize(b.length/2,!0)),h+=c[b[b.length-1]][0].outerHTML,b.push(this.normalize(c.length-1-(b.length-1)/2,!0)),i=c[b[b.length-1]][0].outerHTML+i,g-=1;this._clones=b,a(h).addClass("cloned").appendTo(this.$stage),a(i).addClass("cloned").prependTo(this.$stage)}},{filter:["width","items","settings"],run:function(){for(var a=this.settings.rtl?1:-1,b=this._clones.length+this._items.length,c=-1,d=0,e=0,f=[];++c<b;)d=f[c-1]||0,e=this._widths[this.relative(c)]+this.settings.margin,f.push(d+e*a);this._coordinates=f}},{filter:["width","items","settings"],run:function(){var a=this.settings.stagePadding,b=this._coordinates,c={width:Math.ceil(Math.abs(b[b.length-1]))+2*a,"padding-left":a||"","padding-right":a||""};this.$stage.css(c)}},{filter:["width","items","settings"],run:function(a){var b=this._coordinates.length,c=!this.settings.autoWidth,d=this.$stage.children();if(c&&a.items.merge)for(;b--;)a.css.width=this._widths[this.relative(b)],d.eq(b).css(a.css);else c&&(a.css.width=a.items.width,d.css(a.css))}},{filter:["items"],run:function(){this._coordinates.length<1&&this.$stage.removeAttr("style")}},{filter:["width","items","settings"],run:function(a){a.current=a.current?this.$stage.children().index(a.current):0,a.current=Math.max(this.minimum(),Math.min(this.maximum(),a.current)),this.reset(a.current)}},{filter:["position"],run:function(){this.animate(this.coordinates(this._current))}},{filter:["width","position","items","settings"],run:function(){var a,b,c,d,e=this.settings.rtl?1:-1,f=2*this.settings.stagePadding,g=this.coordinates(this.current())+f,h=g+this.width()*e,i=[];for(c=0,d=this._coordinates.length;c<d;c++)a=this._coordinates[c-1]||0,b=Math.abs(this._coordinates[c])+f*e,(this.op(a,"<=",g)&&this.op(a,">",h)||this.op(b,"<",g)&&this.op(b,">",h))&&i.push(c);this.$stage.children(".active").removeClass("active"),this.$stage.children(":eq("+i.join("), :eq(")+")").addClass("active"),this.$stage.children(".center").removeClass("center"),this.settings.center&&this.$stage.children().eq(this.current()).addClass("center")}}],e.prototype.initializeStage=function(){this.$stage=this.$element.find("."+this.settings.stageClass),this.$stage.length||(this.$element.addClass(this.options.loadingClass),this.$stage=a("<"+this.settings.stageElement+">",{class:this.settings.stageClass}).wrap(a("<div/>",{class:this.settings.stageOuterClass})),this.$element.append(this.$stage.parent()))},e.prototype.initializeItems=function(){var b=this.$element.find(".owl-item");if(b.length)return this._items=b.get().map(function(b){return a(b)}),this._mergers=this._items.map(function(){return 1}),void this.refresh();this.replace(this.$element.children().not(this.$stage.parent())),this.isVisible()?this.refresh():this.invalidate("width"),this.$element.removeClass(this.options.loadingClass).addClass(this.options.loadedClass)},e.prototype.initialize=function(){if(this.enter("initializing"),this.trigger("initialize"),this.$element.toggleClass(this.settings.rtlClass,this.settings.rtl),this.settings.autoWidth&&!this.is("pre-loading")){var a,b,c;a=this.$element.find("img"),b=this.settings.nestedItemSelector?"."+this.settings.nestedItemSelector:d,c=this.$element.children(b).width(),a.length&&c<=0&&this.preloadAutoWidthImages(a)}this.initializeStage(),this.initializeItems(),this.registerEventHandlers(),this.leave("initializing"),this.trigger("initialized")},e.prototype.isVisible=function(){return!this.settings.checkVisibility||this.$element.is(":visible")},e.prototype.setup=function(){var b=this.viewport(),c=this.options.responsive,d=-1,e=null;c?(a.each(c,function(a){a<=b&&a>d&&(d=Number(a))}),e=a.extend({},this.options,c[d]),"function"==typeof e.stagePadding&&(e.stagePadding=e.stagePadding()),delete e.responsive,e.responsiveClass&&this.$element.attr("class",this.$element.attr("class").replace(new RegExp("("+this.options.responsiveClass+"-)\\S+\\s","g"),"$1"+d))):e=a.extend({},this.options),this.trigger("change",{property:{name:"settings",value:e}}),this._breakpoint=d,this.settings=e,this.invalidate("settings"),this.trigger("changed",{property:{name:"settings",value:this.settings}})},e.prototype.optionsLogic=function(){this.settings.autoWidth&&(this.settings.stagePadding=!1,this.settings.merge=!1)},e.prototype.prepare=function(b){var c=this.trigger("prepare",{content:b});return c.data||(c.data=a("<"+this.settings.itemElement+"/>").addClass(this.options.itemClass).append(b)),this.trigger("prepared",{content:c.data}),c.data},e.prototype.update=function(){for(var b=0,c=this._pipe.length,d=a.proxy(function(a){return this[a]},this._invalidated),e={};b<c;)(this._invalidated.all||a.grep(this._pipe[b].filter,d).length>0)&&this._pipe[b].run(e),b++;this._invalidated={},!this.is("valid")&&this.enter("valid")},e.prototype.width=function(a){switch(a=a||e.Width.Default){case e.Width.Inner:case e.Width.Outer:return this._width;default:return this._width-2*this.settings.stagePadding+this.settings.margin}},e.prototype.refresh=function(){this.enter("refreshing"),this.trigger("refresh"),this.setup(),this.optionsLogic(),this.$element.addClass(this.options.refreshClass),this.update(),this.$element.removeClass(this.options.refreshClass),this.leave("refreshing"),this.trigger("refreshed")},e.prototype.onThrottledResize=function(){b.clearTimeout(this.resizeTimer),this.resizeTimer=b.setTimeout(this._handlers.onResize,this.settings.responsiveRefreshRate)},e.prototype.onResize=function(){return!!this._items.length&&(this._width!==this.$element.width()&&(!!this.isVisible()&&(this.enter("resizing"),this.trigger("resize").isDefaultPrevented()?(this.leave("resizing"),!1):(this.invalidate("width"),this.refresh(),this.leave("resizing"),void this.trigger("resized")))))},e.prototype.registerEventHandlers=function(){a.support.transition&&this.$stage.on(a.support.transition.end+".owl.core",a.proxy(this.onTransitionEnd,this)),!1!==this.settings.responsive&&this.on(b,"resize",this._handlers.onThrottledResize),this.settings.mouseDrag&&(this.$element.addClass(this.options.dragClass),this.$stage.on("mousedown.owl.core",a.proxy(this.onDragStart,this)),this.$stage.on("dragstart.owl.core selectstart.owl.core",function(){return!1})),this.settings.touchDrag&&(this.$stage.on("touchstart.owl.core",a.proxy(this.onDragStart,this)),this.$stage.on("touchcancel.owl.core",a.proxy(this.onDragEnd,this)))},e.prototype.onDragStart=function(b){var d=null;3!==b.which&&(a.support.transform?(d=this.$stage.css("transform").replace(/.*\(|\)| /g,"").split(","),d={x:d[16===d.length?12:4],y:d[16===d.length?13:5]}):(d=this.$stage.position(),d={x:this.settings.rtl?d.left+this.$stage.width()-this.width()+this.settings.margin:d.left,y:d.top}),this.is("animating")&&(a.support.transform?this.animate(d.x):this.$stage.stop(),this.invalidate("position")),this.$element.toggleClass(this.options.grabClass,"mousedown"===b.type),this.speed(0),this._drag.time=(new Date).getTime(),this._drag.target=a(b.target),this._drag.stage.start=d,this._drag.stage.current=d,this._drag.pointer=this.pointer(b),a(c).on("mouseup.owl.core touchend.owl.core",a.proxy(this.onDragEnd,this)),a(c).one("mousemove.owl.core touchmove.owl.core",a.proxy(function(b){var d=this.difference(this._drag.pointer,this.pointer(b));a(c).on("mousemove.owl.core touchmove.owl.core",a.proxy(this.onDragMove,this)),Math.abs(d.x)<Math.abs(d.y)&&this.is("valid")||(b.preventDefault(),this.enter("dragging"),this.trigger("drag"))},this)))},e.prototype.onDragMove=function(a){var b=null,c=null,d=null,e=this.difference(this._drag.pointer,this.pointer(a)),f=this.difference(this._drag.stage.start,e);this.is("dragging")&&(a.preventDefault(),this.settings.loop?(b=this.coordinates(this.minimum()),c=this.coordinates(this.maximum()+1)-b,f.x=((f.x-b)%c+c)%c+b):(b=this.settings.rtl?this.coordinates(this.maximum()):this.coordinates(this.minimum()),c=this.settings.rtl?this.coordinates(this.minimum()):this.coordinates(this.maximum()),d=this.settings.pullDrag?-1*e.x/5:0,f.x=Math.max(Math.min(f.x,b+d),c+d)),this._drag.stage.current=f,this.animate(f.x))},e.prototype.onDragEnd=function(b){var d=this.difference(this._drag.pointer,this.pointer(b)),e=this._drag.stage.current,f=d.x>0^this.settings.rtl?"left":"right";a(c).off(".owl.core"),this.$element.removeClass(this.options.grabClass),(0!==d.x&&this.is("dragging")||!this.is("valid"))&&(this.speed(this.settings.dragEndSpeed||this.settings.smartSpeed),this.current(this.closest(e.x,0!==d.x?f:this._drag.direction)),this.invalidate("position"),this.update(),this._drag.direction=f,(Math.abs(d.x)>3||(new Date).getTime()-this._drag.time>300)&&this._drag.target.one("click.owl.core",function(){return!1})),this.is("dragging")&&(this.leave("dragging"),this.trigger("dragged"))},e.prototype.closest=function(b,c){var e=-1,f=30,g=this.width(),h=this.coordinates();return this.settings.freeDrag||a.each(h,a.proxy(function(a,i){return"left"===c&&b>i-f&&b<i+f?e=a:"right"===c&&b>i-g-f&&b<i-g+f?e=a+1:this.op(b,"<",i)&&this.op(b,">",h[a+1]!==d?h[a+1]:i-g)&&(e="left"===c?a+1:a),-1===e},this)),this.settings.loop||(this.op(b,">",h[this.minimum()])?e=b=this.minimum():this.op(b,"<",h[this.maximum()])&&(e=b=this.maximum())),e},e.prototype.animate=function(b){var c=this.speed()>0;this.is("animating")&&this.onTransitionEnd(),c&&(this.enter("animating"),this.trigger("translate")),a.support.transform3d&&a.support.transition?this.$stage.css({transform:"translate3d("+b+"px,0px,0px)",transition:this.speed()/1e3+"s"+(this.settings.slideTransition?" "+this.settings.slideTransition:"")}):c?this.$stage.animate({left:b+"px"},this.speed(),this.settings.fallbackEasing,a.proxy(this.onTransitionEnd,this)):this.$stage.css({left:b+"px"})},e.prototype.is=function(a){return this._states.current[a]&&this._states.current[a]>0},e.prototype.current=function(a){if(a===d)return this._current;if(0===this._items.length)return d;if(a=this.normalize(a),this._current!==a){var b=this.trigger("change",{property:{name:"position",value:a}});b.data!==d&&(a=this.normalize(b.data)),this._current=a,this.invalidate("position"),this.trigger("changed",{property:{name:"position",value:this._current}})}return this._current},e.prototype.invalidate=function(b){return"string"===a.type(b)&&(this._invalidated[b]=!0,this.is("valid")&&this.leave("valid")),a.map(this._invalidated,function(a,b){return b})},e.prototype.reset=function(a){(a=this.normalize(a))!==d&&(this._speed=0,this._current=a,this.suppress(["translate","translated"]),this.animate(this.coordinates(a)),this.release(["translate","translated"]))},e.prototype.normalize=function(a,b){var c=this._items.length,e=b?0:this._clones.length;return!this.isNumeric(a)||c<1?a=d:(a<0||a>=c+e)&&(a=((a-e/2)%c+c)%c+e/2),a},e.prototype.relative=function(a){return a-=this._clones.length/2,this.normalize(a,!0)},e.prototype.maximum=function(a){var b,c,d,e=this.settings,f=this._coordinates.length;if(e.loop)f=this._clones.length/2+this._items.length-1;else if(e.autoWidth||e.merge){if(b=this._items.length)for(c=this._items[--b].width(),d=this.$element.width();b--&&!((c+=this._items[b].width()+this.settings.margin)>d););f=b+1}else f=e.center?this._items.length-1:this._items.length-e.items;return a&&(f-=this._clones.length/2),Math.max(f,0)},e.prototype.minimum=function(a){return a?0:this._clones.length/2},e.prototype.items=function(a){return a===d?this._items.slice():(a=this.normalize(a,!0),this._items[a])},e.prototype.mergers=function(a){return a===d?this._mergers.slice():(a=this.normalize(a,!0),this._mergers[a])},e.prototype.clones=function(b){var c=this._clones.length/2,e=c+this._items.length,f=function(a){return a%2==0?e+a/2:c-(a+1)/2};return b===d?a.map(this._clones,function(a,b){return f(b)}):a.map(this._clones,function(a,c){return a===b?f(c):null})},e.prototype.speed=function(a){return a!==d&&(this._speed=a),this._speed},e.prototype.coordinates=function(b){var c,e=1,f=b-1;return b===d?a.map(this._coordinates,a.proxy(function(a,b){return this.coordinates(b)},this)):(this.settings.center?(this.settings.rtl&&(e=-1,f=b+1),c=this._coordinates[b],c+=(this.width()-c+(this._coordinates[f]||0))/2*e):c=this._coordinates[f]||0,c=Math.ceil(c))},e.prototype.duration=function(a,b,c){return 0===c?0:Math.min(Math.max(Math.abs(b-a),1),6)*Math.abs(c||this.settings.smartSpeed)},e.prototype.to=function(a,b){var c=this.current(),d=null,e=a-this.relative(c),f=(e>0)-(e<0),g=this._items.length,h=this.minimum(),i=this.maximum();this.settings.loop?(!this.settings.rewind&&Math.abs(e)>g/2&&(e+=-1*f*g),a=c+e,(d=((a-h)%g+g)%g+h)!==a&&d-e<=i&&d-e>0&&(c=d-e,a=d,this.reset(c))):this.settings.rewind?(i+=1,a=(a%i+i)%i):a=Math.max(h,Math.min(i,a)),this.speed(this.duration(c,a,b)),this.current(a),this.isVisible()&&this.update()},e.prototype.next=function(a){a=a||!1,this.to(this.relative(this.current())+1,a)},e.prototype.prev=function(a){a=a||!1,this.to(this.relative(this.current())-1,a)},e.prototype.onTransitionEnd=function(a){if(a!==d&&(a.stopPropagation(),(a.target||a.srcElement||a.originalTarget)!==this.$stage.get(0)))return!1;this.leave("animating"),this.trigger("translated")},e.prototype.viewport=function(){var d;return this.options.responsiveBaseElement!==b?d=a(this.options.responsiveBaseElement).width():b.innerWidth?d=b.innerWidth:c.documentElement&&c.documentElement.clientWidth?d=c.documentElement.clientWidth:console.warn("Can not detect viewport width."),d},e.prototype.replace=function(b){this.$stage.empty(),this._items=[],b&&(b=b instanceof jQuery?b:a(b)),this.settings.nestedItemSelector&&(b=b.find("."+this.settings.nestedItemSelector)),b.filter(function(){return 1===this.nodeType}).each(a.proxy(function(a,b){b=this.prepare(b),this.$stage.append(b),this._items.push(b),this._mergers.push(1*b.find("[data-merge]").addBack("[data-merge]").attr("data-merge")||1)},this)),this.reset(this.isNumeric(this.settings.startPosition)?this.settings.startPosition:0),this.invalidate("items")},e.prototype.add=function(b,c){var e=this.relative(this._current);c=c===d?this._items.length:this.normalize(c,!0),b=b instanceof jQuery?b:a(b),this.trigger("add",{content:b,position:c}),b=this.prepare(b),0===this._items.length||c===this._items.length?(0===this._items.length&&this.$stage.append(b),0!==this._items.length&&this._items[c-1].after(b),this._items.push(b),this._mergers.push(1*b.find("[data-merge]").addBack("[data-merge]").attr("data-merge")||1)):(this._items[c].before(b),this._items.splice(c,0,b),this._mergers.splice(c,0,1*b.find("[data-merge]").addBack("[data-merge]").attr("data-merge")||1)),this._items[e]&&this.reset(this._items[e].index()),this.invalidate("items"),this.trigger("added",{content:b,position:c})},e.prototype.remove=function(a){(a=this.normalize(a,!0))!==d&&(this.trigger("remove",{content:this._items[a],position:a}),this._items[a].remove(),this._items.splice(a,1),this._mergers.splice(a,1),this.invalidate("items"),this.trigger("removed",{content:null,position:a}))},e.prototype.preloadAutoWidthImages=function(b){b.each(a.proxy(function(b,c){this.enter("pre-loading"),c=a(c),a(new Image).one("load",a.proxy(function(a){c.attr("src",a.target.src),c.css("opacity",1),this.leave("pre-loading"),!this.is("pre-loading")&&!this.is("initializing")&&this.refresh()},this)).attr("src",c.attr("src")||c.attr("data-src")||c.attr("data-src-retina"))},this))},e.prototype.destroy=function(){this.$element.off(".owl.core"),this.$stage.off(".owl.core"),a(c).off(".owl.core"),!1!==this.settings.responsive&&(b.clearTimeout(this.resizeTimer),this.off(b,"resize",this._handlers.onThrottledResize));for(var d in this._plugins)this._plugins[d].destroy();this.$stage.children(".cloned").remove(),this.$stage.unwrap(),this.$stage.children().contents().unwrap(),this.$stage.children().unwrap(),this.$stage.remove(),this.$element.removeClass(this.options.refreshClass).removeClass(this.options.loadingClass).removeClass(this.options.loadedClass).removeClass(this.options.rtlClass).removeClass(this.options.dragClass).removeClass(this.options.grabClass).attr("class",this.$element.attr("class").replace(new RegExp(this.options.responsiveClass+"-\\S+\\s","g"),"")).removeData("owl.carousel")},e.prototype.op=function(a,b,c){var d=this.settings.rtl;switch(b){case"<":return d?a>c:a<c;case">":return d?a<c:a>c;case">=":return d?a<=c:a>=c;case"<=":return d?a>=c:a<=c}},e.prototype.on=function(a,b,c,d){a.addEventListener?a.addEventListener(b,c,d):a.attachEvent&&a.attachEvent("on"+b,c)},e.prototype.off=function(a,b,c,d){a.removeEventListener?a.removeEventListener(b,c,d):a.detachEvent&&a.detachEvent("on"+b,c)},e.prototype.trigger=function(b,c,d,f,g){var h={item:{count:this._items.length,index:this.current()}},i=a.camelCase(a.grep(["on",b,d],function(a){return a}).join("-").toLowerCase()),j=a.Event([b,"owl",d||"carousel"].join(".").toLowerCase(),a.extend({relatedTarget:this},h,c));return this._supress[b]||(a.each(this._plugins,function(a,b){b.onTrigger&&b.onTrigger(j)}),this.register({type:e.Type.Event,name:b}),this.$element.trigger(j),this.settings&&"function"==typeof this.settings[i]&&this.settings[i].call(this,j)),j},e.prototype.enter=function(b){a.each([b].concat(this._states.tags[b]||[]),a.proxy(function(a,b){this._states.current[b]===d&&(this._states.current[b]=0),this._states.current[b]++},this))},e.prototype.leave=function(b){a.each([b].concat(this._states.tags[b]||[]),a.proxy(function(a,b){this._states.current[b]--},this))},e.prototype.register=function(b){if(b.type===e.Type.Event){if(a.event.special[b.name]||(a.event.special[b.name]={}),!a.event.special[b.name].owl){var c=a.event.special[b.name]._default;a.event.special[b.name]._default=function(a){return!c||!c.apply||a.namespace&&-1!==a.namespace.indexOf("owl")?a.namespace&&a.namespace.indexOf("owl")>-1:c.apply(this,arguments)},a.event.special[b.name].owl=!0}}else b.type===e.Type.State&&(this._states.tags[b.name]?this._states.tags[b.name]=this._states.tags[b.name].concat(b.tags):this._states.tags[b.name]=b.tags,this._states.tags[b.name]=a.grep(this._states.tags[b.name],a.proxy(function(c,d){return a.inArray(c,this._states.tags[b.name])===d},this)))},e.prototype.suppress=function(b){a.each(b,a.proxy(function(a,b){this._supress[b]=!0},this))},e.prototype.release=function(b){a.each(b,a.proxy(function(a,b){delete this._supress[b]},this))},e.prototype.pointer=function(a){var c={x:null,y:null};return a=a.originalEvent||a||b.event,a=a.touches&&a.touches.length?a.touches[0]:a.changedTouches&&a.changedTouches.length?a.changedTouches[0]:a,a.pageX?(c.x=a.pageX,c.y=a.pageY):(c.x=a.clientX,c.y=a.clientY),c},e.prototype.isNumeric=function(a){return!isNaN(parseFloat(a))},e.prototype.difference=function(a,b){return{x:a.x-b.x,y:a.y-b.y}},a.fn.owlCarousel=function(b){var c=Array.prototype.slice.call(arguments,1);return this.each(function(){var d=a(this),f=d.data("owl.carousel");f||(f=new e(this,"object"==typeof b&&b),d.data("owl.carousel",f),a.each(["next","prev","to","destroy","refresh","replace","add","remove"],function(b,c){f.register({type:e.Type.Event,name:c}),f.$element.on(c+".owl.carousel.core",a.proxy(function(a){a.namespace&&a.relatedTarget!==this&&(this.suppress([c]),f[c].apply(this,[].slice.call(arguments,1)),this.release([c]))},f))})),"string"==typeof b&&"_"!==b.charAt(0)&&f[b].apply(f,c)})},a.fn.owlCarousel.Constructor=e}(window.Zepto||window.jQuery,window,document),function(a,b,c,d){var e=function(b){this._core=b,this._interval=null,this._visible=null,this._handlers={"initialized.owl.carousel":a.proxy(function(a){a.namespace&&this._core.settings.autoRefresh&&this.watch()},this)},this._core.options=a.extend({},e.Defaults,this._core.options),this._core.$element.on(this._handlers)};e.Defaults={autoRefresh:!0,autoRefreshInterval:500},e.prototype.watch=function(){this._interval||(this._visible=this._core.isVisible(),this._interval=b.setInterval(a.proxy(this.refresh,this),this._core.settings.autoRefreshInterval))},e.prototype.refresh=function(){this._core.isVisible()!==this._visible&&(this._visible=!this._visible,this._core.$element.toggleClass("owl-hidden",!this._visible),this._visible&&this._core.invalidate("width")&&this._core.refresh())},e.prototype.destroy=function(){var a,c;b.clearInterval(this._interval);for(a in this._handlers)this._core.$element.off(a,this._handlers[a]);for(c in Object.getOwnPropertyNames(this))"function"!=typeof this[c]&&(this[c]=null)},a.fn.owlCarousel.Constructor.Plugins.AutoRefresh=e}(window.Zepto||window.jQuery,window,document),function(a,b,c,d){var e=function(b){this._core=b,this._loaded=[],this._handlers={"initialized.owl.carousel change.owl.carousel resized.owl.carousel":a.proxy(function(b){if(b.namespace&&this._core.settings&&this._core.settings.lazyLoad&&(b.property&&"position"==b.property.name||"initialized"==b.type)){var c=this._core.settings,e=c.center&&Math.ceil(c.items/2)||c.items,f=c.center&&-1*e||0,g=(b.property&&b.property.value!==d?b.property.value:this._core.current())+f,h=this._core.clones().length,i=a.proxy(function(a,b){this.load(b)},this);for(c.lazyLoadEager>0&&(e+=c.lazyLoadEager,c.loop&&(g-=c.lazyLoadEager,e++));f++<e;)this.load(h/2+this._core.relative(g)),h&&a.each(this._core.clones(this._core.relative(g)),i),g++}},this)},this._core.options=a.extend({},e.Defaults,this._core.options),this._core.$element.on(this._handlers)};e.Defaults={lazyLoad:!1,lazyLoadEager:0},e.prototype.load=function(c){var d=this._core.$stage.children().eq(c),e=d&&d.find(".owl-lazy");!e||a.inArray(d.get(0),this._loaded)>-1||(e.each(a.proxy(function(c,d){var e,f=a(d),g=b.devicePixelRatio>1&&f.attr("data-src-retina")||f.attr("data-src")||f.attr("data-srcset");this._core.trigger("load",{element:f,url:g},"lazy"),f.is("img")?f.one("load.owl.lazy",a.proxy(function(){f.css("opacity",1),this._core.trigger("loaded",{element:f,url:g},"lazy")},this)).attr("src",g):f.is("source")?f.one("load.owl.lazy",a.proxy(function(){this._core.trigger("loaded",{element:f,url:g},"lazy")},this)).attr("srcset",g):(e=new Image,e.onload=a.proxy(function(){f.css({"background-image":'url("'+g+'")',opacity:"1"}),this._core.trigger("loaded",{element:f,url:g},"lazy")},this),e.src=g)},this)),this._loaded.push(d.get(0)))},e.prototype.destroy=function(){var a,b;for(a in this.handlers)this._core.$element.off(a,this.handlers[a]);for(b in Object.getOwnPropertyNames(this))"function"!=typeof this[b]&&(this[b]=null)},a.fn.owlCarousel.Constructor.Plugins.Lazy=e}(window.Zepto||window.jQuery,window,document),function(a,b,c,d){var e=function(c){this._core=c,this._previousHeight=null,this._handlers={"initialized.owl.carousel refreshed.owl.carousel":a.proxy(function(a){a.namespace&&this._core.settings.autoHeight&&this.update()},this),"changed.owl.carousel":a.proxy(function(a){a.namespace&&this._core.settings.autoHeight&&"position"===a.property.name&&this.update()},this),"loaded.owl.lazy":a.proxy(function(a){a.namespace&&this._core.settings.autoHeight&&a.element.closest("."+this._core.settings.itemClass).index()===this._core.current()&&this.update()},this)},this._core.options=a.extend({},e.Defaults,this._core.options),this._core.$element.on(this._handlers),this._intervalId=null;var d=this;a(b).on("load",function(){d._core.settings.autoHeight&&d.update()}),a(b).resize(function(){d._core.settings.autoHeight&&(null!=d._intervalId&&clearTimeout(d._intervalId),d._intervalId=setTimeout(function(){d.update()},250))})};e.Defaults={autoHeight:!1,autoHeightClass:"owl-height"},e.prototype.update=function(){var b=this._core._current,c=b+this._core.settings.items,d=this._core.settings.lazyLoad,e=this._core.$stage.children().toArray().slice(b,c),f=[],g=0;a.each(e,function(b,c){f.push(a(c).height())}),g=Math.max.apply(null,f),g<=1&&d&&this._previousHeight&&(g=this._previousHeight),this._previousHeight=g,this._core.$stage.parent().height(g).addClass(this._core.settings.autoHeightClass)},e.prototype.destroy=function(){var a,b;for(a in this._handlers)this._core.$element.off(a,this._handlers[a]);for(b in Object.getOwnPropertyNames(this))"function"!=typeof this[b]&&(this[b]=null)},a.fn.owlCarousel.Constructor.Plugins.AutoHeight=e}(window.Zepto||window.jQuery,window,document),function(a,b,c,d){var e=function(b){this._core=b,this._videos={},this._playing=null,this._handlers={"initialized.owl.carousel":a.proxy(function(a){a.namespace&&this._core.register({type:"state",name:"playing",tags:["interacting"]})},this),"resize.owl.carousel":a.proxy(function(a){a.namespace&&this._core.settings.video&&this.isInFullScreen()&&a.preventDefault()},this),"refreshed.owl.carousel":a.proxy(function(a){a.namespace&&this._core.is("resizing")&&this._core.$stage.find(".cloned .owl-video-frame").remove()},this),"changed.owl.carousel":a.proxy(function(a){a.namespace&&"position"===a.property.name&&this._playing&&this.stop()},this),"prepared.owl.carousel":a.proxy(function(b){if(b.namespace){var c=a(b.content).find(".owl-video");c.length&&(c.css("display","none"),this.fetch(c,a(b.content)))}},this)},this._core.options=a.extend({},e.Defaults,this._core.options),this._core.$element.on(this._handlers),this._core.$element.on("click.owl.video",".owl-video-play-icon",a.proxy(function(a){this.play(a)},this))};e.Defaults={video:!1,videoHeight:!1,videoWidth:!1},e.prototype.fetch=function(a,b){var c=function(){return a.attr("data-vimeo-id")?"vimeo":a.attr("data-vzaar-id")?"vzaar":"youtube"}(),d=a.attr("data-vimeo-id")||a.attr("data-youtube-id")||a.attr("data-vzaar-id"),e=a.attr("data-width")||this._core.settings.videoWidth,f=a.attr("data-height")||this._core.settings.videoHeight,g=a.attr("href");if(!g)throw new Error("Missing video URL.");if(d=g.match(/(http:|https:|)\/\/(player.|www.|app.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com|be\-nocookie\.com)|vzaar\.com)\/(video\/|videos\/|embed\/|channels\/.+\/|groups\/.+\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/),d[3].indexOf("youtu")>-1)c="youtube";else if(d[3].indexOf("vimeo")>-1)c="vimeo";else{if(!(d[3].indexOf("vzaar")>-1))throw new Error("Video URL not supported.");c="vzaar"}d=d[6],this._videos[g]={type:c,id:d,width:e,height:f},b.attr("data-video",g),this.thumbnail(a,this._videos[g])},e.prototype.thumbnail=function(b,c){var d,e,f,g=c.width&&c.height?"width:"+c.width+"px;height:"+c.height+"px;":"",h=b.find("img"),i="src",j="",k=this._core.settings,l=function(c){e='<div class="owl-video-play-icon"></div>',d=k.lazyLoad?a("<div/>",{class:"owl-video-tn "+j,srcType:c}):a("<div/>",{class:"owl-video-tn",style:"opacity:1;background-image:url("+c+")"}),b.after(d),b.after(e)};if(b.wrap(a("<div/>",{class:"owl-video-wrapper",style:g})),this._core.settings.lazyLoad&&(i="data-src",j="owl-lazy"),h.length)return l(h.attr(i)),h.remove(),!1;"youtube"===c.type?(f="//img.youtube.com/vi/"+c.id+"/hqdefault.jpg",l(f)):"vimeo"===c.type?a.ajax({type:"GET",url:"//vimeo.com/api/v2/video/"+c.id+".json",jsonp:"callback",dataType:"jsonp",success:function(a){f=a[0].thumbnail_large,l(f)}}):"vzaar"===c.type&&a.ajax({type:"GET",url:"//vzaar.com/api/videos/"+c.id+".json",jsonp:"callback",dataType:"jsonp",success:function(a){f=a.framegrab_url,l(f)}})},e.prototype.stop=function(){this._core.trigger("stop",null,"video"),this._playing.find(".owl-video-frame").remove(),this._playing.removeClass("owl-video-playing"),this._playing=null,this._core.leave("playing"),this._core.trigger("stopped",null,"video")},e.prototype.play=function(b){var c,d=a(b.target),e=d.closest("."+this._core.settings.itemClass),f=this._videos[e.attr("data-video")],g=f.width||"100%",h=f.height||this._core.$stage.height();this._playing||(this._core.enter("playing"),this._core.trigger("play",null,"video"),e=this._core.items(this._core.relative(e.index())),this._core.reset(e.index()),c=a('<iframe frameborder="0" allowfullscreen mozallowfullscreen webkitAllowFullScreen ></iframe>'),c.attr("height",h),c.attr("width",g),"youtube"===f.type?c.attr("src","//www.youtube.com/embed/"+f.id+"?autoplay=1&rel=0&v="+f.id):"vimeo"===f.type?c.attr("src","//player.vimeo.com/video/"+f.id+"?autoplay=1"):"vzaar"===f.type&&c.attr("src","//view.vzaar.com/"+f.id+"/player?autoplay=true"),a(c).wrap('<div class="owl-video-frame" />').insertAfter(e.find(".owl-video")),this._playing=e.addClass("owl-video-playing"))},e.prototype.isInFullScreen=function(){var b=c.fullscreenElement||c.mozFullScreenElement||c.webkitFullscreenElement;return b&&a(b).parent().hasClass("owl-video-frame")},e.prototype.destroy=function(){var a,b;this._core.$element.off("click.owl.video");for(a in this._handlers)this._core.$element.off(a,this._handlers[a]);for(b in Object.getOwnPropertyNames(this))"function"!=typeof this[b]&&(this[b]=null)},a.fn.owlCarousel.Constructor.Plugins.Video=e}(window.Zepto||window.jQuery,window,document),function(a,b,c,d){var e=function(b){this.core=b,this.core.options=a.extend({},e.Defaults,this.core.options),this.swapping=!0,this.previous=d,this.next=d,this.handlers={"change.owl.carousel":a.proxy(function(a){a.namespace&&"position"==a.property.name&&(this.previous=this.core.current(),this.next=a.property.value)},this),"drag.owl.carousel dragged.owl.carousel translated.owl.carousel":a.proxy(function(a){a.namespace&&(this.swapping="translated"==a.type)},this),"translate.owl.carousel":a.proxy(function(a){a.namespace&&this.swapping&&(this.core.options.animateOut||this.core.options.animateIn)&&this.swap()},this)},this.core.$element.on(this.handlers)};e.Defaults={animateOut:!1,animateIn:!1},e.prototype.swap=function(){if(1===this.core.settings.items&&a.support.animation&&a.support.transition){this.core.speed(0);var b,c=a.proxy(this.clear,this),d=this.core.$stage.children().eq(this.previous),e=this.core.$stage.children().eq(this.next),f=this.core.settings.animateIn,g=this.core.settings.animateOut;this.core.current()!==this.previous&&(g&&(b=this.core.coordinates(this.previous)-this.core.coordinates(this.next),d.one(a.support.animation.end,c).css({left:b+"px"}).addClass("animated owl-animated-out").addClass(g)),f&&e.one(a.support.animation.end,c).addClass("animated owl-animated-in").addClass(f))}},e.prototype.clear=function(b){a(b.target).css({left:""}).removeClass("animated owl-animated-out owl-animated-in").removeClass(this.core.settings.animateIn).removeClass(this.core.settings.animateOut),this.core.onTransitionEnd()},e.prototype.destroy=function(){var a,b;for(a in this.handlers)this.core.$element.off(a,this.handlers[a]);for(b in Object.getOwnPropertyNames(this))"function"!=typeof this[b]&&(this[b]=null)},a.fn.owlCarousel.Constructor.Plugins.Animate=e}(window.Zepto||window.jQuery,window,document),function(a,b,c,d){var e=function(b){this._core=b,this._call=null,this._time=0,this._timeout=0,this._paused=!0,this._handlers={"changed.owl.carousel":a.proxy(function(a){a.namespace&&"settings"===a.property.name?this._core.settings.autoplay?this.play():this.stop():a.namespace&&"position"===a.property.name&&this._paused&&(this._time=0)},this),"initialized.owl.carousel":a.proxy(function(a){a.namespace&&this._core.settings.autoplay&&this.play()},this),"play.owl.autoplay":a.proxy(function(a,b,c){a.namespace&&this.play(b,c)},this),"stop.owl.autoplay":a.proxy(function(a){a.namespace&&this.stop()},this),"mouseover.owl.autoplay":a.proxy(function(){this._core.settings.autoplayHoverPause&&this._core.is("rotating")&&this.pause()},this),"mouseleave.owl.autoplay":a.proxy(function(){this._core.settings.autoplayHoverPause&&this._core.is("rotating")&&this.play()},this),"touchstart.owl.core":a.proxy(function(){this._core.settings.autoplayHoverPause&&this._core.is("rotating")&&this.pause()},this),"touchend.owl.core":a.proxy(function(){this._core.settings.autoplayHoverPause&&this.play()},this)},this._core.$element.on(this._handlers),this._core.options=a.extend({},e.Defaults,this._core.options)};e.Defaults={autoplay:!1,autoplayTimeout:5e3,autoplayHoverPause:!1,autoplaySpeed:!1},e.prototype._next=function(d){this._call=b.setTimeout(a.proxy(this._next,this,d),this._timeout*(Math.round(this.read()/this._timeout)+1)-this.read()),this._core.is("interacting")||c.hidden||this._core.next(d||this._core.settings.autoplaySpeed)},e.prototype.read=function(){return(new Date).getTime()-this._time},e.prototype.play=function(c,d){var e;this._core.is("rotating")||this._core.enter("rotating"),c=c||this._core.settings.autoplayTimeout,e=Math.min(this._time%(this._timeout||c),c),this._paused?(this._time=this.read(),this._paused=!1):b.clearTimeout(this._call),this._time+=this.read()%c-e,this._timeout=c,this._call=b.setTimeout(a.proxy(this._next,this,d),c-e)},e.prototype.stop=function(){this._core.is("rotating")&&(this._time=0,this._paused=!0,b.clearTimeout(this._call),this._core.leave("rotating"))},e.prototype.pause=function(){this._core.is("rotating")&&!this._paused&&(this._time=this.read(),this._paused=!0,b.clearTimeout(this._call))},e.prototype.destroy=function(){var a,b;this.stop();for(a in this._handlers)this._core.$element.off(a,this._handlers[a]);for(b in Object.getOwnPropertyNames(this))"function"!=typeof this[b]&&(this[b]=null)},a.fn.owlCarousel.Constructor.Plugins.autoplay=e}(window.Zepto||window.jQuery,window,document),function(a,b,c,d){"use strict";var e=function(b){this._core=b,this._initialized=!1,this._pages=[],this._controls={},this._templates=[],this.$element=this._core.$element,this._overrides={next:this._core.next,prev:this._core.prev,to:this._core.to},this._handlers={"prepared.owl.carousel":a.proxy(function(b){b.namespace&&this._core.settings.dotsData&&this._templates.push('<div class="'+this._core.settings.dotClass+'">'+a(b.content).find("[data-dot]").addBack("[data-dot]").attr("data-dot")+"</div>")},this),"added.owl.carousel":a.proxy(function(a){a.namespace&&this._core.settings.dotsData&&this._templates.splice(a.position,0,this._templates.pop())},this),"remove.owl.carousel":a.proxy(function(a){a.namespace&&this._core.settings.dotsData&&this._templates.splice(a.position,1)},this),"changed.owl.carousel":a.proxy(function(a){a.namespace&&"position"==a.property.name&&this.draw()},this),"initialized.owl.carousel":a.proxy(function(a){a.namespace&&!this._initialized&&(this._core.trigger("initialize",null,"navigation"),this.initialize(),this.update(),this.draw(),this._initialized=!0,this._core.trigger("initialized",null,"navigation"))},this),"refreshed.owl.carousel":a.proxy(function(a){a.namespace&&this._initialized&&(this._core.trigger("refresh",null,"navigation"),this.update(),this.draw(),this._core.trigger("refreshed",null,"navigation"))},this)},this._core.options=a.extend({},e.Defaults,this._core.options),this.$element.on(this._handlers)};e.Defaults={nav:!1,navText:['<span aria-label="Previous">&#x2039;</span>','<span aria-label="Next">&#x203a;</span>'],navSpeed:!1,navElement:'button type="button" role="presentation"',navContainer:!1,navContainerClass:"owl-nav",navClass:["owl-prev","owl-next"],slideBy:1,dotClass:"owl-dot",dotsClass:"owl-dots",dots:!0,dotsEach:!1,dotsData:!1,dotsSpeed:!1,dotsContainer:!1},e.prototype.initialize=function(){var b,c=this._core.settings;this._controls.$relative=(c.navContainer?a(c.navContainer):a("<div>").addClass(c.navContainerClass).appendTo(this.$element)).addClass("disabled"),this._controls.$previous=a("<"+c.navElement+">").addClass(c.navClass[0]).html(c.navText[0]).prependTo(this._controls.$relative).on("click",a.proxy(function(a){this.prev(c.navSpeed)},this)),this._controls.$next=a("<"+c.navElement+">").addClass(c.navClass[1]).html(c.navText[1]).appendTo(this._controls.$relative).on("click",a.proxy(function(a){this.next(c.navSpeed)},this)),c.dotsData||(this._templates=[a('<button role="button">').addClass(c.dotClass).append(a("<span>")).prop("outerHTML")]),this._controls.$absolute=(c.dotsContainer?a(c.dotsContainer):a("<div>").addClass(c.dotsClass).appendTo(this.$element)).addClass("disabled"),this._controls.$absolute.on("click","button",a.proxy(function(b){var d=a(b.target).parent().is(this._controls.$absolute)?a(b.target).index():a(b.target).parent().index();b.preventDefault(),this.to(d,c.dotsSpeed)},this));for(b in this._overrides)this._core[b]=a.proxy(this[b],this)},e.prototype.destroy=function(){var a,b,c,d,e;e=this._core.settings;for(a in this._handlers)this.$element.off(a,this._handlers[a]);for(b in this._controls)"$relative"===b&&e.navContainer?this._controls[b].html(""):this._controls[b].remove();for(d in this.overides)this._core[d]=this._overrides[d];for(c in Object.getOwnPropertyNames(this))"function"!=typeof this[c]&&(this[c]=null)},e.prototype.update=function(){var a,b,c,d=this._core.clones().length/2,e=d+this._core.items().length,f=this._core.maximum(!0),g=this._core.settings,h=g.center||g.autoWidth||g.dotsData?1:g.dotsEach||g.items;if("page"!==g.slideBy&&(g.slideBy=Math.min(g.slideBy,g.items)),g.dots||"page"==g.slideBy)for(this._pages=[],a=d,b=0,c=0;a<e;a++){if(b>=h||0===b){if(this._pages.push({start:Math.min(f,a-d),end:a-d+h-1}),Math.min(f,a-d)===f)break;b=0,++c}b+=this._core.mergers(this._core.relative(a))}},e.prototype.draw=function(){var b,c=this._core.settings,d=this._core.items().length<=c.items,e=this._core.relative(this._core.current()),f=c.loop||c.rewind;this._controls.$relative.toggleClass("disabled",!c.nav||d),c.nav&&(this._controls.$previous.toggleClass("disabled",!f&&e<=this._core.minimum(!0)),this._controls.$next.toggleClass("disabled",!f&&e>=this._core.maximum(!0))),this._controls.$absolute.toggleClass("disabled",!c.dots||d),c.dots&&(b=this._pages.length-this._controls.$absolute.children().length,c.dotsData&&0!==b?this._controls.$absolute.html(this._templates.join("")):b>0?this._controls.$absolute.append(new Array(b+1).join(this._templates[0])):b<0&&this._controls.$absolute.children().slice(b).remove(),this._controls.$absolute.find(".active").removeClass("active"),this._controls.$absolute.children().eq(a.inArray(this.current(),this._pages)).addClass("active"))},e.prototype.onTrigger=function(b){var c=this._core.settings;b.page={index:a.inArray(this.current(),this._pages),count:this._pages.length,size:c&&(c.center||c.autoWidth||c.dotsData?1:c.dotsEach||c.items)}},e.prototype.current=function(){var b=this._core.relative(this._core.current());return a.grep(this._pages,a.proxy(function(a,c){return a.start<=b&&a.end>=b},this)).pop()},e.prototype.getPosition=function(b){var c,d,e=this._core.settings;return"page"==e.slideBy?(c=a.inArray(this.current(),this._pages),d=this._pages.length,b?++c:--c,c=this._pages[(c%d+d)%d].start):(c=this._core.relative(this._core.current()),d=this._core.items().length,b?c+=e.slideBy:c-=e.slideBy),c},e.prototype.next=function(b){a.proxy(this._overrides.to,this._core)(this.getPosition(!0),b)},e.prototype.prev=function(b){a.proxy(this._overrides.to,this._core)(this.getPosition(!1),b)},e.prototype.to=function(b,c,d){var e;!d&&this._pages.length?(e=this._pages.length,a.proxy(this._overrides.to,this._core)(this._pages[(b%e+e)%e].start,c)):a.proxy(this._overrides.to,this._core)(b,c)},a.fn.owlCarousel.Constructor.Plugins.Navigation=e}(window.Zepto||window.jQuery,window,document),function(a,b,c,d){"use strict";var e=function(c){this._core=c,this._hashes={},this.$element=this._core.$element,this._handlers={"initialized.owl.carousel":a.proxy(function(c){c.namespace&&"URLHash"===this._core.settings.startPosition&&a(b).trigger("hashchange.owl.navigation")},this),"prepared.owl.carousel":a.proxy(function(b){if(b.namespace){var c=a(b.content).find("[data-hash]").addBack("[data-hash]").attr("data-hash");if(!c)return;this._hashes[c]=b.content}},this),"changed.owl.carousel":a.proxy(function(c){if(c.namespace&&"position"===c.property.name){var d=this._core.items(this._core.relative(this._core.current())),e=a.map(this._hashes,function(a,b){return a===d?b:null}).join();if(!e||b.location.hash.slice(1)===e)return;b.location.hash=e}},this)},this._core.options=a.extend({},e.Defaults,this._core.options),this.$element.on(this._handlers),a(b).on("hashchange.owl.navigation",a.proxy(function(a){var c=b.location.hash.substring(1),e=this._core.$stage.children(),f=this._hashes[c]&&e.index(this._hashes[c]);f!==d&&f!==this._core.current()&&this._core.to(this._core.relative(f),!1,!0)},this))};e.Defaults={URLhashListener:!1},e.prototype.destroy=function(){var c,d;a(b).off("hashchange.owl.navigation");for(c in this._handlers)this._core.$element.off(c,this._handlers[c]);for(d in Object.getOwnPropertyNames(this))"function"!=typeof this[d]&&(this[d]=null)},a.fn.owlCarousel.Constructor.Plugins.Hash=e}(window.Zepto||window.jQuery,window,document),function(a,b,c,d){function e(b,c){var e=!1,f=b.charAt(0).toUpperCase()+b.slice(1);return a.each((b+" "+h.join(f+" ")+f).split(" "),function(a,b){if(g[b]!==d)return e=!c||b,!1}),e}function f(a){return e(a,!0)}var g=a("<support>").get(0).style,h="Webkit Moz O ms".split(" "),i={transition:{end:{WebkitTransition:"webkitTransitionEnd",MozTransition:"transitionend",OTransition:"oTransitionEnd",transition:"transitionend"}},animation:{end:{WebkitAnimation:"webkitAnimationEnd",MozAnimation:"animationend",OAnimation:"oAnimationEnd",animation:"animationend"}}},j={csstransforms:function(){return!!e("transform")},csstransforms3d:function(){return!!e("perspective")},csstransitions:function(){return!!e("transition")},cssanimations:function(){return!!e("animation")}};j.csstransitions()&&(a.support.transition=new String(f("transition")),a.support.transition.end=i.transition.end[a.support.transition]),j.cssanimations()&&(a.support.animation=new String(f("animation")),a.support.animation.end=i.animation.end[a.support.animation]),j.csstransforms()&&(a.support.transform=new String(f("transform")),a.support.transform3d=j.csstransforms3d())}(window.Zepto||window.jQuery,window,document);

/*! Morphext - v2.4.7 - 2016-11-04 */
!function(a){"use strict";function b(b,c){this.element=a(b),this.settings=a.extend({},d,c),this._defaults=d,this._init()}var c="Morphext",d={animation:"bounceIn",separator:",",speed:2e3,complete:a.noop};b.prototype={_init:function(){var b=this;this.phrases=[],this.element.addClass("morphext"),a.each(this.element.text().split(this.settings.separator),function(c,d){b.phrases.push(a.trim(d))}),this.index=-1,this.animate(),this.start()},animate:function(){this.index=++this.index%this.phrases.length,this.element[0].innerHTML='<span class="animated '+this.settings.animation+'">'+this.phrases[this.index]+"</span>",a.isFunction(this.settings.complete)&&this.settings.complete.call(this)},start:function(){var a=this;this._interval=setInterval(function(){a.animate()},this.settings.speed)},stop:function(){this._interval=clearInterval(this._interval)}},a.fn[c]=function(d){return this.each(function(){a.data(this,"plugin_"+c)||a.data(this,"plugin_"+c,new b(this,d))})}}(jQuery);

/*!
 * Isotope PACKAGED v3.0.6
 *
 * Licensed GPLv3 for open source use
 * or Isotope Commercial License for commercial use
 *
 * https://isotope.metafizzy.co
 * Copyright 2010-2018 Metafizzy
 */

!function(t,e){"function"==typeof define&&define.amd?define("jquery-bridget/jquery-bridget",["jquery"],function(i){return e(t,i)}):"object"==typeof module&&module.exports?module.exports=e(t,require("jquery")):t.jQueryBridget=e(t,t.jQuery)}(window,function(t,e){"use strict";function i(i,s,a){function u(t,e,o){var n,s="$()."+i+'("'+e+'")';return t.each(function(t,u){var h=a.data(u,i);if(!h)return void r(i+" not initialized. Cannot call methods, i.e. "+s);var d=h[e];if(!d||"_"==e.charAt(0))return void r(s+" is not a valid method");var l=d.apply(h,o);n=void 0===n?l:n}),void 0!==n?n:t}function h(t,e){t.each(function(t,o){var n=a.data(o,i);n?(n.option(e),n._init()):(n=new s(o,e),a.data(o,i,n))})}a=a||e||t.jQuery,a&&(s.prototype.option||(s.prototype.option=function(t){a.isPlainObject(t)&&(this.options=a.extend(!0,this.options,t))}),a.fn[i]=function(t){if("string"==typeof t){var e=n.call(arguments,1);return u(this,t,e)}return h(this,t),this},o(a))}function o(t){!t||t&&t.bridget||(t.bridget=i)}var n=Array.prototype.slice,s=t.console,r="undefined"==typeof s?function(){}:function(t){s.error(t)};return o(e||t.jQuery),i}),function(t,e){"function"==typeof define&&define.amd?define("ev-emitter/ev-emitter",e):"object"==typeof module&&module.exports?module.exports=e():t.EvEmitter=e()}("undefined"!=typeof window?window:this,function(){function t(){}var e=t.prototype;return e.on=function(t,e){if(t&&e){var i=this._events=this._events||{},o=i[t]=i[t]||[];return o.indexOf(e)==-1&&o.push(e),this}},e.once=function(t,e){if(t&&e){this.on(t,e);var i=this._onceEvents=this._onceEvents||{},o=i[t]=i[t]||{};return o[e]=!0,this}},e.off=function(t,e){var i=this._events&&this._events[t];if(i&&i.length){var o=i.indexOf(e);return o!=-1&&i.splice(o,1),this}},e.emitEvent=function(t,e){var i=this._events&&this._events[t];if(i&&i.length){i=i.slice(0),e=e||[];for(var o=this._onceEvents&&this._onceEvents[t],n=0;n<i.length;n++){var s=i[n],r=o&&o[s];r&&(this.off(t,s),delete o[s]),s.apply(this,e)}return this}},e.allOff=function(){delete this._events,delete this._onceEvents},t}),function(t,e){"function"==typeof define&&define.amd?define("get-size/get-size",e):"object"==typeof module&&module.exports?module.exports=e():t.getSize=e()}(window,function(){"use strict";function t(t){var e=parseFloat(t),i=t.indexOf("%")==-1&&!isNaN(e);return i&&e}function e(){}function i(){for(var t={width:0,height:0,innerWidth:0,innerHeight:0,outerWidth:0,outerHeight:0},e=0;e<h;e++){var i=u[e];t[i]=0}return t}function o(t){var e=getComputedStyle(t);return e||a("Style returned "+e+". Are you running this code in a hidden iframe on Firefox? See https://bit.ly/getsizebug1"),e}function n(){if(!d){d=!0;var e=document.createElement("div");e.style.width="200px",e.style.padding="1px 2px 3px 4px",e.style.borderStyle="solid",e.style.borderWidth="1px 2px 3px 4px",e.style.boxSizing="border-box";var i=document.body||document.documentElement;i.appendChild(e);var n=o(e);r=200==Math.round(t(n.width)),s.isBoxSizeOuter=r,i.removeChild(e)}}function s(e){if(n(),"string"==typeof e&&(e=document.querySelector(e)),e&&"object"==typeof e&&e.nodeType){var s=o(e);if("none"==s.display)return i();var a={};a.width=e.offsetWidth,a.height=e.offsetHeight;for(var d=a.isBorderBox="border-box"==s.boxSizing,l=0;l<h;l++){var f=u[l],c=s[f],m=parseFloat(c);a[f]=isNaN(m)?0:m}var p=a.paddingLeft+a.paddingRight,y=a.paddingTop+a.paddingBottom,g=a.marginLeft+a.marginRight,v=a.marginTop+a.marginBottom,_=a.borderLeftWidth+a.borderRightWidth,z=a.borderTopWidth+a.borderBottomWidth,I=d&&r,x=t(s.width);x!==!1&&(a.width=x+(I?0:p+_));var S=t(s.height);return S!==!1&&(a.height=S+(I?0:y+z)),a.innerWidth=a.width-(p+_),a.innerHeight=a.height-(y+z),a.outerWidth=a.width+g,a.outerHeight=a.height+v,a}}var r,a="undefined"==typeof console?e:function(t){console.error(t)},u=["paddingLeft","paddingRight","paddingTop","paddingBottom","marginLeft","marginRight","marginTop","marginBottom","borderLeftWidth","borderRightWidth","borderTopWidth","borderBottomWidth"],h=u.length,d=!1;return s}),function(t,e){"use strict";"function"==typeof define&&define.amd?define("desandro-matches-selector/matches-selector",e):"object"==typeof module&&module.exports?module.exports=e():t.matchesSelector=e()}(window,function(){"use strict";var t=function(){var t=window.Element.prototype;if(t.matches)return"matches";if(t.matchesSelector)return"matchesSelector";for(var e=["webkit","moz","ms","o"],i=0;i<e.length;i++){var o=e[i],n=o+"MatchesSelector";if(t[n])return n}}();return function(e,i){return e[t](i)}}),function(t,e){"function"==typeof define&&define.amd?define("fizzy-ui-utils/utils",["desandro-matches-selector/matches-selector"],function(i){return e(t,i)}):"object"==typeof module&&module.exports?module.exports=e(t,require("desandro-matches-selector")):t.fizzyUIUtils=e(t,t.matchesSelector)}(window,function(t,e){var i={};i.extend=function(t,e){for(var i in e)t[i]=e[i];return t},i.modulo=function(t,e){return(t%e+e)%e};var o=Array.prototype.slice;i.makeArray=function(t){if(Array.isArray(t))return t;if(null===t||void 0===t)return[];var e="object"==typeof t&&"number"==typeof t.length;return e?o.call(t):[t]},i.removeFrom=function(t,e){var i=t.indexOf(e);i!=-1&&t.splice(i,1)},i.getParent=function(t,i){for(;t.parentNode&&t!=document.body;)if(t=t.parentNode,e(t,i))return t},i.getQueryElement=function(t){return"string"==typeof t?document.querySelector(t):t},i.handleEvent=function(t){var e="on"+t.type;this[e]&&this[e](t)},i.filterFindElements=function(t,o){t=i.makeArray(t);var n=[];return t.forEach(function(t){if(t instanceof HTMLElement){if(!o)return void n.push(t);e(t,o)&&n.push(t);for(var i=t.querySelectorAll(o),s=0;s<i.length;s++)n.push(i[s])}}),n},i.debounceMethod=function(t,e,i){i=i||100;var o=t.prototype[e],n=e+"Timeout";t.prototype[e]=function(){var t=this[n];clearTimeout(t);var e=arguments,s=this;this[n]=setTimeout(function(){o.apply(s,e),delete s[n]},i)}},i.docReady=function(t){var e=document.readyState;"complete"==e||"interactive"==e?setTimeout(t):document.addEventListener("DOMContentLoaded",t)},i.toDashed=function(t){return t.replace(/(.)([A-Z])/g,function(t,e,i){return e+"-"+i}).toLowerCase()};var n=t.console;return i.htmlInit=function(e,o){i.docReady(function(){var s=i.toDashed(o),r="data-"+s,a=document.querySelectorAll("["+r+"]"),u=document.querySelectorAll(".js-"+s),h=i.makeArray(a).concat(i.makeArray(u)),d=r+"-options",l=t.jQuery;h.forEach(function(t){var i,s=t.getAttribute(r)||t.getAttribute(d);try{i=s&&JSON.parse(s)}catch(a){return void(n&&n.error("Error parsing "+r+" on "+t.className+": "+a))}var u=new e(t,i);l&&l.data(t,o,u)})})},i}),function(t,e){"function"==typeof define&&define.amd?define("outlayer/item",["ev-emitter/ev-emitter","get-size/get-size"],e):"object"==typeof module&&module.exports?module.exports=e(require("ev-emitter"),require("get-size")):(t.Outlayer={},t.Outlayer.Item=e(t.EvEmitter,t.getSize))}(window,function(t,e){"use strict";function i(t){for(var e in t)return!1;return e=null,!0}function o(t,e){t&&(this.element=t,this.layout=e,this.position={x:0,y:0},this._create())}function n(t){return t.replace(/([A-Z])/g,function(t){return"-"+t.toLowerCase()})}var s=document.documentElement.style,r="string"==typeof s.transition?"transition":"WebkitTransition",a="string"==typeof s.transform?"transform":"WebkitTransform",u={WebkitTransition:"webkitTransitionEnd",transition:"transitionend"}[r],h={transform:a,transition:r,transitionDuration:r+"Duration",transitionProperty:r+"Property",transitionDelay:r+"Delay"},d=o.prototype=Object.create(t.prototype);d.constructor=o,d._create=function(){this._transn={ingProperties:{},clean:{},onEnd:{}},this.css({position:"absolute"})},d.handleEvent=function(t){var e="on"+t.type;this[e]&&this[e](t)},d.getSize=function(){this.size=e(this.element)},d.css=function(t){var e=this.element.style;for(var i in t){var o=h[i]||i;e[o]=t[i]}},d.getPosition=function(){var t=getComputedStyle(this.element),e=this.layout._getOption("originLeft"),i=this.layout._getOption("originTop"),o=t[e?"left":"right"],n=t[i?"top":"bottom"],s=parseFloat(o),r=parseFloat(n),a=this.layout.size;o.indexOf("%")!=-1&&(s=s/100*a.width),n.indexOf("%")!=-1&&(r=r/100*a.height),s=isNaN(s)?0:s,r=isNaN(r)?0:r,s-=e?a.paddingLeft:a.paddingRight,r-=i?a.paddingTop:a.paddingBottom,this.position.x=s,this.position.y=r},d.layoutPosition=function(){var t=this.layout.size,e={},i=this.layout._getOption("originLeft"),o=this.layout._getOption("originTop"),n=i?"paddingLeft":"paddingRight",s=i?"left":"right",r=i?"right":"left",a=this.position.x+t[n];e[s]=this.getXValue(a),e[r]="";var u=o?"paddingTop":"paddingBottom",h=o?"top":"bottom",d=o?"bottom":"top",l=this.position.y+t[u];e[h]=this.getYValue(l),e[d]="",this.css(e),this.emitEvent("layout",[this])},d.getXValue=function(t){var e=this.layout._getOption("horizontal");return this.layout.options.percentPosition&&!e?t/this.layout.size.width*100+"%":t+"px"},d.getYValue=function(t){var e=this.layout._getOption("horizontal");return this.layout.options.percentPosition&&e?t/this.layout.size.height*100+"%":t+"px"},d._transitionTo=function(t,e){this.getPosition();var i=this.position.x,o=this.position.y,n=t==this.position.x&&e==this.position.y;if(this.setPosition(t,e),n&&!this.isTransitioning)return void this.layoutPosition();var s=t-i,r=e-o,a={};a.transform=this.getTranslate(s,r),this.transition({to:a,onTransitionEnd:{transform:this.layoutPosition},isCleaning:!0})},d.getTranslate=function(t,e){var i=this.layout._getOption("originLeft"),o=this.layout._getOption("originTop");return t=i?t:-t,e=o?e:-e,"translate3d("+t+"px, "+e+"px, 0)"},d.goTo=function(t,e){this.setPosition(t,e),this.layoutPosition()},d.moveTo=d._transitionTo,d.setPosition=function(t,e){this.position.x=parseFloat(t),this.position.y=parseFloat(e)},d._nonTransition=function(t){this.css(t.to),t.isCleaning&&this._removeStyles(t.to);for(var e in t.onTransitionEnd)t.onTransitionEnd[e].call(this)},d.transition=function(t){if(!parseFloat(this.layout.options.transitionDuration))return void this._nonTransition(t);var e=this._transn;for(var i in t.onTransitionEnd)e.onEnd[i]=t.onTransitionEnd[i];for(i in t.to)e.ingProperties[i]=!0,t.isCleaning&&(e.clean[i]=!0);if(t.from){this.css(t.from);var o=this.element.offsetHeight;o=null}this.enableTransition(t.to),this.css(t.to),this.isTransitioning=!0};var l="opacity,"+n(a);d.enableTransition=function(){if(!this.isTransitioning){var t=this.layout.options.transitionDuration;t="number"==typeof t?t+"ms":t,this.css({transitionProperty:l,transitionDuration:t,transitionDelay:this.staggerDelay||0}),this.element.addEventListener(u,this,!1)}},d.onwebkitTransitionEnd=function(t){this.ontransitionend(t)},d.onotransitionend=function(t){this.ontransitionend(t)};var f={"-webkit-transform":"transform"};d.ontransitionend=function(t){if(t.target===this.element){var e=this._transn,o=f[t.propertyName]||t.propertyName;if(delete e.ingProperties[o],i(e.ingProperties)&&this.disableTransition(),o in e.clean&&(this.element.style[t.propertyName]="",delete e.clean[o]),o in e.onEnd){var n=e.onEnd[o];n.call(this),delete e.onEnd[o]}this.emitEvent("transitionEnd",[this])}},d.disableTransition=function(){this.removeTransitionStyles(),this.element.removeEventListener(u,this,!1),this.isTransitioning=!1},d._removeStyles=function(t){var e={};for(var i in t)e[i]="";this.css(e)};var c={transitionProperty:"",transitionDuration:"",transitionDelay:""};return d.removeTransitionStyles=function(){this.css(c)},d.stagger=function(t){t=isNaN(t)?0:t,this.staggerDelay=t+"ms"},d.removeElem=function(){this.element.parentNode.removeChild(this.element),this.css({display:""}),this.emitEvent("remove",[this])},d.remove=function(){return r&&parseFloat(this.layout.options.transitionDuration)?(this.once("transitionEnd",function(){this.removeElem()}),void this.hide()):void this.removeElem()},d.reveal=function(){delete this.isHidden,this.css({display:""});var t=this.layout.options,e={},i=this.getHideRevealTransitionEndProperty("visibleStyle");e[i]=this.onRevealTransitionEnd,this.transition({from:t.hiddenStyle,to:t.visibleStyle,isCleaning:!0,onTransitionEnd:e})},d.onRevealTransitionEnd=function(){this.isHidden||this.emitEvent("reveal")},d.getHideRevealTransitionEndProperty=function(t){var e=this.layout.options[t];if(e.opacity)return"opacity";for(var i in e)return i},d.hide=function(){this.isHidden=!0,this.css({display:""});var t=this.layout.options,e={},i=this.getHideRevealTransitionEndProperty("hiddenStyle");e[i]=this.onHideTransitionEnd,this.transition({from:t.visibleStyle,to:t.hiddenStyle,isCleaning:!0,onTransitionEnd:e})},d.onHideTransitionEnd=function(){this.isHidden&&(this.css({display:"none"}),this.emitEvent("hide"))},d.destroy=function(){this.css({position:"",left:"",right:"",top:"",bottom:"",transition:"",transform:""})},o}),function(t,e){"use strict";"function"==typeof define&&define.amd?define("outlayer/outlayer",["ev-emitter/ev-emitter","get-size/get-size","fizzy-ui-utils/utils","./item"],function(i,o,n,s){return e(t,i,o,n,s)}):"object"==typeof module&&module.exports?module.exports=e(t,require("ev-emitter"),require("get-size"),require("fizzy-ui-utils"),require("./item")):t.Outlayer=e(t,t.EvEmitter,t.getSize,t.fizzyUIUtils,t.Outlayer.Item)}(window,function(t,e,i,o,n){"use strict";function s(t,e){var i=o.getQueryElement(t);if(!i)return void(u&&u.error("Bad element for "+this.constructor.namespace+": "+(i||t)));this.element=i,h&&(this.$element=h(this.element)),this.options=o.extend({},this.constructor.defaults),this.option(e);var n=++l;this.element.outlayerGUID=n,f[n]=this,this._create();var s=this._getOption("initLayout");s&&this.layout()}function r(t){function e(){t.apply(this,arguments)}return e.prototype=Object.create(t.prototype),e.prototype.constructor=e,e}function a(t){if("number"==typeof t)return t;var e=t.match(/(^\d*\.?\d*)(\w*)/),i=e&&e[1],o=e&&e[2];if(!i.length)return 0;i=parseFloat(i);var n=m[o]||1;return i*n}var u=t.console,h=t.jQuery,d=function(){},l=0,f={};s.namespace="outlayer",s.Item=n,s.defaults={containerStyle:{position:"relative"},initLayout:!0,originLeft:!0,originTop:!0,resize:!0,resizeContainer:!0,transitionDuration:"0.4s",hiddenStyle:{opacity:0,transform:"scale(0.001)"},visibleStyle:{opacity:1,transform:"scale(1)"}};var c=s.prototype;o.extend(c,e.prototype),c.option=function(t){o.extend(this.options,t)},c._getOption=function(t){var e=this.constructor.compatOptions[t];return e&&void 0!==this.options[e]?this.options[e]:this.options[t]},s.compatOptions={initLayout:"isInitLayout",horizontal:"isHorizontal",layoutInstant:"isLayoutInstant",originLeft:"isOriginLeft",originTop:"isOriginTop",resize:"isResizeBound",resizeContainer:"isResizingContainer"},c._create=function(){this.reloadItems(),this.stamps=[],this.stamp(this.options.stamp),o.extend(this.element.style,this.options.containerStyle);var t=this._getOption("resize");t&&this.bindResize()},c.reloadItems=function(){this.items=this._itemize(this.element.children)},c._itemize=function(t){for(var e=this._filterFindItemElements(t),i=this.constructor.Item,o=[],n=0;n<e.length;n++){var s=e[n],r=new i(s,this);o.push(r)}return o},c._filterFindItemElements=function(t){return o.filterFindElements(t,this.options.itemSelector)},c.getItemElements=function(){return this.items.map(function(t){return t.element})},c.layout=function(){this._resetLayout(),this._manageStamps();var t=this._getOption("layoutInstant"),e=void 0!==t?t:!this._isLayoutInited;this.layoutItems(this.items,e),this._isLayoutInited=!0},c._init=c.layout,c._resetLayout=function(){this.getSize()},c.getSize=function(){this.size=i(this.element)},c._getMeasurement=function(t,e){var o,n=this.options[t];n?("string"==typeof n?o=this.element.querySelector(n):n instanceof HTMLElement&&(o=n),this[t]=o?i(o)[e]:n):this[t]=0},c.layoutItems=function(t,e){t=this._getItemsForLayout(t),this._layoutItems(t,e),this._postLayout()},c._getItemsForLayout=function(t){return t.filter(function(t){return!t.isIgnored})},c._layoutItems=function(t,e){if(this._emitCompleteOnItems("layout",t),t&&t.length){var i=[];t.forEach(function(t){var o=this._getItemLayoutPosition(t);o.item=t,o.isInstant=e||t.isLayoutInstant,i.push(o)},this),this._processLayoutQueue(i)}},c._getItemLayoutPosition=function(){return{x:0,y:0}},c._processLayoutQueue=function(t){this.updateStagger(),t.forEach(function(t,e){this._positionItem(t.item,t.x,t.y,t.isInstant,e)},this)},c.updateStagger=function(){var t=this.options.stagger;return null===t||void 0===t?void(this.stagger=0):(this.stagger=a(t),this.stagger)},c._positionItem=function(t,e,i,o,n){o?t.goTo(e,i):(t.stagger(n*this.stagger),t.moveTo(e,i))},c._postLayout=function(){this.resizeContainer()},c.resizeContainer=function(){var t=this._getOption("resizeContainer");if(t){var e=this._getContainerSize();e&&(this._setContainerMeasure(e.width,!0),this._setContainerMeasure(e.height,!1))}},c._getContainerSize=d,c._setContainerMeasure=function(t,e){if(void 0!==t){var i=this.size;i.isBorderBox&&(t+=e?i.paddingLeft+i.paddingRight+i.borderLeftWidth+i.borderRightWidth:i.paddingBottom+i.paddingTop+i.borderTopWidth+i.borderBottomWidth),t=Math.max(t,0),this.element.style[e?"width":"height"]=t+"px"}},c._emitCompleteOnItems=function(t,e){function i(){n.dispatchEvent(t+"Complete",null,[e])}function o(){r++,r==s&&i()}var n=this,s=e.length;if(!e||!s)return void i();var r=0;e.forEach(function(e){e.once(t,o)})},c.dispatchEvent=function(t,e,i){var o=e?[e].concat(i):i;if(this.emitEvent(t,o),h)if(this.$element=this.$element||h(this.element),e){var n=h.Event(e);n.type=t,this.$element.trigger(n,i)}else this.$element.trigger(t,i)},c.ignore=function(t){var e=this.getItem(t);e&&(e.isIgnored=!0)},c.unignore=function(t){var e=this.getItem(t);e&&delete e.isIgnored},c.stamp=function(t){t=this._find(t),t&&(this.stamps=this.stamps.concat(t),t.forEach(this.ignore,this))},c.unstamp=function(t){t=this._find(t),t&&t.forEach(function(t){o.removeFrom(this.stamps,t),this.unignore(t)},this)},c._find=function(t){if(t)return"string"==typeof t&&(t=this.element.querySelectorAll(t)),t=o.makeArray(t)},c._manageStamps=function(){this.stamps&&this.stamps.length&&(this._getBoundingRect(),this.stamps.forEach(this._manageStamp,this))},c._getBoundingRect=function(){var t=this.element.getBoundingClientRect(),e=this.size;this._boundingRect={left:t.left+e.paddingLeft+e.borderLeftWidth,top:t.top+e.paddingTop+e.borderTopWidth,right:t.right-(e.paddingRight+e.borderRightWidth),bottom:t.bottom-(e.paddingBottom+e.borderBottomWidth)}},c._manageStamp=d,c._getElementOffset=function(t){var e=t.getBoundingClientRect(),o=this._boundingRect,n=i(t),s={left:e.left-o.left-n.marginLeft,top:e.top-o.top-n.marginTop,right:o.right-e.right-n.marginRight,bottom:o.bottom-e.bottom-n.marginBottom};return s},c.handleEvent=o.handleEvent,c.bindResize=function(){t.addEventListener("resize",this),this.isResizeBound=!0},c.unbindResize=function(){t.removeEventListener("resize",this),this.isResizeBound=!1},c.onresize=function(){this.resize()},o.debounceMethod(s,"onresize",100),c.resize=function(){this.isResizeBound&&this.needsResizeLayout()&&this.layout()},c.needsResizeLayout=function(){var t=i(this.element),e=this.size&&t;return e&&t.innerWidth!==this.size.innerWidth},c.addItems=function(t){var e=this._itemize(t);return e.length&&(this.items=this.items.concat(e)),e},c.appended=function(t){var e=this.addItems(t);e.length&&(this.layoutItems(e,!0),this.reveal(e))},c.prepended=function(t){var e=this._itemize(t);if(e.length){var i=this.items.slice(0);this.items=e.concat(i),this._resetLayout(),this._manageStamps(),this.layoutItems(e,!0),this.reveal(e),this.layoutItems(i)}},c.reveal=function(t){if(this._emitCompleteOnItems("reveal",t),t&&t.length){var e=this.updateStagger();t.forEach(function(t,i){t.stagger(i*e),t.reveal()})}},c.hide=function(t){if(this._emitCompleteOnItems("hide",t),t&&t.length){var e=this.updateStagger();t.forEach(function(t,i){t.stagger(i*e),t.hide()})}},c.revealItemElements=function(t){var e=this.getItems(t);this.reveal(e)},c.hideItemElements=function(t){var e=this.getItems(t);this.hide(e)},c.getItem=function(t){for(var e=0;e<this.items.length;e++){var i=this.items[e];if(i.element==t)return i}},c.getItems=function(t){t=o.makeArray(t);var e=[];return t.forEach(function(t){var i=this.getItem(t);i&&e.push(i)},this),e},c.remove=function(t){var e=this.getItems(t);this._emitCompleteOnItems("remove",e),e&&e.length&&e.forEach(function(t){t.remove(),o.removeFrom(this.items,t)},this)},c.destroy=function(){var t=this.element.style;t.height="",t.position="",t.width="",this.items.forEach(function(t){t.destroy()}),this.unbindResize();var e=this.element.outlayerGUID;delete f[e],delete this.element.outlayerGUID,h&&h.removeData(this.element,this.constructor.namespace)},s.data=function(t){t=o.getQueryElement(t);var e=t&&t.outlayerGUID;return e&&f[e]},s.create=function(t,e){var i=r(s);return i.defaults=o.extend({},s.defaults),o.extend(i.defaults,e),i.compatOptions=o.extend({},s.compatOptions),i.namespace=t,i.data=s.data,i.Item=r(n),o.htmlInit(i,t),h&&h.bridget&&h.bridget(t,i),i};var m={ms:1,s:1e3};return s.Item=n,s}),function(t,e){"function"==typeof define&&define.amd?define("isotope-layout/js/item",["outlayer/outlayer"],e):"object"==typeof module&&module.exports?module.exports=e(require("outlayer")):(t.Isotope=t.Isotope||{},t.Isotope.Item=e(t.Outlayer))}(window,function(t){"use strict";function e(){t.Item.apply(this,arguments)}var i=e.prototype=Object.create(t.Item.prototype),o=i._create;i._create=function(){this.id=this.layout.itemGUID++,o.call(this),this.sortData={}},i.updateSortData=function(){if(!this.isIgnored){this.sortData.id=this.id,this.sortData["original-order"]=this.id,this.sortData.random=Math.random();var t=this.layout.options.getSortData,e=this.layout._sorters;for(var i in t){var o=e[i];this.sortData[i]=o(this.element,this)}}};var n=i.destroy;return i.destroy=function(){n.apply(this,arguments),this.css({display:""})},e}),function(t,e){"function"==typeof define&&define.amd?define("isotope-layout/js/layout-mode",["get-size/get-size","outlayer/outlayer"],e):"object"==typeof module&&module.exports?module.exports=e(require("get-size"),require("outlayer")):(t.Isotope=t.Isotope||{},t.Isotope.LayoutMode=e(t.getSize,t.Outlayer))}(window,function(t,e){"use strict";function i(t){this.isotope=t,t&&(this.options=t.options[this.namespace],this.element=t.element,this.items=t.filteredItems,this.size=t.size)}var o=i.prototype,n=["_resetLayout","_getItemLayoutPosition","_manageStamp","_getContainerSize","_getElementOffset","needsResizeLayout","_getOption"];return n.forEach(function(t){o[t]=function(){return e.prototype[t].apply(this.isotope,arguments)}}),o.needsVerticalResizeLayout=function(){var e=t(this.isotope.element),i=this.isotope.size&&e;return i&&e.innerHeight!=this.isotope.size.innerHeight},o._getMeasurement=function(){this.isotope._getMeasurement.apply(this,arguments)},o.getColumnWidth=function(){this.getSegmentSize("column","Width")},o.getRowHeight=function(){this.getSegmentSize("row","Height")},o.getSegmentSize=function(t,e){var i=t+e,o="outer"+e;if(this._getMeasurement(i,o),!this[i]){var n=this.getFirstItemSize();this[i]=n&&n[o]||this.isotope.size["inner"+e]}},o.getFirstItemSize=function(){var e=this.isotope.filteredItems[0];return e&&e.element&&t(e.element)},o.layout=function(){this.isotope.layout.apply(this.isotope,arguments)},o.getSize=function(){this.isotope.getSize(),this.size=this.isotope.size},i.modes={},i.create=function(t,e){function n(){i.apply(this,arguments)}return n.prototype=Object.create(o),n.prototype.constructor=n,e&&(n.options=e),n.prototype.namespace=t,i.modes[t]=n,n},i}),function(t,e){"function"==typeof define&&define.amd?define("masonry-layout/masonry",["outlayer/outlayer","get-size/get-size"],e):"object"==typeof module&&module.exports?module.exports=e(require("outlayer"),require("get-size")):t.Masonry=e(t.Outlayer,t.getSize)}(window,function(t,e){var i=t.create("masonry");i.compatOptions.fitWidth="isFitWidth";var o=i.prototype;return o._resetLayout=function(){this.getSize(),this._getMeasurement("columnWidth","outerWidth"),this._getMeasurement("gutter","outerWidth"),this.measureColumns(),this.colYs=[];for(var t=0;t<this.cols;t++)this.colYs.push(0);this.maxY=0,this.horizontalColIndex=0},o.measureColumns=function(){if(this.getContainerWidth(),!this.columnWidth){var t=this.items[0],i=t&&t.element;this.columnWidth=i&&e(i).outerWidth||this.containerWidth}var o=this.columnWidth+=this.gutter,n=this.containerWidth+this.gutter,s=n/o,r=o-n%o,a=r&&r<1?"round":"floor";s=Math[a](s),this.cols=Math.max(s,1)},o.getContainerWidth=function(){var t=this._getOption("fitWidth"),i=t?this.element.parentNode:this.element,o=e(i);this.containerWidth=o&&o.innerWidth},o._getItemLayoutPosition=function(t){t.getSize();var e=t.size.outerWidth%this.columnWidth,i=e&&e<1?"round":"ceil",o=Math[i](t.size.outerWidth/this.columnWidth);o=Math.min(o,this.cols);for(var n=this.options.horizontalOrder?"_getHorizontalColPosition":"_getTopColPosition",s=this[n](o,t),r={x:this.columnWidth*s.col,y:s.y},a=s.y+t.size.outerHeight,u=o+s.col,h=s.col;h<u;h++)this.colYs[h]=a;return r},o._getTopColPosition=function(t){var e=this._getTopColGroup(t),i=Math.min.apply(Math,e);return{col:e.indexOf(i),y:i}},o._getTopColGroup=function(t){if(t<2)return this.colYs;for(var e=[],i=this.cols+1-t,o=0;o<i;o++)e[o]=this._getColGroupY(o,t);return e},o._getColGroupY=function(t,e){if(e<2)return this.colYs[t];var i=this.colYs.slice(t,t+e);return Math.max.apply(Math,i)},o._getHorizontalColPosition=function(t,e){var i=this.horizontalColIndex%this.cols,o=t>1&&i+t>this.cols;i=o?0:i;var n=e.size.outerWidth&&e.size.outerHeight;return this.horizontalColIndex=n?i+t:this.horizontalColIndex,{col:i,y:this._getColGroupY(i,t)}},o._manageStamp=function(t){var i=e(t),o=this._getElementOffset(t),n=this._getOption("originLeft"),s=n?o.left:o.right,r=s+i.outerWidth,a=Math.floor(s/this.columnWidth);a=Math.max(0,a);var u=Math.floor(r/this.columnWidth);u-=r%this.columnWidth?0:1,u=Math.min(this.cols-1,u);for(var h=this._getOption("originTop"),d=(h?o.top:o.bottom)+i.outerHeight,l=a;l<=u;l++)this.colYs[l]=Math.max(d,this.colYs[l])},o._getContainerSize=function(){this.maxY=Math.max.apply(Math,this.colYs);var t={height:this.maxY};return this._getOption("fitWidth")&&(t.width=this._getContainerFitWidth()),t},o._getContainerFitWidth=function(){for(var t=0,e=this.cols;--e&&0===this.colYs[e];)t++;return(this.cols-t)*this.columnWidth-this.gutter},o.needsResizeLayout=function(){var t=this.containerWidth;return this.getContainerWidth(),t!=this.containerWidth},i}),function(t,e){"function"==typeof define&&define.amd?define("isotope-layout/js/layout-modes/masonry",["../layout-mode","masonry-layout/masonry"],e):"object"==typeof module&&module.exports?module.exports=e(require("../layout-mode"),require("masonry-layout")):e(t.Isotope.LayoutMode,t.Masonry)}(window,function(t,e){"use strict";var i=t.create("masonry"),o=i.prototype,n={_getElementOffset:!0,layout:!0,_getMeasurement:!0};for(var s in e.prototype)n[s]||(o[s]=e.prototype[s]);var r=o.measureColumns;o.measureColumns=function(){this.items=this.isotope.filteredItems,r.call(this)};var a=o._getOption;return o._getOption=function(t){return"fitWidth"==t?void 0!==this.options.isFitWidth?this.options.isFitWidth:this.options.fitWidth:a.apply(this.isotope,arguments)},i}),function(t,e){"function"==typeof define&&define.amd?define("isotope-layout/js/layout-modes/fit-rows",["../layout-mode"],e):"object"==typeof exports?module.exports=e(require("../layout-mode")):e(t.Isotope.LayoutMode)}(window,function(t){"use strict";var e=t.create("fitRows"),i=e.prototype;return i._resetLayout=function(){this.x=0,this.y=0,this.maxY=0,this._getMeasurement("gutter","outerWidth")},i._getItemLayoutPosition=function(t){t.getSize();var e=t.size.outerWidth+this.gutter,i=this.isotope.size.innerWidth+this.gutter;0!==this.x&&e+this.x>i&&(this.x=0,this.y=this.maxY);var o={x:this.x,y:this.y};return this.maxY=Math.max(this.maxY,this.y+t.size.outerHeight),this.x+=e,o},i._getContainerSize=function(){return{height:this.maxY}},e}),function(t,e){"function"==typeof define&&define.amd?define("isotope-layout/js/layout-modes/vertical",["../layout-mode"],e):"object"==typeof module&&module.exports?module.exports=e(require("../layout-mode")):e(t.Isotope.LayoutMode)}(window,function(t){"use strict";var e=t.create("vertical",{horizontalAlignment:0}),i=e.prototype;return i._resetLayout=function(){this.y=0},i._getItemLayoutPosition=function(t){t.getSize();var e=(this.isotope.size.innerWidth-t.size.outerWidth)*this.options.horizontalAlignment,i=this.y;return this.y+=t.size.outerHeight,{x:e,y:i}},i._getContainerSize=function(){return{height:this.y}},e}),function(t,e){"function"==typeof define&&define.amd?define(["outlayer/outlayer","get-size/get-size","desandro-matches-selector/matches-selector","fizzy-ui-utils/utils","isotope-layout/js/item","isotope-layout/js/layout-mode","isotope-layout/js/layout-modes/masonry","isotope-layout/js/layout-modes/fit-rows","isotope-layout/js/layout-modes/vertical"],function(i,o,n,s,r,a){return e(t,i,o,n,s,r,a)}):"object"==typeof module&&module.exports?module.exports=e(t,require("outlayer"),require("get-size"),require("desandro-matches-selector"),require("fizzy-ui-utils"),require("isotope-layout/js/item"),require("isotope-layout/js/layout-mode"),require("isotope-layout/js/layout-modes/masonry"),require("isotope-layout/js/layout-modes/fit-rows"),require("isotope-layout/js/layout-modes/vertical")):t.Isotope=e(t,t.Outlayer,t.getSize,t.matchesSelector,t.fizzyUIUtils,t.Isotope.Item,t.Isotope.LayoutMode)}(window,function(t,e,i,o,n,s,r){function a(t,e){return function(i,o){for(var n=0;n<t.length;n++){var s=t[n],r=i.sortData[s],a=o.sortData[s];if(r>a||r<a){var u=void 0!==e[s]?e[s]:e,h=u?1:-1;return(r>a?1:-1)*h}}return 0}}var u=t.jQuery,h=String.prototype.trim?function(t){return t.trim()}:function(t){return t.replace(/^\s+|\s+$/g,"")},d=e.create("isotope",{layoutMode:"masonry",isJQueryFiltering:!0,sortAscending:!0});d.Item=s,d.LayoutMode=r;var l=d.prototype;l._create=function(){this.itemGUID=0,this._sorters={},this._getSorters(),e.prototype._create.call(this),this.modes={},this.filteredItems=this.items,this.sortHistory=["original-order"];for(var t in r.modes)this._initLayoutMode(t)},l.reloadItems=function(){this.itemGUID=0,e.prototype.reloadItems.call(this)},l._itemize=function(){for(var t=e.prototype._itemize.apply(this,arguments),i=0;i<t.length;i++){var o=t[i];o.id=this.itemGUID++}return this._updateItemsSortData(t),t},l._initLayoutMode=function(t){var e=r.modes[t],i=this.options[t]||{};this.options[t]=e.options?n.extend(e.options,i):i,this.modes[t]=new e(this)},l.layout=function(){return!this._isLayoutInited&&this._getOption("initLayout")?void this.arrange():void this._layout()},l._layout=function(){var t=this._getIsInstant();this._resetLayout(),this._manageStamps(),this.layoutItems(this.filteredItems,t),this._isLayoutInited=!0},l.arrange=function(t){this.option(t),this._getIsInstant();var e=this._filter(this.items);this.filteredItems=e.matches,this._bindArrangeComplete(),this._isInstant?this._noTransition(this._hideReveal,[e]):this._hideReveal(e),this._sort(),this._layout()},l._init=l.arrange,l._hideReveal=function(t){this.reveal(t.needReveal),this.hide(t.needHide)},l._getIsInstant=function(){var t=this._getOption("layoutInstant"),e=void 0!==t?t:!this._isLayoutInited;return this._isInstant=e,e},l._bindArrangeComplete=function(){function t(){e&&i&&o&&n.dispatchEvent("arrangeComplete",null,[n.filteredItems])}var e,i,o,n=this;this.once("layoutComplete",function(){e=!0,t()}),this.once("hideComplete",function(){i=!0,t()}),this.once("revealComplete",function(){o=!0,t()})},l._filter=function(t){var e=this.options.filter;e=e||"*";for(var i=[],o=[],n=[],s=this._getFilterTest(e),r=0;r<t.length;r++){var a=t[r];if(!a.isIgnored){var u=s(a);u&&i.push(a),u&&a.isHidden?o.push(a):u||a.isHidden||n.push(a)}}return{matches:i,needReveal:o,needHide:n}},l._getFilterTest=function(t){return u&&this.options.isJQueryFiltering?function(e){return u(e.element).is(t);}:"function"==typeof t?function(e){return t(e.element)}:function(e){return o(e.element,t)}},l.updateSortData=function(t){var e;t?(t=n.makeArray(t),e=this.getItems(t)):e=this.items,this._getSorters(),this._updateItemsSortData(e)},l._getSorters=function(){var t=this.options.getSortData;for(var e in t){var i=t[e];this._sorters[e]=f(i)}},l._updateItemsSortData=function(t){for(var e=t&&t.length,i=0;e&&i<e;i++){var o=t[i];o.updateSortData()}};var f=function(){function t(t){if("string"!=typeof t)return t;var i=h(t).split(" "),o=i[0],n=o.match(/^\[(.+)\]$/),s=n&&n[1],r=e(s,o),a=d.sortDataParsers[i[1]];return t=a?function(t){return t&&a(r(t))}:function(t){return t&&r(t)}}function e(t,e){return t?function(e){return e.getAttribute(t)}:function(t){var i=t.querySelector(e);return i&&i.textContent}}return t}();d.sortDataParsers={parseInt:function(t){return parseInt(t,10)},parseFloat:function(t){return parseFloat(t)}},l._sort=function(){if(this.options.sortBy){var t=n.makeArray(this.options.sortBy);this._getIsSameSortBy(t)||(this.sortHistory=t.concat(this.sortHistory));var e=a(this.sortHistory,this.options.sortAscending);this.filteredItems.sort(e)}},l._getIsSameSortBy=function(t){for(var e=0;e<t.length;e++)if(t[e]!=this.sortHistory[e])return!1;return!0},l._mode=function(){var t=this.options.layoutMode,e=this.modes[t];if(!e)throw new Error("No layout mode: "+t);return e.options=this.options[t],e},l._resetLayout=function(){e.prototype._resetLayout.call(this),this._mode()._resetLayout()},l._getItemLayoutPosition=function(t){return this._mode()._getItemLayoutPosition(t)},l._manageStamp=function(t){this._mode()._manageStamp(t)},l._getContainerSize=function(){return this._mode()._getContainerSize()},l.needsResizeLayout=function(){return this._mode().needsResizeLayout()},l.appended=function(t){var e=this.addItems(t);if(e.length){var i=this._filterRevealAdded(e);this.filteredItems=this.filteredItems.concat(i)}},l.prepended=function(t){var e=this._itemize(t);if(e.length){this._resetLayout(),this._manageStamps();var i=this._filterRevealAdded(e);this.layoutItems(this.filteredItems),this.filteredItems=i.concat(this.filteredItems),this.items=e.concat(this.items)}},l._filterRevealAdded=function(t){var e=this._filter(t);return this.hide(e.needHide),this.reveal(e.matches),this.layoutItems(e.matches,!0),e.matches},l.insert=function(t){var e=this.addItems(t);if(e.length){var i,o,n=e.length;for(i=0;i<n;i++)o=e[i],this.element.appendChild(o.element);var s=this._filter(e).matches;for(i=0;i<n;i++)e[i].isLayoutInstant=!0;for(this.arrange(),i=0;i<n;i++)delete e[i].isLayoutInstant;this.reveal(s)}};var c=l.remove;return l.remove=function(t){t=n.makeArray(t);var e=this.getItems(t);c.call(this,t);for(var i=e&&e.length,o=0;i&&o<i;o++){var s=e[o];n.removeFrom(this.filteredItems,s)}},l.shuffle=function(){for(var t=0;t<this.items.length;t++){var e=this.items[t];e.sortData.random=Math.random()}this.options.sortBy="random",this._sort(),this._layout()},l._noTransition=function(t,e){var i=this.options.transitionDuration;this.options.transitionDuration=0;var o=t.apply(this,e);return this.options.transitionDuration=i,o},l.getFilteredItemElements=function(){return this.filteredItems.map(function(t){return t.element})},d});

/*!
 * imagesLoaded PACKAGED v4.1.4
 * JavaScript is all like "You images are done yet or what?"
 * MIT License
 */

!function(e,t){"function"==typeof define&&define.amd?define("ev-emitter/ev-emitter",t):"object"==typeof module&&module.exports?module.exports=t():e.EvEmitter=t()}("undefined"!=typeof window?window:this,function(){function e(){}var t=e.prototype;return t.on=function(e,t){if(e&&t){var i=this._events=this._events||{},n=i[e]=i[e]||[];return n.indexOf(t)==-1&&n.push(t),this}},t.once=function(e,t){if(e&&t){this.on(e,t);var i=this._onceEvents=this._onceEvents||{},n=i[e]=i[e]||{};return n[t]=!0,this}},t.off=function(e,t){var i=this._events&&this._events[e];if(i&&i.length){var n=i.indexOf(t);return n!=-1&&i.splice(n,1),this}},t.emitEvent=function(e,t){var i=this._events&&this._events[e];if(i&&i.length){i=i.slice(0),t=t||[];for(var n=this._onceEvents&&this._onceEvents[e],o=0;o<i.length;o++){var r=i[o],s=n&&n[r];s&&(this.off(e,r),delete n[r]),r.apply(this,t)}return this}},t.allOff=function(){delete this._events,delete this._onceEvents},e}),function(e,t){"use strict";"function"==typeof define&&define.amd?define(["ev-emitter/ev-emitter"],function(i){return t(e,i)}):"object"==typeof module&&module.exports?module.exports=t(e,require("ev-emitter")):e.imagesLoaded=t(e,e.EvEmitter)}("undefined"!=typeof window?window:this,function(e,t){function i(e,t){for(var i in t)e[i]=t[i];return e}function n(e){if(Array.isArray(e))return e;var t="object"==typeof e&&"number"==typeof e.length;return t?d.call(e):[e]}function o(e,t,r){if(!(this instanceof o))return new o(e,t,r);var s=e;return"string"==typeof e&&(s=document.querySelectorAll(e)),s?(this.elements=n(s),this.options=i({},this.options),"function"==typeof t?r=t:i(this.options,t),r&&this.on("always",r),this.getImages(),h&&(this.jqDeferred=new h.Deferred),void setTimeout(this.check.bind(this))):void a.error("Bad element for imagesLoaded "+(s||e))}function r(e){this.img=e}function s(e,t){this.url=e,this.element=t,this.img=new Image}var h=e.jQuery,a=e.console,d=Array.prototype.slice;o.prototype=Object.create(t.prototype),o.prototype.options={},o.prototype.getImages=function(){this.images=[],this.elements.forEach(this.addElementImages,this)},o.prototype.addElementImages=function(e){"IMG"==e.nodeName&&this.addImage(e),this.options.background===!0&&this.addElementBackgroundImages(e);var t=e.nodeType;if(t&&u[t]){for(var i=e.querySelectorAll("img"),n=0;n<i.length;n++){var o=i[n];this.addImage(o)}if("string"==typeof this.options.background){var r=e.querySelectorAll(this.options.background);for(n=0;n<r.length;n++){var s=r[n];this.addElementBackgroundImages(s)}}}};var u={1:!0,9:!0,11:!0};return o.prototype.addElementBackgroundImages=function(e){var t=getComputedStyle(e);if(t)for(var i=/url\((['"])?(.*?)\1\)/gi,n=i.exec(t.backgroundImage);null!==n;){var o=n&&n[2];o&&this.addBackground(o,e),n=i.exec(t.backgroundImage)}},o.prototype.addImage=function(e){var t=new r(e);this.images.push(t)},o.prototype.addBackground=function(e,t){var i=new s(e,t);this.images.push(i)},o.prototype.check=function(){function e(e,i,n){setTimeout(function(){t.progress(e,i,n)})}var t=this;return this.progressedCount=0,this.hasAnyBroken=!1,this.images.length?void this.images.forEach(function(t){t.once("progress",e),t.check()}):void this.complete()},o.prototype.progress=function(e,t,i){this.progressedCount++,this.hasAnyBroken=this.hasAnyBroken||!e.isLoaded,this.emitEvent("progress",[this,e,t]),this.jqDeferred&&this.jqDeferred.notify&&this.jqDeferred.notify(this,e),this.progressedCount==this.images.length&&this.complete(),this.options.debug&&a&&a.log("progress: "+i,e,t)},o.prototype.complete=function(){var e=this.hasAnyBroken?"fail":"done";if(this.isComplete=!0,this.emitEvent(e,[this]),this.emitEvent("always",[this]),this.jqDeferred){var t=this.hasAnyBroken?"reject":"resolve";this.jqDeferred[t](this)}},r.prototype=Object.create(t.prototype),r.prototype.check=function(){var e=this.getIsImageComplete();return e?void this.confirm(0!==this.img.naturalWidth,"naturalWidth"):(this.proxyImage=new Image,this.proxyImage.addEventListener("load",this),this.proxyImage.addEventListener("error",this),this.img.addEventListener("load",this),this.img.addEventListener("error",this),void(this.proxyImage.src=this.img.src))},r.prototype.getIsImageComplete=function(){return this.img.complete&&this.img.naturalWidth},r.prototype.confirm=function(e,t){this.isLoaded=e,this.emitEvent("progress",[this,this.img,t])},r.prototype.handleEvent=function(e){var t="on"+e.type;this[t]&&this[t](e)},r.prototype.onload=function(){this.confirm(!0,"onload"),this.unbindEvents()},r.prototype.onerror=function(){this.confirm(!1,"onerror"),this.unbindEvents()},r.prototype.unbindEvents=function(){this.proxyImage.removeEventListener("load",this),this.proxyImage.removeEventListener("error",this),this.img.removeEventListener("load",this),this.img.removeEventListener("error",this)},s.prototype=Object.create(r.prototype),s.prototype.check=function(){this.img.addEventListener("load",this),this.img.addEventListener("error",this),this.img.src=this.url;var e=this.getIsImageComplete();e&&(this.confirm(0!==this.img.naturalWidth,"naturalWidth"),this.unbindEvents())},s.prototype.unbindEvents=function(){this.img.removeEventListener("load",this),this.img.removeEventListener("error",this)},s.prototype.confirm=function(e,t){this.isLoaded=e,this.emitEvent("progress",[this,this.element,t])},o.makeJQueryPlugin=function(t){t=t||e.jQuery,t&&(h=t,h.fn.imagesLoaded=function(e,t){var i=new o(this,e,t);return i.jqDeferred.promise(h(this))})},o.makeJQueryPlugin(),o});

/**
 * Swiper 4.4.6
 * Most modern mobile touch slider and framework with hardware accelerated transitions
 * http://www.idangero.us/swiper/
 *
 * Copyright 2014-2018 Vladimir Kharlampidi
 *
 * Released under the MIT License
 *
 * Released on: December 19, 2018
 */
!function(e,t){"object"==typeof exports&&"undefined"!=typeof module?module.exports=t():"function"==typeof define&&define.amd?define(t):e.Swiper=t()}(this,function(){"use strict";var f="undefined"==typeof document?{body:{},addEventListener:function(){},removeEventListener:function(){},activeElement:{blur:function(){},nodeName:""},querySelector:function(){return null},querySelectorAll:function(){return[]},getElementById:function(){return null},createEvent:function(){return{initEvent:function(){}}},createElement:function(){return{children:[],childNodes:[],style:{},setAttribute:function(){},getElementsByTagName:function(){return[]}}},location:{hash:""}}:document,J="undefined"==typeof window?{document:f,navigator:{userAgent:""},location:{},history:{},CustomEvent:function(){return this},addEventListener:function(){},removeEventListener:function(){},getComputedStyle:function(){return{getPropertyValue:function(){return""}}},Image:function(){},Date:function(){},screen:{},setTimeout:function(){},clearTimeout:function(){}}:window,l=function(e){for(var t=0;t<e.length;t+=1)this[t]=e[t];return this.length=e.length,this};function L(e,t){var a=[],i=0;if(e&&!t&&e instanceof l)return e;if(e)if("string"==typeof e){var s,r,n=e.trim();if(0<=n.indexOf("<")&&0<=n.indexOf(">")){var o="div";for(0===n.indexOf("<li")&&(o="ul"),0===n.indexOf("<tr")&&(o="tbody"),0!==n.indexOf("<td")&&0!==n.indexOf("<th")||(o="tr"),0===n.indexOf("<tbody")&&(o="table"),0===n.indexOf("<option")&&(o="select"),(r=f.createElement(o)).innerHTML=n,i=0;i<r.childNodes.length;i+=1)a.push(r.childNodes[i])}else for(s=t||"#"!==e[0]||e.match(/[ .<>:~]/)?(t||f).querySelectorAll(e.trim()):[f.getElementById(e.trim().split("#")[1])],i=0;i<s.length;i+=1)s[i]&&a.push(s[i])}else if(e.nodeType||e===J||e===f)a.push(e);else if(0<e.length&&e[0].nodeType)for(i=0;i<e.length;i+=1)a.push(e[i]);return new l(a)}function r(e){for(var t=[],a=0;a<e.length;a+=1)-1===t.indexOf(e[a])&&t.push(e[a]);return t}L.fn=l.prototype,L.Class=l,L.Dom7=l;var t={addClass:function(e){if(void 0===e)return this;for(var t=e.split(" "),a=0;a<t.length;a+=1)for(var i=0;i<this.length;i+=1)void 0!==this[i]&&void 0!==this[i].classList&&this[i].classList.add(t[a]);return this},removeClass:function(e){for(var t=e.split(" "),a=0;a<t.length;a+=1)for(var i=0;i<this.length;i+=1)void 0!==this[i]&&void 0!==this[i].classList&&this[i].classList.remove(t[a]);return this},hasClass:function(e){return!!this[0]&&this[0].classList.contains(e)},toggleClass:function(e){for(var t=e.split(" "),a=0;a<t.length;a+=1)for(var i=0;i<this.length;i+=1)void 0!==this[i]&&void 0!==this[i].classList&&this[i].classList.toggle(t[a]);return this},attr:function(e,t){var a=arguments;if(1===arguments.length&&"string"==typeof e)return this[0]?this[0].getAttribute(e):void 0;for(var i=0;i<this.length;i+=1)if(2===a.length)this[i].setAttribute(e,t);else for(var s in e)this[i][s]=e[s],this[i].setAttribute(s,e[s]);return this},removeAttr:function(e){for(var t=0;t<this.length;t+=1)this[t].removeAttribute(e);return this},data:function(e,t){var a;if(void 0!==t){for(var i=0;i<this.length;i+=1)(a=this[i]).dom7ElementDataStorage||(a.dom7ElementDataStorage={}),a.dom7ElementDataStorage[e]=t;return this}if(a=this[0]){if(a.dom7ElementDataStorage&&e in a.dom7ElementDataStorage)return a.dom7ElementDataStorage[e];var s=a.getAttribute("data-"+e);return s||void 0}},transform:function(e){for(var t=0;t<this.length;t+=1){var a=this[t].style;a.webkitTransform=e,a.transform=e}return this},transition:function(e){"string"!=typeof e&&(e+="ms");for(var t=0;t<this.length;t+=1){var a=this[t].style;a.webkitTransitionDuration=e,a.transitionDuration=e}return this},on:function(){for(var e,t=[],a=arguments.length;a--;)t[a]=arguments[a];var i=t[0],r=t[1],n=t[2],s=t[3];function o(e){var t=e.target;if(t){var a=e.target.dom7EventData||[];if(a.indexOf(e)<0&&a.unshift(e),L(t).is(r))n.apply(t,a);else for(var i=L(t).parents(),s=0;s<i.length;s+=1)L(i[s]).is(r)&&n.apply(i[s],a)}}function l(e){var t=e&&e.target&&e.target.dom7EventData||[];t.indexOf(e)<0&&t.unshift(e),n.apply(this,t)}"function"==typeof t[1]&&(i=(e=t)[0],n=e[1],s=e[2],r=void 0),s||(s=!1);for(var d,p=i.split(" "),c=0;c<this.length;c+=1){var u=this[c];if(r)for(d=0;d<p.length;d+=1){var h=p[d];u.dom7LiveListeners||(u.dom7LiveListeners={}),u.dom7LiveListeners[h]||(u.dom7LiveListeners[h]=[]),u.dom7LiveListeners[h].push({listener:n,proxyListener:o}),u.addEventListener(h,o,s)}else for(d=0;d<p.length;d+=1){var v=p[d];u.dom7Listeners||(u.dom7Listeners={}),u.dom7Listeners[v]||(u.dom7Listeners[v]=[]),u.dom7Listeners[v].push({listener:n,proxyListener:l}),u.addEventListener(v,l,s)}}return this},off:function(){for(var e,t=[],a=arguments.length;a--;)t[a]=arguments[a];var i=t[0],s=t[1],r=t[2],n=t[3];"function"==typeof t[1]&&(i=(e=t)[0],r=e[1],n=e[2],s=void 0),n||(n=!1);for(var o=i.split(" "),l=0;l<o.length;l+=1)for(var d=o[l],p=0;p<this.length;p+=1){var c=this[p],u=void 0;if(!s&&c.dom7Listeners?u=c.dom7Listeners[d]:s&&c.dom7LiveListeners&&(u=c.dom7LiveListeners[d]),u&&u.length)for(var h=u.length-1;0<=h;h-=1){var v=u[h];r&&v.listener===r?(c.removeEventListener(d,v.proxyListener,n),u.splice(h,1)):r||(c.removeEventListener(d,v.proxyListener,n),u.splice(h,1))}}return this},trigger:function(){for(var e=[],t=arguments.length;t--;)e[t]=arguments[t];for(var a=e[0].split(" "),i=e[1],s=0;s<a.length;s+=1)for(var r=a[s],n=0;n<this.length;n+=1){var o=this[n],l=void 0;try{l=new J.CustomEvent(r,{detail:i,bubbles:!0,cancelable:!0})}catch(e){(l=f.createEvent("Event")).initEvent(r,!0,!0),l.detail=i}o.dom7EventData=e.filter(function(e,t){return 0<t}),o.dispatchEvent(l),o.dom7EventData=[],delete o.dom7EventData}return this},transitionEnd:function(t){var a,i=["webkitTransitionEnd","transitionend"],s=this;function r(e){if(e.target===this)for(t.call(this,e),a=0;a<i.length;a+=1)s.off(i[a],r)}if(t)for(a=0;a<i.length;a+=1)s.on(i[a],r);return this},outerWidth:function(e){if(0<this.length){if(e){var t=this.styles();return this[0].offsetWidth+parseFloat(t.getPropertyValue("margin-right"))+parseFloat(t.getPropertyValue("margin-left"))}return this[0].offsetWidth}return null},outerHeight:function(e){if(0<this.length){if(e){var t=this.styles();return this[0].offsetHeight+parseFloat(t.getPropertyValue("margin-top"))+parseFloat(t.getPropertyValue("margin-bottom"))}return this[0].offsetHeight}return null},offset:function(){if(0<this.length){var e=this[0],t=e.getBoundingClientRect(),a=f.body,i=e.clientTop||a.clientTop||0,s=e.clientLeft||a.clientLeft||0,r=e===J?J.scrollY:e.scrollTop,n=e===J?J.scrollX:e.scrollLeft;return{top:t.top+r-i,left:t.left+n-s}}return null},css:function(e,t){var a;if(1===arguments.length){if("string"!=typeof e){for(a=0;a<this.length;a+=1)for(var i in e)this[a].style[i]=e[i];return this}if(this[0])return J.getComputedStyle(this[0],null).getPropertyValue(e)}if(2===arguments.length&&"string"==typeof e){for(a=0;a<this.length;a+=1)this[a].style[e]=t;return this}return this},each:function(e){if(!e)return this;for(var t=0;t<this.length;t+=1)if(!1===e.call(this[t],t,this[t]))return this;return this},html:function(e){if(void 0===e)return this[0]?this[0].innerHTML:void 0;for(var t=0;t<this.length;t+=1)this[t].innerHTML=e;return this},text:function(e){if(void 0===e)return this[0]?this[0].textContent.trim():null;for(var t=0;t<this.length;t+=1)this[t].textContent=e;return this},is:function(e){var t,a,i=this[0];if(!i||void 0===e)return!1;if("string"==typeof e){if(i.matches)return i.matches(e);if(i.webkitMatchesSelector)return i.webkitMatchesSelector(e);if(i.msMatchesSelector)return i.msMatchesSelector(e);for(t=L(e),a=0;a<t.length;a+=1)if(t[a]===i)return!0;return!1}if(e===f)return i===f;if(e===J)return i===J;if(e.nodeType||e instanceof l){for(t=e.nodeType?[e]:e,a=0;a<t.length;a+=1)if(t[a]===i)return!0;return!1}return!1},index:function(){var e,t=this[0];if(t){for(e=0;null!==(t=t.previousSibling);)1===t.nodeType&&(e+=1);return e}},eq:function(e){if(void 0===e)return this;var t,a=this.length;return new l(a-1<e?[]:e<0?(t=a+e)<0?[]:[this[t]]:[this[e]])},append:function(){for(var e,t=[],a=arguments.length;a--;)t[a]=arguments[a];for(var i=0;i<t.length;i+=1){e=t[i];for(var s=0;s<this.length;s+=1)if("string"==typeof e){var r=f.createElement("div");for(r.innerHTML=e;r.firstChild;)this[s].appendChild(r.firstChild)}else if(e instanceof l)for(var n=0;n<e.length;n+=1)this[s].appendChild(e[n]);else this[s].appendChild(e)}return this},prepend:function(e){var t,a;for(t=0;t<this.length;t+=1)if("string"==typeof e){var i=f.createElement("div");for(i.innerHTML=e,a=i.childNodes.length-1;0<=a;a-=1)this[t].insertBefore(i.childNodes[a],this[t].childNodes[0])}else if(e instanceof l)for(a=0;a<e.length;a+=1)this[t].insertBefore(e[a],this[t].childNodes[0]);else this[t].insertBefore(e,this[t].childNodes[0]);return this},next:function(e){return 0<this.length?e?this[0].nextElementSibling&&L(this[0].nextElementSibling).is(e)?new l([this[0].nextElementSibling]):new l([]):this[0].nextElementSibling?new l([this[0].nextElementSibling]):new l([]):new l([])},nextAll:function(e){var t=[],a=this[0];if(!a)return new l([]);for(;a.nextElementSibling;){var i=a.nextElementSibling;e?L(i).is(e)&&t.push(i):t.push(i),a=i}return new l(t)},prev:function(e){if(0<this.length){var t=this[0];return e?t.previousElementSibling&&L(t.previousElementSibling).is(e)?new l([t.previousElementSibling]):new l([]):t.previousElementSibling?new l([t.previousElementSibling]):new l([])}return new l([])},prevAll:function(e){var t=[],a=this[0];if(!a)return new l([]);for(;a.previousElementSibling;){var i=a.previousElementSibling;e?L(i).is(e)&&t.push(i):t.push(i),a=i}return new l(t)},parent:function(e){for(var t=[],a=0;a<this.length;a+=1)null!==this[a].parentNode&&(e?L(this[a].parentNode).is(e)&&t.push(this[a].parentNode):t.push(this[a].parentNode));return L(r(t))},parents:function(e){for(var t=[],a=0;a<this.length;a+=1)for(var i=this[a].parentNode;i;)e?L(i).is(e)&&t.push(i):t.push(i),i=i.parentNode;return L(r(t))},closest:function(e){var t=this;return void 0===e?new l([]):(t.is(e)||(t=t.parents(e).eq(0)),t)},find:function(e){for(var t=[],a=0;a<this.length;a+=1)for(var i=this[a].querySelectorAll(e),s=0;s<i.length;s+=1)t.push(i[s]);return new l(t)},children:function(e){for(var t=[],a=0;a<this.length;a+=1)for(var i=this[a].childNodes,s=0;s<i.length;s+=1)e?1===i[s].nodeType&&L(i[s]).is(e)&&t.push(i[s]):1===i[s].nodeType&&t.push(i[s]);return new l(r(t))},remove:function(){for(var e=0;e<this.length;e+=1)this[e].parentNode&&this[e].parentNode.removeChild(this[e]);return this},add:function(){for(var e=[],t=arguments.length;t--;)e[t]=arguments[t];var a,i;for(a=0;a<e.length;a+=1){var s=L(e[a]);for(i=0;i<s.length;i+=1)this[this.length]=s[i],this.length+=1}return this},styles:function(){return this[0]?J.getComputedStyle(this[0],null):{}}};Object.keys(t).forEach(function(e){L.fn[e]=t[e]});var e,a,i,ee={deleteProps:function(e){var t=e;Object.keys(t).forEach(function(e){try{t[e]=null}catch(e){}try{delete t[e]}catch(e){}})},nextTick:function(e,t){return void 0===t&&(t=0),setTimeout(e,t)},now:function(){return Date.now()},getTranslate:function(e,t){var a,i,s;void 0===t&&(t="x");var r=J.getComputedStyle(e,null);return J.WebKitCSSMatrix?(6<(i=r.transform||r.webkitTransform).split(",").length&&(i=i.split(", ").map(function(e){return e.replace(",",".")}).join(", ")),s=new J.WebKitCSSMatrix("none"===i?"":i)):a=(s=r.MozTransform||r.OTransform||r.MsTransform||r.msTransform||r.transform||r.getPropertyValue("transform").replace("translate(","matrix(1, 0, 0, 1,")).toString().split(","),"x"===t&&(i=J.WebKitCSSMatrix?s.m41:16===a.length?parseFloat(a[12]):parseFloat(a[4])),"y"===t&&(i=J.WebKitCSSMatrix?s.m42:16===a.length?parseFloat(a[13]):parseFloat(a[5])),i||0},parseUrlQuery:function(e){var t,a,i,s,r={},n=e||J.location.href;if("string"==typeof n&&n.length)for(s=(a=(n=-1<n.indexOf("?")?n.replace(/\S*\?/,""):"").split("&").filter(function(e){return""!==e})).length,t=0;t<s;t+=1)i=a[t].replace(/#\S+/g,"").split("="),r[decodeURIComponent(i[0])]=void 0===i[1]?void 0:decodeURIComponent(i[1])||"";return r},isObject:function(e){return"object"==typeof e&&null!==e&&e.constructor&&e.constructor===Object},extend:function(){for(var e=[],t=arguments.length;t--;)e[t]=arguments[t];for(var a=Object(e[0]),i=1;i<e.length;i+=1){var s=e[i];if(null!=s)for(var r=Object.keys(Object(s)),n=0,o=r.length;n<o;n+=1){var l=r[n],d=Object.getOwnPropertyDescriptor(s,l);void 0!==d&&d.enumerable&&(ee.isObject(a[l])&&ee.isObject(s[l])?ee.extend(a[l],s[l]):!ee.isObject(a[l])&&ee.isObject(s[l])?(a[l]={},ee.extend(a[l],s[l])):a[l]=s[l])}}return a}},te=(i=f.createElement("div"),{touch:J.Modernizr&&!0===J.Modernizr.touch||!!(0<J.navigator.maxTouchPoints||"ontouchstart"in J||J.DocumentTouch&&f instanceof J.DocumentTouch),pointerEvents:!!(J.navigator.pointerEnabled||J.PointerEvent||"maxTouchPoints"in J.navigator),prefixedPointerEvents:!!J.navigator.msPointerEnabled,transition:(a=i.style,"transition"in a||"webkitTransition"in a||"MozTransition"in a),transforms3d:J.Modernizr&&!0===J.Modernizr.csstransforms3d||(e=i.style,"webkitPerspective"in e||"MozPerspective"in e||"OPerspective"in e||"MsPerspective"in e||"perspective"in e),flexbox:function(){for(var e=i.style,t="alignItems webkitAlignItems webkitBoxAlign msFlexAlign mozBoxAlign webkitFlexDirection msFlexDirection mozBoxDirection mozBoxOrient webkitBoxDirection webkitBoxOrient".split(" "),a=0;a<t.length;a+=1)if(t[a]in e)return!0;return!1}(),observer:"MutationObserver"in J||"WebkitMutationObserver"in J,passiveListener:function(){var e=!1;try{var t=Object.defineProperty({},"passive",{get:function(){e=!0}});J.addEventListener("testPassiveListener",null,t)}catch(e){}return e}(),gestures:"ongesturestart"in J}),s=function(e){void 0===e&&(e={});var t=this;t.params=e,t.eventsListeners={},t.params&&t.params.on&&Object.keys(t.params.on).forEach(function(e){t.on(e,t.params.on[e])})},n={components:{configurable:!0}};s.prototype.on=function(e,t,a){var i=this;if("function"!=typeof t)return i;var s=a?"unshift":"push";return e.split(" ").forEach(function(e){i.eventsListeners[e]||(i.eventsListeners[e]=[]),i.eventsListeners[e][s](t)}),i},s.prototype.once=function(i,s,e){var r=this;if("function"!=typeof s)return r;return r.on(i,function e(){for(var t=[],a=arguments.length;a--;)t[a]=arguments[a];s.apply(r,t),r.off(i,e)},e)},s.prototype.off=function(e,i){var s=this;return s.eventsListeners&&e.split(" ").forEach(function(a){void 0===i?s.eventsListeners[a]=[]:s.eventsListeners[a]&&s.eventsListeners[a].length&&s.eventsListeners[a].forEach(function(e,t){e===i&&s.eventsListeners[a].splice(t,1)})}),s},s.prototype.emit=function(){for(var e=[],t=arguments.length;t--;)e[t]=arguments[t];var a,i,s,r=this;return r.eventsListeners&&("string"==typeof e[0]||Array.isArray(e[0])?(a=e[0],i=e.slice(1,e.length),s=r):(a=e[0].events,i=e[0].data,s=e[0].context||r),(Array.isArray(a)?a:a.split(" ")).forEach(function(e){if(r.eventsListeners&&r.eventsListeners[e]){var t=[];r.eventsListeners[e].forEach(function(e){t.push(e)}),t.forEach(function(e){e.apply(s,i)})}})),r},s.prototype.useModulesParams=function(a){var i=this;i.modules&&Object.keys(i.modules).forEach(function(e){var t=i.modules[e];t.params&&ee.extend(a,t.params)})},s.prototype.useModules=function(i){void 0===i&&(i={});var s=this;s.modules&&Object.keys(s.modules).forEach(function(e){var a=s.modules[e],t=i[e]||{};a.instance&&Object.keys(a.instance).forEach(function(e){var t=a.instance[e];s[e]="function"==typeof t?t.bind(s):t}),a.on&&s.on&&Object.keys(a.on).forEach(function(e){s.on(e,a.on[e])}),a.create&&a.create.bind(s)(t)})},n.components.set=function(e){this.use&&this.use(e)},s.installModule=function(t){for(var e=[],a=arguments.length-1;0<a--;)e[a]=arguments[a+1];var i=this;i.prototype.modules||(i.prototype.modules={});var s=t.name||Object.keys(i.prototype.modules).length+"_"+ee.now();return(i.prototype.modules[s]=t).proto&&Object.keys(t.proto).forEach(function(e){i.prototype[e]=t.proto[e]}),t.static&&Object.keys(t.static).forEach(function(e){i[e]=t.static[e]}),t.install&&t.install.apply(i,e),i},s.use=function(e){for(var t=[],a=arguments.length-1;0<a--;)t[a]=arguments[a+1];var i=this;return Array.isArray(e)?(e.forEach(function(e){return i.installModule(e)}),i):i.installModule.apply(i,[e].concat(t))},Object.defineProperties(s,n);var o={updateSize:function(){var e,t,a=this,i=a.$el;e=void 0!==a.params.width?a.params.width:i[0].clientWidth,t=void 0!==a.params.height?a.params.height:i[0].clientHeight,0===e&&a.isHorizontal()||0===t&&a.isVertical()||(e=e-parseInt(i.css("padding-left"),10)-parseInt(i.css("padding-right"),10),t=t-parseInt(i.css("padding-top"),10)-parseInt(i.css("padding-bottom"),10),ee.extend(a,{width:e,height:t,size:a.isHorizontal()?e:t}))},updateSlides:function(){var e=this,t=e.params,a=e.$wrapperEl,i=e.size,s=e.rtlTranslate,r=e.wrongRTL,n=e.virtual&&t.virtual.enabled,o=n?e.virtual.slides.length:e.slides.length,l=a.children("."+e.params.slideClass),d=n?e.virtual.slides.length:l.length,p=[],c=[],u=[],h=t.slidesOffsetBefore;"function"==typeof h&&(h=t.slidesOffsetBefore.call(e));var v=t.slidesOffsetAfter;"function"==typeof v&&(v=t.slidesOffsetAfter.call(e));var f=e.snapGrid.length,m=e.snapGrid.length,g=t.spaceBetween,b=-h,w=0,y=0;if(void 0!==i){var x,T;"string"==typeof g&&0<=g.indexOf("%")&&(g=parseFloat(g.replace("%",""))/100*i),e.virtualSize=-g,s?l.css({marginLeft:"",marginTop:""}):l.css({marginRight:"",marginBottom:""}),1<t.slidesPerColumn&&(x=Math.floor(d/t.slidesPerColumn)===d/e.params.slidesPerColumn?d:Math.ceil(d/t.slidesPerColumn)*t.slidesPerColumn,"auto"!==t.slidesPerView&&"row"===t.slidesPerColumnFill&&(x=Math.max(x,t.slidesPerView*t.slidesPerColumn)));for(var E,S=t.slidesPerColumn,C=x/S,M=Math.floor(d/t.slidesPerColumn),k=0;k<d;k+=1){T=0;var P=l.eq(k);if(1<t.slidesPerColumn){var z=void 0,$=void 0,L=void 0;"column"===t.slidesPerColumnFill?(L=k-($=Math.floor(k/S))*S,(M<$||$===M&&L===S-1)&&S<=(L+=1)&&(L=0,$+=1),z=$+L*x/S,P.css({"-webkit-box-ordinal-group":z,"-moz-box-ordinal-group":z,"-ms-flex-order":z,"-webkit-order":z,order:z})):$=k-(L=Math.floor(k/C))*C,P.css("margin-"+(e.isHorizontal()?"top":"left"),0!==L&&t.spaceBetween&&t.spaceBetween+"px").attr("data-swiper-column",$).attr("data-swiper-row",L)}if("none"!==P.css("display")){if("auto"===t.slidesPerView){var I=J.getComputedStyle(P[0],null),D=P[0].style.transform,O=P[0].style.webkitTransform;if(D&&(P[0].style.transform="none"),O&&(P[0].style.webkitTransform="none"),t.roundLengths)T=e.isHorizontal()?P.outerWidth(!0):P.outerHeight(!0);else if(e.isHorizontal()){var A=parseFloat(I.getPropertyValue("width")),N=parseFloat(I.getPropertyValue("padding-left")),H=parseFloat(I.getPropertyValue("padding-right")),G=parseFloat(I.getPropertyValue("margin-left")),B=parseFloat(I.getPropertyValue("margin-right")),X=I.getPropertyValue("box-sizing");T=X&&"border-box"===X?A+G+B:A+N+H+G+B}else{var Y=parseFloat(I.getPropertyValue("height")),V=parseFloat(I.getPropertyValue("padding-top")),F=parseFloat(I.getPropertyValue("padding-bottom")),R=parseFloat(I.getPropertyValue("margin-top")),q=parseFloat(I.getPropertyValue("margin-bottom")),W=I.getPropertyValue("box-sizing");T=W&&"border-box"===W?Y+R+q:Y+V+F+R+q}D&&(P[0].style.transform=D),O&&(P[0].style.webkitTransform=O),t.roundLengths&&(T=Math.floor(T))}else T=(i-(t.slidesPerView-1)*g)/t.slidesPerView,t.roundLengths&&(T=Math.floor(T)),l[k]&&(e.isHorizontal()?l[k].style.width=T+"px":l[k].style.height=T+"px");l[k]&&(l[k].swiperSlideSize=T),u.push(T),t.centeredSlides?(b=b+T/2+w/2+g,0===w&&0!==k&&(b=b-i/2-g),0===k&&(b=b-i/2-g),Math.abs(b)<.001&&(b=0),t.roundLengths&&(b=Math.floor(b)),y%t.slidesPerGroup==0&&p.push(b),c.push(b)):(t.roundLengths&&(b=Math.floor(b)),y%t.slidesPerGroup==0&&p.push(b),c.push(b),b=b+T+g),e.virtualSize+=T+g,w=T,y+=1}}if(e.virtualSize=Math.max(e.virtualSize,i)+v,s&&r&&("slide"===t.effect||"coverflow"===t.effect)&&a.css({width:e.virtualSize+t.spaceBetween+"px"}),te.flexbox&&!t.setWrapperSize||(e.isHorizontal()?a.css({width:e.virtualSize+t.spaceBetween+"px"}):a.css({height:e.virtualSize+t.spaceBetween+"px"})),1<t.slidesPerColumn&&(e.virtualSize=(T+t.spaceBetween)*x,e.virtualSize=Math.ceil(e.virtualSize/t.slidesPerColumn)-t.spaceBetween,e.isHorizontal()?a.css({width:e.virtualSize+t.spaceBetween+"px"}):a.css({height:e.virtualSize+t.spaceBetween+"px"}),t.centeredSlides)){E=[];for(var j=0;j<p.length;j+=1){var U=p[j];t.roundLengths&&(U=Math.floor(U)),p[j]<e.virtualSize+p[0]&&E.push(U)}p=E}if(!t.centeredSlides){E=[];for(var K=0;K<p.length;K+=1){var _=p[K];t.roundLengths&&(_=Math.floor(_)),p[K]<=e.virtualSize-i&&E.push(_)}p=E,1<Math.floor(e.virtualSize-i)-Math.floor(p[p.length-1])&&p.push(e.virtualSize-i)}if(0===p.length&&(p=[0]),0!==t.spaceBetween&&(e.isHorizontal()?s?l.css({marginLeft:g+"px"}):l.css({marginRight:g+"px"}):l.css({marginBottom:g+"px"})),t.centerInsufficientSlides){var Z=0;if(u.forEach(function(e){Z+=e+(t.spaceBetween?t.spaceBetween:0)}),(Z-=t.spaceBetween)<i){var Q=(i-Z)/2;p.forEach(function(e,t){p[t]=e-Q}),c.forEach(function(e,t){c[t]=e+Q})}}ee.extend(e,{slides:l,snapGrid:p,slidesGrid:c,slidesSizesGrid:u}),d!==o&&e.emit("slidesLengthChange"),p.length!==f&&(e.params.watchOverflow&&e.checkOverflow(),e.emit("snapGridLengthChange")),c.length!==m&&e.emit("slidesGridLengthChange"),(t.watchSlidesProgress||t.watchSlidesVisibility)&&e.updateSlidesOffset()}},updateAutoHeight:function(e){var t,a=this,i=[],s=0;if("number"==typeof e?a.setTransition(e):!0===e&&a.setTransition(a.params.speed),"auto"!==a.params.slidesPerView&&1<a.params.slidesPerView)for(t=0;t<Math.ceil(a.params.slidesPerView);t+=1){var r=a.activeIndex+t;if(r>a.slides.length)break;i.push(a.slides.eq(r)[0])}else i.push(a.slides.eq(a.activeIndex)[0]);for(t=0;t<i.length;t+=1)if(void 0!==i[t]){var n=i[t].offsetHeight;s=s<n?n:s}s&&a.$wrapperEl.css("height",s+"px")},updateSlidesOffset:function(){for(var e=this.slides,t=0;t<e.length;t+=1)e[t].swiperSlideOffset=this.isHorizontal()?e[t].offsetLeft:e[t].offsetTop},updateSlidesProgress:function(e){void 0===e&&(e=this&&this.translate||0);var t=this,a=t.params,i=t.slides,s=t.rtlTranslate;if(0!==i.length){void 0===i[0].swiperSlideOffset&&t.updateSlidesOffset();var r=-e;s&&(r=e),i.removeClass(a.slideVisibleClass),t.visibleSlidesIndexes=[],t.visibleSlides=[];for(var n=0;n<i.length;n+=1){var o=i[n],l=(r+(a.centeredSlides?t.minTranslate():0)-o.swiperSlideOffset)/(o.swiperSlideSize+a.spaceBetween);if(a.watchSlidesVisibility){var d=-(r-o.swiperSlideOffset),p=d+t.slidesSizesGrid[n];(0<=d&&d<t.size||0<p&&p<=t.size||d<=0&&p>=t.size)&&(t.visibleSlides.push(o),t.visibleSlidesIndexes.push(n),i.eq(n).addClass(a.slideVisibleClass))}o.progress=s?-l:l}t.visibleSlides=L(t.visibleSlides)}},updateProgress:function(e){void 0===e&&(e=this&&this.translate||0);var t=this,a=t.params,i=t.maxTranslate()-t.minTranslate(),s=t.progress,r=t.isBeginning,n=t.isEnd,o=r,l=n;0===i?n=r=!(s=0):(r=(s=(e-t.minTranslate())/i)<=0,n=1<=s),ee.extend(t,{progress:s,isBeginning:r,isEnd:n}),(a.watchSlidesProgress||a.watchSlidesVisibility)&&t.updateSlidesProgress(e),r&&!o&&t.emit("reachBeginning toEdge"),n&&!l&&t.emit("reachEnd toEdge"),(o&&!r||l&&!n)&&t.emit("fromEdge"),t.emit("progress",s)},updateSlidesClasses:function(){var e,t=this,a=t.slides,i=t.params,s=t.$wrapperEl,r=t.activeIndex,n=t.realIndex,o=t.virtual&&i.virtual.enabled;a.removeClass(i.slideActiveClass+" "+i.slideNextClass+" "+i.slidePrevClass+" "+i.slideDuplicateActiveClass+" "+i.slideDuplicateNextClass+" "+i.slideDuplicatePrevClass),(e=o?t.$wrapperEl.find("."+i.slideClass+'[data-swiper-slide-index="'+r+'"]'):a.eq(r)).addClass(i.slideActiveClass),i.loop&&(e.hasClass(i.slideDuplicateClass)?s.children("."+i.slideClass+":not(."+i.slideDuplicateClass+')[data-swiper-slide-index="'+n+'"]').addClass(i.slideDuplicateActiveClass):s.children("."+i.slideClass+"."+i.slideDuplicateClass+'[data-swiper-slide-index="'+n+'"]').addClass(i.slideDuplicateActiveClass));var l=e.nextAll("."+i.slideClass).eq(0).addClass(i.slideNextClass);i.loop&&0===l.length&&(l=a.eq(0)).addClass(i.slideNextClass);var d=e.prevAll("."+i.slideClass).eq(0).addClass(i.slidePrevClass);i.loop&&0===d.length&&(d=a.eq(-1)).addClass(i.slidePrevClass),i.loop&&(l.hasClass(i.slideDuplicateClass)?s.children("."+i.slideClass+":not(."+i.slideDuplicateClass+')[data-swiper-slide-index="'+l.attr("data-swiper-slide-index")+'"]').addClass(i.slideDuplicateNextClass):s.children("."+i.slideClass+"."+i.slideDuplicateClass+'[data-swiper-slide-index="'+l.attr("data-swiper-slide-index")+'"]').addClass(i.slideDuplicateNextClass),d.hasClass(i.slideDuplicateClass)?s.children("."+i.slideClass+":not(."+i.slideDuplicateClass+')[data-swiper-slide-index="'+d.attr("data-swiper-slide-index")+'"]').addClass(i.slideDuplicatePrevClass):s.children("."+i.slideClass+"."+i.slideDuplicateClass+'[data-swiper-slide-index="'+d.attr("data-swiper-slide-index")+'"]').addClass(i.slideDuplicatePrevClass))},updateActiveIndex:function(e){var t,a=this,i=a.rtlTranslate?a.translate:-a.translate,s=a.slidesGrid,r=a.snapGrid,n=a.params,o=a.activeIndex,l=a.realIndex,d=a.snapIndex,p=e;if(void 0===p){for(var c=0;c<s.length;c+=1)void 0!==s[c+1]?i>=s[c]&&i<s[c+1]-(s[c+1]-s[c])/2?p=c:i>=s[c]&&i<s[c+1]&&(p=c+1):i>=s[c]&&(p=c);n.normalizeSlideIndex&&(p<0||void 0===p)&&(p=0)}if((t=0<=r.indexOf(i)?r.indexOf(i):Math.floor(p/n.slidesPerGroup))>=r.length&&(t=r.length-1),p!==o){var u=parseInt(a.slides.eq(p).attr("data-swiper-slide-index")||p,10);ee.extend(a,{snapIndex:t,realIndex:u,previousIndex:o,activeIndex:p}),a.emit("activeIndexChange"),a.emit("snapIndexChange"),l!==u&&a.emit("realIndexChange"),a.emit("slideChange")}else t!==d&&(a.snapIndex=t,a.emit("snapIndexChange"))},updateClickedSlide:function(e){var t=this,a=t.params,i=L(e.target).closest("."+a.slideClass)[0],s=!1;if(i)for(var r=0;r<t.slides.length;r+=1)t.slides[r]===i&&(s=!0);if(!i||!s)return t.clickedSlide=void 0,void(t.clickedIndex=void 0);t.clickedSlide=i,t.virtual&&t.params.virtual.enabled?t.clickedIndex=parseInt(L(i).attr("data-swiper-slide-index"),10):t.clickedIndex=L(i).index(),a.slideToClickedSlide&&void 0!==t.clickedIndex&&t.clickedIndex!==t.activeIndex&&t.slideToClickedSlide()}};var d={getTranslate:function(e){void 0===e&&(e=this.isHorizontal()?"x":"y");var t=this.params,a=this.rtlTranslate,i=this.translate,s=this.$wrapperEl;if(t.virtualTranslate)return a?-i:i;var r=ee.getTranslate(s[0],e);return a&&(r=-r),r||0},setTranslate:function(e,t){var a=this,i=a.rtlTranslate,s=a.params,r=a.$wrapperEl,n=a.progress,o=0,l=0;a.isHorizontal()?o=i?-e:e:l=e,s.roundLengths&&(o=Math.floor(o),l=Math.floor(l)),s.virtualTranslate||(te.transforms3d?r.transform("translate3d("+o+"px, "+l+"px, 0px)"):r.transform("translate("+o+"px, "+l+"px)")),a.previousTranslate=a.translate,a.translate=a.isHorizontal()?o:l;var d=a.maxTranslate()-a.minTranslate();(0===d?0:(e-a.minTranslate())/d)!==n&&a.updateProgress(e),a.emit("setTranslate",a.translate,t)},minTranslate:function(){return-this.snapGrid[0]},maxTranslate:function(){return-this.snapGrid[this.snapGrid.length-1]}};var p={setTransition:function(e,t){this.$wrapperEl.transition(e),this.emit("setTransition",e,t)},transitionStart:function(e,t){void 0===e&&(e=!0);var a=this,i=a.activeIndex,s=a.params,r=a.previousIndex;s.autoHeight&&a.updateAutoHeight();var n=t;if(n||(n=r<i?"next":i<r?"prev":"reset"),a.emit("transitionStart"),e&&i!==r){if("reset"===n)return void a.emit("slideResetTransitionStart");a.emit("slideChangeTransitionStart"),"next"===n?a.emit("slideNextTransitionStart"):a.emit("slidePrevTransitionStart")}},transitionEnd:function(e,t){void 0===e&&(e=!0);var a=this,i=a.activeIndex,s=a.previousIndex;a.animating=!1,a.setTransition(0);var r=t;if(r||(r=s<i?"next":i<s?"prev":"reset"),a.emit("transitionEnd"),e&&i!==s){if("reset"===r)return void a.emit("slideResetTransitionEnd");a.emit("slideChangeTransitionEnd"),"next"===r?a.emit("slideNextTransitionEnd"):a.emit("slidePrevTransitionEnd")}}};var c={slideTo:function(e,t,a,i){void 0===e&&(e=0),void 0===t&&(t=this.params.speed),void 0===a&&(a=!0);var s=this,r=e;r<0&&(r=0);var n=s.params,o=s.snapGrid,l=s.slidesGrid,d=s.previousIndex,p=s.activeIndex,c=s.rtlTranslate;if(s.animating&&n.preventInteractionOnTransition)return!1;var u=Math.floor(r/n.slidesPerGroup);u>=o.length&&(u=o.length-1),(p||n.initialSlide||0)===(d||0)&&a&&s.emit("beforeSlideChangeStart");var h,v=-o[u];if(s.updateProgress(v),n.normalizeSlideIndex)for(var f=0;f<l.length;f+=1)-Math.floor(100*v)>=Math.floor(100*l[f])&&(r=f);if(s.initialized&&r!==p){if(!s.allowSlideNext&&v<s.translate&&v<s.minTranslate())return!1;if(!s.allowSlidePrev&&v>s.translate&&v>s.maxTranslate()&&(p||0)!==r)return!1}return h=p<r?"next":r<p?"prev":"reset",c&&-v===s.translate||!c&&v===s.translate?(s.updateActiveIndex(r),n.autoHeight&&s.updateAutoHeight(),s.updateSlidesClasses(),"slide"!==n.effect&&s.setTranslate(v),"reset"!==h&&(s.transitionStart(a,h),s.transitionEnd(a,h)),!1):(0!==t&&te.transition?(s.setTransition(t),s.setTranslate(v),s.updateActiveIndex(r),s.updateSlidesClasses(),s.emit("beforeTransitionStart",t,i),s.transitionStart(a,h),s.animating||(s.animating=!0,s.onSlideToWrapperTransitionEnd||(s.onSlideToWrapperTransitionEnd=function(e){s&&!s.destroyed&&e.target===this&&(s.$wrapperEl[0].removeEventListener("transitionend",s.onSlideToWrapperTransitionEnd),s.$wrapperEl[0].removeEventListener("webkitTransitionEnd",s.onSlideToWrapperTransitionEnd),s.onSlideToWrapperTransitionEnd=null,delete s.onSlideToWrapperTransitionEnd,s.transitionEnd(a,h))}),s.$wrapperEl[0].addEventListener("transitionend",s.onSlideToWrapperTransitionEnd),s.$wrapperEl[0].addEventListener("webkitTransitionEnd",s.onSlideToWrapperTransitionEnd))):(s.setTransition(0),s.setTranslate(v),s.updateActiveIndex(r),s.updateSlidesClasses(),s.emit("beforeTransitionStart",t,i),s.transitionStart(a,h),s.transitionEnd(a,h)),!0)},slideToLoop:function(e,t,a,i){void 0===e&&(e=0),void 0===t&&(t=this.params.speed),void 0===a&&(a=!0);var s=e;return this.params.loop&&(s+=this.loopedSlides),this.slideTo(s,t,a,i)},slideNext:function(e,t,a){void 0===e&&(e=this.params.speed),void 0===t&&(t=!0);var i=this,s=i.params,r=i.animating;return s.loop?!r&&(i.loopFix(),i._clientLeft=i.$wrapperEl[0].clientLeft,i.slideTo(i.activeIndex+s.slidesPerGroup,e,t,a)):i.slideTo(i.activeIndex+s.slidesPerGroup,e,t,a)},slidePrev:function(e,t,a){void 0===e&&(e=this.params.speed),void 0===t&&(t=!0);var i=this,s=i.params,r=i.animating,n=i.snapGrid,o=i.slidesGrid,l=i.rtlTranslate;if(s.loop){if(r)return!1;i.loopFix(),i._clientLeft=i.$wrapperEl[0].clientLeft}function d(e){return e<0?-Math.floor(Math.abs(e)):Math.floor(e)}var p,c=d(l?i.translate:-i.translate),u=n.map(function(e){return d(e)}),h=(o.map(function(e){return d(e)}),n[u.indexOf(c)],n[u.indexOf(c)-1]);return void 0!==h&&(p=o.indexOf(h))<0&&(p=i.activeIndex-1),i.slideTo(p,e,t,a)},slideReset:function(e,t,a){return void 0===e&&(e=this.params.speed),void 0===t&&(t=!0),this.slideTo(this.activeIndex,e,t,a)},slideToClosest:function(e,t,a){void 0===e&&(e=this.params.speed),void 0===t&&(t=!0);var i=this,s=i.activeIndex,r=Math.floor(s/i.params.slidesPerGroup);if(r<i.snapGrid.length-1){var n=i.rtlTranslate?i.translate:-i.translate,o=i.snapGrid[r];(i.snapGrid[r+1]-o)/2<n-o&&(s=i.params.slidesPerGroup)}return i.slideTo(s,e,t,a)},slideToClickedSlide:function(){var e,t=this,a=t.params,i=t.$wrapperEl,s="auto"===a.slidesPerView?t.slidesPerViewDynamic():a.slidesPerView,r=t.clickedIndex;if(a.loop){if(t.animating)return;e=parseInt(L(t.clickedSlide).attr("data-swiper-slide-index"),10),a.centeredSlides?r<t.loopedSlides-s/2||r>t.slides.length-t.loopedSlides+s/2?(t.loopFix(),r=i.children("."+a.slideClass+'[data-swiper-slide-index="'+e+'"]:not(.'+a.slideDuplicateClass+")").eq(0).index(),ee.nextTick(function(){t.slideTo(r)})):t.slideTo(r):r>t.slides.length-s?(t.loopFix(),r=i.children("."+a.slideClass+'[data-swiper-slide-index="'+e+'"]:not(.'+a.slideDuplicateClass+")").eq(0).index(),ee.nextTick(function(){t.slideTo(r)})):t.slideTo(r)}else t.slideTo(r)}};var u={loopCreate:function(){var i=this,e=i.params,t=i.$wrapperEl;t.children("."+e.slideClass+"."+e.slideDuplicateClass).remove();var s=t.children("."+e.slideClass);if(e.loopFillGroupWithBlank){var a=e.slidesPerGroup-s.length%e.slidesPerGroup;if(a!==e.slidesPerGroup){for(var r=0;r<a;r+=1){var n=L(f.createElement("div")).addClass(e.slideClass+" "+e.slideBlankClass);t.append(n)}s=t.children("."+e.slideClass)}}"auto"!==e.slidesPerView||e.loopedSlides||(e.loopedSlides=s.length),i.loopedSlides=parseInt(e.loopedSlides||e.slidesPerView,10),i.loopedSlides+=e.loopAdditionalSlides,i.loopedSlides>s.length&&(i.loopedSlides=s.length);var o=[],l=[];s.each(function(e,t){var a=L(t);e<i.loopedSlides&&l.push(t),e<s.length&&e>=s.length-i.loopedSlides&&o.push(t),a.attr("data-swiper-slide-index",e)});for(var d=0;d<l.length;d+=1)t.append(L(l[d].cloneNode(!0)).addClass(e.slideDuplicateClass));for(var p=o.length-1;0<=p;p-=1)t.prepend(L(o[p].cloneNode(!0)).addClass(e.slideDuplicateClass))},loopFix:function(){var e,t=this,a=t.params,i=t.activeIndex,s=t.slides,r=t.loopedSlides,n=t.allowSlidePrev,o=t.allowSlideNext,l=t.snapGrid,d=t.rtlTranslate;t.allowSlidePrev=!0,t.allowSlideNext=!0;var p=-l[i]-t.getTranslate();i<r?(e=s.length-3*r+i,e+=r,t.slideTo(e,0,!1,!0)&&0!==p&&t.setTranslate((d?-t.translate:t.translate)-p)):("auto"===a.slidesPerView&&2*r<=i||i>=s.length-r)&&(e=-s.length+i+r,e+=r,t.slideTo(e,0,!1,!0)&&0!==p&&t.setTranslate((d?-t.translate:t.translate)-p));t.allowSlidePrev=n,t.allowSlideNext=o},loopDestroy:function(){var e=this.$wrapperEl,t=this.params,a=this.slides;e.children("."+t.slideClass+"."+t.slideDuplicateClass+",."+t.slideClass+"."+t.slideBlankClass).remove(),a.removeAttr("data-swiper-slide-index")}};var h={setGrabCursor:function(e){if(!(te.touch||!this.params.simulateTouch||this.params.watchOverflow&&this.isLocked)){var t=this.el;t.style.cursor="move",t.style.cursor=e?"-webkit-grabbing":"-webkit-grab",t.style.cursor=e?"-moz-grabbin":"-moz-grab",t.style.cursor=e?"grabbing":"grab"}},unsetGrabCursor:function(){te.touch||this.params.watchOverflow&&this.isLocked||(this.el.style.cursor="")}};var v={appendSlide:function(e){var t=this,a=t.$wrapperEl,i=t.params;if(i.loop&&t.loopDestroy(),"object"==typeof e&&"length"in e)for(var s=0;s<e.length;s+=1)e[s]&&a.append(e[s]);else a.append(e);i.loop&&t.loopCreate(),i.observer&&te.observer||t.update()},prependSlide:function(e){var t=this,a=t.params,i=t.$wrapperEl,s=t.activeIndex;a.loop&&t.loopDestroy();var r=s+1;if("object"==typeof e&&"length"in e){for(var n=0;n<e.length;n+=1)e[n]&&i.prepend(e[n]);r=s+e.length}else i.prepend(e);a.loop&&t.loopCreate(),a.observer&&te.observer||t.update(),t.slideTo(r,0,!1)},addSlide:function(e,t){var a=this,i=a.$wrapperEl,s=a.params,r=a.activeIndex;s.loop&&(r-=a.loopedSlides,a.loopDestroy(),a.slides=i.children("."+s.slideClass));var n=a.slides.length;if(e<=0)a.prependSlide(t);else if(n<=e)a.appendSlide(t);else{for(var o=e<r?r+1:r,l=[],d=n-1;e<=d;d-=1){var p=a.slides.eq(d);p.remove(),l.unshift(p)}if("object"==typeof t&&"length"in t){for(var c=0;c<t.length;c+=1)t[c]&&i.append(t[c]);o=e<r?r+t.length:r}else i.append(t);for(var u=0;u<l.length;u+=1)i.append(l[u]);s.loop&&a.loopCreate(),s.observer&&te.observer||a.update(),s.loop?a.slideTo(o+a.loopedSlides,0,!1):a.slideTo(o,0,!1)}},removeSlide:function(e){var t=this,a=t.params,i=t.$wrapperEl,s=t.activeIndex;a.loop&&(s-=t.loopedSlides,t.loopDestroy(),t.slides=i.children("."+a.slideClass));var r,n=s;if("object"==typeof e&&"length"in e){for(var o=0;o<e.length;o+=1)r=e[o],t.slides[r]&&t.slides.eq(r).remove(),r<n&&(n-=1);n=Math.max(n,0)}else r=e,t.slides[r]&&t.slides.eq(r).remove(),r<n&&(n-=1),n=Math.max(n,0);a.loop&&t.loopCreate(),a.observer&&te.observer||t.update(),a.loop?t.slideTo(n+t.loopedSlides,0,!1):t.slideTo(n,0,!1)},removeAllSlides:function(){for(var e=[],t=0;t<this.slides.length;t+=1)e.push(t);this.removeSlide(e)}},m=function(){var e=J.navigator.userAgent,t={ios:!1,android:!1,androidChrome:!1,desktop:!1,windows:!1,iphone:!1,ipod:!1,ipad:!1,cordova:J.cordova||J.phonegap,phonegap:J.cordova||J.phonegap},a=e.match(/(Windows Phone);?[\s\/]+([\d.]+)?/),i=e.match(/(Android);?[\s\/]+([\d.]+)?/),s=e.match(/(iPad).*OS\s([\d_]+)/),r=e.match(/(iPod)(.*OS\s([\d_]+))?/),n=!s&&e.match(/(iPhone\sOS|iOS)\s([\d_]+)/);if(a&&(t.os="windows",t.osVersion=a[2],t.windows=!0),i&&!a&&(t.os="android",t.osVersion=i[2],t.android=!0,t.androidChrome=0<=e.toLowerCase().indexOf("chrome")),(s||n||r)&&(t.os="ios",t.ios=!0),n&&!r&&(t.osVersion=n[2].replace(/_/g,"."),t.iphone=!0),s&&(t.osVersion=s[2].replace(/_/g,"."),t.ipad=!0),r&&(t.osVersion=r[3]?r[3].replace(/_/g,"."):null,t.iphone=!0),t.ios&&t.osVersion&&0<=e.indexOf("Version/")&&"10"===t.osVersion.split(".")[0]&&(t.osVersion=e.toLowerCase().split("version/")[1].split(" ")[0]),t.desktop=!(t.os||t.android||t.webView),t.webView=(n||s||r)&&e.match(/.*AppleWebKit(?!.*Safari)/i),t.os&&"ios"===t.os){var o=t.osVersion.split("."),l=f.querySelector('meta[name="viewport"]');t.minimalUi=!t.webView&&(r||n)&&(1*o[0]==7?1<=1*o[1]:7<1*o[0])&&l&&0<=l.getAttribute("content").indexOf("minimal-ui")}return t.pixelRatio=J.devicePixelRatio||1,t}();function g(){var e=this,t=e.params,a=e.el;if(!a||0!==a.offsetWidth){t.breakpoints&&e.setBreakpoint();var i=e.allowSlideNext,s=e.allowSlidePrev,r=e.snapGrid;if(e.allowSlideNext=!0,e.allowSlidePrev=!0,e.updateSize(),e.updateSlides(),t.freeMode){var n=Math.min(Math.max(e.translate,e.maxTranslate()),e.minTranslate());e.setTranslate(n),e.updateActiveIndex(),e.updateSlidesClasses(),t.autoHeight&&e.updateAutoHeight()}else e.updateSlidesClasses(),("auto"===t.slidesPerView||1<t.slidesPerView)&&e.isEnd&&!e.params.centeredSlides?e.slideTo(e.slides.length-1,0,!1,!0):e.slideTo(e.activeIndex,0,!1,!0);e.allowSlidePrev=s,e.allowSlideNext=i,e.params.watchOverflow&&r!==e.snapGrid&&e.checkOverflow()}}var b={attachEvents:function(){var e=this,t=e.params,a=e.touchEvents,i=e.el,s=e.wrapperEl;e.onTouchStart=function(e){var t=this,a=t.touchEventsData,i=t.params,s=t.touches;if(!t.animating||!i.preventInteractionOnTransition){var r=e;if(r.originalEvent&&(r=r.originalEvent),a.isTouchEvent="touchstart"===r.type,(a.isTouchEvent||!("which"in r)||3!==r.which)&&!(!a.isTouchEvent&&"button"in r&&0<r.button||a.isTouched&&a.isMoved))if(i.noSwiping&&L(r.target).closest(i.noSwipingSelector?i.noSwipingSelector:"."+i.noSwipingClass)[0])t.allowClick=!0;else if(!i.swipeHandler||L(r).closest(i.swipeHandler)[0]){s.currentX="touchstart"===r.type?r.targetTouches[0].pageX:r.pageX,s.currentY="touchstart"===r.type?r.targetTouches[0].pageY:r.pageY;var n=s.currentX,o=s.currentY,l=i.edgeSwipeDetection||i.iOSEdgeSwipeDetection,d=i.edgeSwipeThreshold||i.iOSEdgeSwipeThreshold;if(!l||!(n<=d||n>=J.screen.width-d)){if(ee.extend(a,{isTouched:!0,isMoved:!1,allowTouchCallbacks:!0,isScrolling:void 0,startMoving:void 0}),s.startX=n,s.startY=o,a.touchStartTime=ee.now(),t.allowClick=!0,t.updateSize(),t.swipeDirection=void 0,0<i.threshold&&(a.allowThresholdMove=!1),"touchstart"!==r.type){var p=!0;L(r.target).is(a.formElements)&&(p=!1),f.activeElement&&L(f.activeElement).is(a.formElements)&&f.activeElement!==r.target&&f.activeElement.blur();var c=p&&t.allowTouchMove&&i.touchStartPreventDefault;(i.touchStartForcePreventDefault||c)&&r.preventDefault()}t.emit("touchStart",r)}}}}.bind(e),e.onTouchMove=function(e){var t=this,a=t.touchEventsData,i=t.params,s=t.touches,r=t.rtlTranslate,n=e;if(n.originalEvent&&(n=n.originalEvent),a.isTouched){if(!a.isTouchEvent||"mousemove"!==n.type){var o="touchmove"===n.type?n.targetTouches[0].pageX:n.pageX,l="touchmove"===n.type?n.targetTouches[0].pageY:n.pageY;if(n.preventedByNestedSwiper)return s.startX=o,void(s.startY=l);if(!t.allowTouchMove)return t.allowClick=!1,void(a.isTouched&&(ee.extend(s,{startX:o,startY:l,currentX:o,currentY:l}),a.touchStartTime=ee.now()));if(a.isTouchEvent&&i.touchReleaseOnEdges&&!i.loop)if(t.isVertical()){if(l<s.startY&&t.translate<=t.maxTranslate()||l>s.startY&&t.translate>=t.minTranslate())return a.isTouched=!1,void(a.isMoved=!1)}else if(o<s.startX&&t.translate<=t.maxTranslate()||o>s.startX&&t.translate>=t.minTranslate())return;if(a.isTouchEvent&&f.activeElement&&n.target===f.activeElement&&L(n.target).is(a.formElements))return a.isMoved=!0,void(t.allowClick=!1);if(a.allowTouchCallbacks&&t.emit("touchMove",n),!(n.targetTouches&&1<n.targetTouches.length)){s.currentX=o,s.currentY=l;var d,p=s.currentX-s.startX,c=s.currentY-s.startY;if(!(t.params.threshold&&Math.sqrt(Math.pow(p,2)+Math.pow(c,2))<t.params.threshold))if(void 0===a.isScrolling&&(t.isHorizontal()&&s.currentY===s.startY||t.isVertical()&&s.currentX===s.startX?a.isScrolling=!1:25<=p*p+c*c&&(d=180*Math.atan2(Math.abs(c),Math.abs(p))/Math.PI,a.isScrolling=t.isHorizontal()?d>i.touchAngle:90-d>i.touchAngle)),a.isScrolling&&t.emit("touchMoveOpposite",n),void 0===a.startMoving&&(s.currentX===s.startX&&s.currentY===s.startY||(a.startMoving=!0)),a.isScrolling)a.isTouched=!1;else if(a.startMoving){t.allowClick=!1,n.preventDefault(),i.touchMoveStopPropagation&&!i.nested&&n.stopPropagation(),a.isMoved||(i.loop&&t.loopFix(),a.startTranslate=t.getTranslate(),t.setTransition(0),t.animating&&t.$wrapperEl.trigger("webkitTransitionEnd transitionend"),a.allowMomentumBounce=!1,!i.grabCursor||!0!==t.allowSlideNext&&!0!==t.allowSlidePrev||t.setGrabCursor(!0),t.emit("sliderFirstMove",n)),t.emit("sliderMove",n),a.isMoved=!0;var u=t.isHorizontal()?p:c;s.diff=u,u*=i.touchRatio,r&&(u=-u),t.swipeDirection=0<u?"prev":"next",a.currentTranslate=u+a.startTranslate;var h=!0,v=i.resistanceRatio;if(i.touchReleaseOnEdges&&(v=0),0<u&&a.currentTranslate>t.minTranslate()?(h=!1,i.resistance&&(a.currentTranslate=t.minTranslate()-1+Math.pow(-t.minTranslate()+a.startTranslate+u,v))):u<0&&a.currentTranslate<t.maxTranslate()&&(h=!1,i.resistance&&(a.currentTranslate=t.maxTranslate()+1-Math.pow(t.maxTranslate()-a.startTranslate-u,v))),h&&(n.preventedByNestedSwiper=!0),!t.allowSlideNext&&"next"===t.swipeDirection&&a.currentTranslate<a.startTranslate&&(a.currentTranslate=a.startTranslate),!t.allowSlidePrev&&"prev"===t.swipeDirection&&a.currentTranslate>a.startTranslate&&(a.currentTranslate=a.startTranslate),0<i.threshold){if(!(Math.abs(u)>i.threshold||a.allowThresholdMove))return void(a.currentTranslate=a.startTranslate);if(!a.allowThresholdMove)return a.allowThresholdMove=!0,s.startX=s.currentX,s.startY=s.currentY,a.currentTranslate=a.startTranslate,void(s.diff=t.isHorizontal()?s.currentX-s.startX:s.currentY-s.startY)}i.followFinger&&((i.freeMode||i.watchSlidesProgress||i.watchSlidesVisibility)&&(t.updateActiveIndex(),t.updateSlidesClasses()),i.freeMode&&(0===a.velocities.length&&a.velocities.push({position:s[t.isHorizontal()?"startX":"startY"],time:a.touchStartTime}),a.velocities.push({position:s[t.isHorizontal()?"currentX":"currentY"],time:ee.now()})),t.updateProgress(a.currentTranslate),t.setTranslate(a.currentTranslate))}}}}else a.startMoving&&a.isScrolling&&t.emit("touchMoveOpposite",n)}.bind(e),e.onTouchEnd=function(e){var t=this,a=t.touchEventsData,i=t.params,s=t.touches,r=t.rtlTranslate,n=t.$wrapperEl,o=t.slidesGrid,l=t.snapGrid,d=e;if(d.originalEvent&&(d=d.originalEvent),a.allowTouchCallbacks&&t.emit("touchEnd",d),a.allowTouchCallbacks=!1,!a.isTouched)return a.isMoved&&i.grabCursor&&t.setGrabCursor(!1),a.isMoved=!1,void(a.startMoving=!1);i.grabCursor&&a.isMoved&&a.isTouched&&(!0===t.allowSlideNext||!0===t.allowSlidePrev)&&t.setGrabCursor(!1);var p,c=ee.now(),u=c-a.touchStartTime;if(t.allowClick&&(t.updateClickedSlide(d),t.emit("tap",d),u<300&&300<c-a.lastClickTime&&(a.clickTimeout&&clearTimeout(a.clickTimeout),a.clickTimeout=ee.nextTick(function(){t&&!t.destroyed&&t.emit("click",d)},300)),u<300&&c-a.lastClickTime<300&&(a.clickTimeout&&clearTimeout(a.clickTimeout),t.emit("doubleTap",d))),a.lastClickTime=ee.now(),ee.nextTick(function(){t.destroyed||(t.allowClick=!0)}),!a.isTouched||!a.isMoved||!t.swipeDirection||0===s.diff||a.currentTranslate===a.startTranslate)return a.isTouched=!1,a.isMoved=!1,void(a.startMoving=!1);if(a.isTouched=!1,a.isMoved=!1,a.startMoving=!1,p=i.followFinger?r?t.translate:-t.translate:-a.currentTranslate,i.freeMode){if(p<-t.minTranslate())return void t.slideTo(t.activeIndex);if(p>-t.maxTranslate())return void(t.slides.length<l.length?t.slideTo(l.length-1):t.slideTo(t.slides.length-1));if(i.freeModeMomentum){if(1<a.velocities.length){var h=a.velocities.pop(),v=a.velocities.pop(),f=h.position-v.position,m=h.time-v.time;t.velocity=f/m,t.velocity/=2,Math.abs(t.velocity)<i.freeModeMinimumVelocity&&(t.velocity=0),(150<m||300<ee.now()-h.time)&&(t.velocity=0)}else t.velocity=0;t.velocity*=i.freeModeMomentumVelocityRatio,a.velocities.length=0;var g=1e3*i.freeModeMomentumRatio,b=t.velocity*g,w=t.translate+b;r&&(w=-w);var y,x,T=!1,E=20*Math.abs(t.velocity)*i.freeModeMomentumBounceRatio;if(w<t.maxTranslate())i.freeModeMomentumBounce?(w+t.maxTranslate()<-E&&(w=t.maxTranslate()-E),y=t.maxTranslate(),T=!0,a.allowMomentumBounce=!0):w=t.maxTranslate(),i.loop&&i.centeredSlides&&(x=!0);else if(w>t.minTranslate())i.freeModeMomentumBounce?(w-t.minTranslate()>E&&(w=t.minTranslate()+E),y=t.minTranslate(),T=!0,a.allowMomentumBounce=!0):w=t.minTranslate(),i.loop&&i.centeredSlides&&(x=!0);else if(i.freeModeSticky){for(var S,C=0;C<l.length;C+=1)if(l[C]>-w){S=C;break}w=-(w=Math.abs(l[S]-w)<Math.abs(l[S-1]-w)||"next"===t.swipeDirection?l[S]:l[S-1])}if(x&&t.once("transitionEnd",function(){t.loopFix()}),0!==t.velocity)g=r?Math.abs((-w-t.translate)/t.velocity):Math.abs((w-t.translate)/t.velocity);else if(i.freeModeSticky)return void t.slideToClosest();i.freeModeMomentumBounce&&T?(t.updateProgress(y),t.setTransition(g),t.setTranslate(w),t.transitionStart(!0,t.swipeDirection),t.animating=!0,n.transitionEnd(function(){t&&!t.destroyed&&a.allowMomentumBounce&&(t.emit("momentumBounce"),t.setTransition(i.speed),t.setTranslate(y),n.transitionEnd(function(){t&&!t.destroyed&&t.transitionEnd()}))})):t.velocity?(t.updateProgress(w),t.setTransition(g),t.setTranslate(w),t.transitionStart(!0,t.swipeDirection),t.animating||(t.animating=!0,n.transitionEnd(function(){t&&!t.destroyed&&t.transitionEnd()}))):t.updateProgress(w),t.updateActiveIndex(),t.updateSlidesClasses()}else if(i.freeModeSticky)return void t.slideToClosest();(!i.freeModeMomentum||u>=i.longSwipesMs)&&(t.updateProgress(),t.updateActiveIndex(),t.updateSlidesClasses())}else{for(var M=0,k=t.slidesSizesGrid[0],P=0;P<o.length;P+=i.slidesPerGroup)void 0!==o[P+i.slidesPerGroup]?p>=o[P]&&p<o[P+i.slidesPerGroup]&&(k=o[(M=P)+i.slidesPerGroup]-o[P]):p>=o[P]&&(M=P,k=o[o.length-1]-o[o.length-2]);var z=(p-o[M])/k;if(u>i.longSwipesMs){if(!i.longSwipes)return void t.slideTo(t.activeIndex);"next"===t.swipeDirection&&(z>=i.longSwipesRatio?t.slideTo(M+i.slidesPerGroup):t.slideTo(M)),"prev"===t.swipeDirection&&(z>1-i.longSwipesRatio?t.slideTo(M+i.slidesPerGroup):t.slideTo(M))}else{if(!i.shortSwipes)return void t.slideTo(t.activeIndex);"next"===t.swipeDirection&&t.slideTo(M+i.slidesPerGroup),"prev"===t.swipeDirection&&t.slideTo(M)}}}.bind(e),e.onClick=function(e){this.allowClick||(this.params.preventClicks&&e.preventDefault(),this.params.preventClicksPropagation&&this.animating&&(e.stopPropagation(),e.stopImmediatePropagation()))}.bind(e);var r="container"===t.touchEventsTarget?i:s,n=!!t.nested;if(te.touch||!te.pointerEvents&&!te.prefixedPointerEvents){if(te.touch){var o=!("touchstart"!==a.start||!te.passiveListener||!t.passiveListeners)&&{passive:!0,capture:!1};r.addEventListener(a.start,e.onTouchStart,o),r.addEventListener(a.move,e.onTouchMove,te.passiveListener?{passive:!1,capture:n}:n),r.addEventListener(a.end,e.onTouchEnd,o)}(t.simulateTouch&&!m.ios&&!m.android||t.simulateTouch&&!te.touch&&m.ios)&&(r.addEventListener("mousedown",e.onTouchStart,!1),f.addEventListener("mousemove",e.onTouchMove,n),f.addEventListener("mouseup",e.onTouchEnd,!1))}else r.addEventListener(a.start,e.onTouchStart,!1),f.addEventListener(a.move,e.onTouchMove,n),f.addEventListener(a.end,e.onTouchEnd,!1);(t.preventClicks||t.preventClicksPropagation)&&r.addEventListener("click",e.onClick,!0),e.on(m.ios||m.android?"resize orientationchange observerUpdate":"resize observerUpdate",g,!0)},detachEvents:function(){var e=this,t=e.params,a=e.touchEvents,i=e.el,s=e.wrapperEl,r="container"===t.touchEventsTarget?i:s,n=!!t.nested;if(te.touch||!te.pointerEvents&&!te.prefixedPointerEvents){if(te.touch){var o=!("onTouchStart"!==a.start||!te.passiveListener||!t.passiveListeners)&&{passive:!0,capture:!1};r.removeEventListener(a.start,e.onTouchStart,o),r.removeEventListener(a.move,e.onTouchMove,n),r.removeEventListener(a.end,e.onTouchEnd,o)}(t.simulateTouch&&!m.ios&&!m.android||t.simulateTouch&&!te.touch&&m.ios)&&(r.removeEventListener("mousedown",e.onTouchStart,!1),f.removeEventListener("mousemove",e.onTouchMove,n),f.removeEventListener("mouseup",e.onTouchEnd,!1))}else r.removeEventListener(a.start,e.onTouchStart,!1),f.removeEventListener(a.move,e.onTouchMove,n),f.removeEventListener(a.end,e.onTouchEnd,!1);(t.preventClicks||t.preventClicksPropagation)&&r.removeEventListener("click",e.onClick,!0),e.off(m.ios||m.android?"resize orientationchange observerUpdate":"resize observerUpdate",g)}};var w,y={setBreakpoint:function(){var e=this,t=e.activeIndex,a=e.initialized,i=e.loopedSlides;void 0===i&&(i=0);var s=e.params,r=s.breakpoints;if(r&&(!r||0!==Object.keys(r).length)){var n=e.getBreakpoint(r);if(n&&e.currentBreakpoint!==n){var o=n in r?r[n]:void 0;o&&["slidesPerView","spaceBetween","slidesPerGroup"].forEach(function(e){var t=o[e];void 0!==t&&(o[e]="slidesPerView"!==e||"AUTO"!==t&&"auto"!==t?"slidesPerView"===e?parseFloat(t):parseInt(t,10):"auto")});var l=o||e.originalParams,d=s.loop&&l.slidesPerView!==s.slidesPerView;ee.extend(e.params,l),ee.extend(e,{allowTouchMove:e.params.allowTouchMove,allowSlideNext:e.params.allowSlideNext,allowSlidePrev:e.params.allowSlidePrev}),e.currentBreakpoint=n,d&&a&&(e.loopDestroy(),e.loopCreate(),e.updateSlides(),e.slideTo(t-i+e.loopedSlides,0,!1)),e.emit("breakpoint",l)}}},getBreakpoint:function(e){if(e){var t=!1,a=[];Object.keys(e).forEach(function(e){a.push(e)}),a.sort(function(e,t){return parseInt(e,10)-parseInt(t,10)});for(var i=0;i<a.length;i+=1){var s=a[i];this.params.breakpointsInverse?s<=J.innerWidth&&(t=s):s>=J.innerWidth&&!t&&(t=s)}return t||"max"}}},I={isIE:!!J.navigator.userAgent.match(/Trident/g)||!!J.navigator.userAgent.match(/MSIE/g),isEdge:!!J.navigator.userAgent.match(/Edge/g),isSafari:(w=J.navigator.userAgent.toLowerCase(),0<=w.indexOf("safari")&&w.indexOf("chrome")<0&&w.indexOf("android")<0),isUiWebView:/(iPhone|iPod|iPad).*AppleWebKit(?!.*Safari)/i.test(J.navigator.userAgent)};var x={init:!0,direction:"horizontal",touchEventsTarget:"container",initialSlide:0,speed:300,preventInteractionOnTransition:!1,edgeSwipeDetection:!1,edgeSwipeThreshold:20,freeMode:!1,freeModeMomentum:!0,freeModeMomentumRatio:1,freeModeMomentumBounce:!0,freeModeMomentumBounceRatio:1,freeModeMomentumVelocityRatio:1,freeModeSticky:!1,freeModeMinimumVelocity:.02,autoHeight:!1,setWrapperSize:!1,virtualTranslate:!1,effect:"slide",breakpoints:void 0,breakpointsInverse:!1,spaceBetween:0,slidesPerView:1,slidesPerColumn:1,slidesPerColumnFill:"column",slidesPerGroup:1,centeredSlides:!1,slidesOffsetBefore:0,slidesOffsetAfter:0,normalizeSlideIndex:!0,centerInsufficientSlides:!1,watchOverflow:!1,roundLengths:!1,touchRatio:1,touchAngle:45,simulateTouch:!0,shortSwipes:!0,longSwipes:!0,longSwipesRatio:.5,longSwipesMs:300,followFinger:!0,allowTouchMove:!0,threshold:0,touchMoveStopPropagation:!0,touchStartPreventDefault:!0,touchStartForcePreventDefault:!1,touchReleaseOnEdges:!1,uniqueNavElements:!0,resistance:!0,resistanceRatio:.85,watchSlidesProgress:!1,watchSlidesVisibility:!1,grabCursor:!1,preventClicks:!0,preventClicksPropagation:!0,slideToClickedSlide:!1,preloadImages:!0,updateOnImagesReady:!0,loop:!1,loopAdditionalSlides:0,loopedSlides:null,loopFillGroupWithBlank:!1,allowSlidePrev:!0,allowSlideNext:!0,swipeHandler:null,noSwiping:!0,noSwipingClass:"swiper-no-swiping",noSwipingSelector:null,passiveListeners:!0,containerModifierClass:"swiper-container-",slideClass:"swiper-slide",slideBlankClass:"swiper-slide-invisible-blank",slideActiveClass:"swiper-slide-active",slideDuplicateActiveClass:"swiper-slide-duplicate-active",slideVisibleClass:"swiper-slide-visible",slideDuplicateClass:"swiper-slide-duplicate",slideNextClass:"swiper-slide-next",slideDuplicateNextClass:"swiper-slide-duplicate-next",slidePrevClass:"swiper-slide-prev",slideDuplicatePrevClass:"swiper-slide-duplicate-prev",wrapperClass:"swiper-wrapper",runCallbacksOnInit:!0},T={update:o,translate:d,transition:p,slide:c,loop:u,grabCursor:h,manipulation:v,events:b,breakpoints:y,checkOverflow:{checkOverflow:function(){var e=this,t=e.isLocked;e.isLocked=1===e.snapGrid.length,e.allowSlideNext=!e.isLocked,e.allowSlidePrev=!e.isLocked,t!==e.isLocked&&e.emit(e.isLocked?"lock":"unlock"),t&&t!==e.isLocked&&(e.isEnd=!1,e.navigation.update())}},classes:{addClasses:function(){var t=this.classNames,a=this.params,e=this.rtl,i=this.$el,s=[];s.push(a.direction),a.freeMode&&s.push("free-mode"),te.flexbox||s.push("no-flexbox"),a.autoHeight&&s.push("autoheight"),e&&s.push("rtl"),1<a.slidesPerColumn&&s.push("multirow"),m.android&&s.push("android"),m.ios&&s.push("ios"),(I.isIE||I.isEdge)&&(te.pointerEvents||te.prefixedPointerEvents)&&s.push("wp8-"+a.direction),s.forEach(function(e){t.push(a.containerModifierClass+e)}),i.addClass(t.join(" "))},removeClasses:function(){var e=this.$el,t=this.classNames;e.removeClass(t.join(" "))}},images:{loadImage:function(e,t,a,i,s,r){var n;function o(){r&&r()}e.complete&&s?o():t?((n=new J.Image).onload=o,n.onerror=o,i&&(n.sizes=i),a&&(n.srcset=a),t&&(n.src=t)):o()},preloadImages:function(){var e=this;function t(){null!=e&&e&&!e.destroyed&&(void 0!==e.imagesLoaded&&(e.imagesLoaded+=1),e.imagesLoaded===e.imagesToLoad.length&&(e.params.updateOnImagesReady&&e.update(),e.emit("imagesReady")))}e.imagesToLoad=e.$el.find("img");for(var a=0;a<e.imagesToLoad.length;a+=1){var i=e.imagesToLoad[a];e.loadImage(i,i.currentSrc||i.getAttribute("src"),i.srcset||i.getAttribute("srcset"),i.sizes||i.getAttribute("sizes"),!0,t)}}}},E={},S=function(u){function h(){for(var e,t,s,a=[],i=arguments.length;i--;)a[i]=arguments[i];1===a.length&&a[0].constructor&&a[0].constructor===Object?s=a[0]:(t=(e=a)[0],s=e[1]),s||(s={}),s=ee.extend({},s),t&&!s.el&&(s.el=t),u.call(this,s),Object.keys(T).forEach(function(t){Object.keys(T[t]).forEach(function(e){h.prototype[e]||(h.prototype[e]=T[t][e])})});var r=this;void 0===r.modules&&(r.modules={}),Object.keys(r.modules).forEach(function(e){var t=r.modules[e];if(t.params){var a=Object.keys(t.params)[0],i=t.params[a];if("object"!=typeof i||null===i)return;if(!(a in s&&"enabled"in i))return;!0===s[a]&&(s[a]={enabled:!0}),"object"!=typeof s[a]||"enabled"in s[a]||(s[a].enabled=!0),s[a]||(s[a]={enabled:!1})}});var n=ee.extend({},x);r.useModulesParams(n),r.params=ee.extend({},n,E,s),r.originalParams=ee.extend({},r.params),r.passedParams=ee.extend({},s);var o=(r.$=L)(r.params.el);if(t=o[0]){if(1<o.length){var l=[];return o.each(function(e,t){var a=ee.extend({},s,{el:t});l.push(new h(a))}),l}t.swiper=r,o.data("swiper",r);var d,p,c=o.children("."+r.params.wrapperClass);return ee.extend(r,{$el:o,el:t,$wrapperEl:c,wrapperEl:c[0],classNames:[],slides:L(),slidesGrid:[],snapGrid:[],slidesSizesGrid:[],isHorizontal:function(){return"horizontal"===r.params.direction},isVertical:function(){return"vertical"===r.params.direction},rtl:"rtl"===t.dir.toLowerCase()||"rtl"===o.css("direction"),rtlTranslate:"horizontal"===r.params.direction&&("rtl"===t.dir.toLowerCase()||"rtl"===o.css("direction")),wrongRTL:"-webkit-box"===c.css("display"),activeIndex:0,realIndex:0,isBeginning:!0,isEnd:!1,translate:0,previousTranslate:0,progress:0,velocity:0,animating:!1,allowSlideNext:r.params.allowSlideNext,allowSlidePrev:r.params.allowSlidePrev,touchEvents:(d=["touchstart","touchmove","touchend"],p=["mousedown","mousemove","mouseup"],te.pointerEvents?p=["pointerdown","pointermove","pointerup"]:te.prefixedPointerEvents&&(p=["MSPointerDown","MSPointerMove","MSPointerUp"]),r.touchEventsTouch={start:d[0],move:d[1],end:d[2]},r.touchEventsDesktop={start:p[0],move:p[1],end:p[2]},te.touch||!r.params.simulateTouch?r.touchEventsTouch:r.touchEventsDesktop),touchEventsData:{isTouched:void 0,isMoved:void 0,allowTouchCallbacks:void 0,touchStartTime:void 0,isScrolling:void 0,currentTranslate:void 0,startTranslate:void 0,allowThresholdMove:void 0,formElements:"input, select, option, textarea, button, video",lastClickTime:ee.now(),clickTimeout:void 0,velocities:[],allowMomentumBounce:void 0,isTouchEvent:void 0,startMoving:void 0},allowClick:!0,allowTouchMove:r.params.allowTouchMove,touches:{startX:0,startY:0,currentX:0,currentY:0,diff:0},imagesToLoad:[],imagesLoaded:0}),r.useModules(),r.params.init&&r.init(),r}}u&&(h.__proto__=u);var e={extendedDefaults:{configurable:!0},defaults:{configurable:!0},Class:{configurable:!0},$:{configurable:!0}};return((h.prototype=Object.create(u&&u.prototype)).constructor=h).prototype.slidesPerViewDynamic=function(){var e=this,t=e.params,a=e.slides,i=e.slidesGrid,s=e.size,r=e.activeIndex,n=1;if(t.centeredSlides){for(var o,l=a[r].swiperSlideSize,d=r+1;d<a.length;d+=1)a[d]&&!o&&(n+=1,s<(l+=a[d].swiperSlideSize)&&(o=!0));for(var p=r-1;0<=p;p-=1)a[p]&&!o&&(n+=1,s<(l+=a[p].swiperSlideSize)&&(o=!0))}else for(var c=r+1;c<a.length;c+=1)i[c]-i[r]<s&&(n+=1);return n},h.prototype.update=function(){var a=this;if(a&&!a.destroyed){var e=a.snapGrid,t=a.params;t.breakpoints&&a.setBreakpoint(),a.updateSize(),a.updateSlides(),a.updateProgress(),a.updateSlidesClasses(),a.params.freeMode?(i(),a.params.autoHeight&&a.updateAutoHeight()):(("auto"===a.params.slidesPerView||1<a.params.slidesPerView)&&a.isEnd&&!a.params.centeredSlides?a.slideTo(a.slides.length-1,0,!1,!0):a.slideTo(a.activeIndex,0,!1,!0))||i(),t.watchOverflow&&e!==a.snapGrid&&a.checkOverflow(),a.emit("update")}function i(){var e=a.rtlTranslate?-1*a.translate:a.translate,t=Math.min(Math.max(e,a.maxTranslate()),a.minTranslate());a.setTranslate(t),a.updateActiveIndex(),a.updateSlidesClasses()}},h.prototype.init=function(){var e=this;e.initialized||(e.emit("beforeInit"),e.params.breakpoints&&e.setBreakpoint(),e.addClasses(),e.params.loop&&e.loopCreate(),e.updateSize(),e.updateSlides(),e.params.watchOverflow&&e.checkOverflow(),e.params.grabCursor&&e.setGrabCursor(),e.params.preloadImages&&e.preloadImages(),e.params.loop?e.slideTo(e.params.initialSlide+e.loopedSlides,0,e.params.runCallbacksOnInit):e.slideTo(e.params.initialSlide,0,e.params.runCallbacksOnInit),e.attachEvents(),e.initialized=!0,e.emit("init"))},h.prototype.destroy=function(e,t){void 0===e&&(e=!0),void 0===t&&(t=!0);var a=this,i=a.params,s=a.$el,r=a.$wrapperEl,n=a.slides;return void 0===a.params||a.destroyed||(a.emit("beforeDestroy"),a.initialized=!1,a.detachEvents(),i.loop&&a.loopDestroy(),t&&(a.removeClasses(),s.removeAttr("style"),r.removeAttr("style"),n&&n.length&&n.removeClass([i.slideVisibleClass,i.slideActiveClass,i.slideNextClass,i.slidePrevClass].join(" ")).removeAttr("style").removeAttr("data-swiper-slide-index").removeAttr("data-swiper-column").removeAttr("data-swiper-row")),a.emit("destroy"),Object.keys(a.eventsListeners).forEach(function(e){a.off(e)}),!1!==e&&(a.$el[0].swiper=null,a.$el.data("swiper",null),ee.deleteProps(a)),a.destroyed=!0),null},h.extendDefaults=function(e){ee.extend(E,e)},e.extendedDefaults.get=function(){return E},e.defaults.get=function(){return x},e.Class.get=function(){return u},e.$.get=function(){return L},Object.defineProperties(h,e),h}(s),C={name:"device",proto:{device:m},static:{device:m}},M={name:"support",proto:{support:te},static:{support:te}},k={name:"browser",proto:{browser:I},static:{browser:I}},P={name:"resize",create:function(){var e=this;ee.extend(e,{resize:{resizeHandler:function(){e&&!e.destroyed&&e.initialized&&(e.emit("beforeResize"),e.emit("resize"))},orientationChangeHandler:function(){e&&!e.destroyed&&e.initialized&&e.emit("orientationchange")}}})},on:{init:function(){J.addEventListener("resize",this.resize.resizeHandler),J.addEventListener("orientationchange",this.resize.orientationChangeHandler)},destroy:function(){J.removeEventListener("resize",this.resize.resizeHandler),J.removeEventListener("orientationchange",this.resize.orientationChangeHandler)}}},z={func:J.MutationObserver||J.WebkitMutationObserver,attach:function(e,t){void 0===t&&(t={});var a=this,i=new z.func(function(e){if(1!==e.length){var t=function(){a.emit("observerUpdate",e[0])};J.requestAnimationFrame?J.requestAnimationFrame(t):J.setTimeout(t,0)}else a.emit("observerUpdate",e[0])});i.observe(e,{attributes:void 0===t.attributes||t.attributes,childList:void 0===t.childList||t.childList,characterData:void 0===t.characterData||t.characterData}),a.observer.observers.push(i)},init:function(){var e=this;if(te.observer&&e.params.observer){if(e.params.observeParents)for(var t=e.$el.parents(),a=0;a<t.length;a+=1)e.observer.attach(t[a]);e.observer.attach(e.$el[0],{childList:e.params.observeSlideChildren}),e.observer.attach(e.$wrapperEl[0],{attributes:!1})}},destroy:function(){this.observer.observers.forEach(function(e){e.disconnect()}),this.observer.observers=[]}},$={name:"observer",params:{observer:!1,observeParents:!1,observeSlideChildren:!1},create:function(){ee.extend(this,{observer:{init:z.init.bind(this),attach:z.attach.bind(this),destroy:z.destroy.bind(this),observers:[]}})},on:{init:function(){this.observer.init()},destroy:function(){this.observer.destroy()}}},D={update:function(e){var t=this,a=t.params,i=a.slidesPerView,s=a.slidesPerGroup,r=a.centeredSlides,n=t.params.virtual,o=n.addSlidesBefore,l=n.addSlidesAfter,d=t.virtual,p=d.from,c=d.to,u=d.slides,h=d.slidesGrid,v=d.renderSlide,f=d.offset;t.updateActiveIndex();var m,g,b,w=t.activeIndex||0;m=t.rtlTranslate?"right":t.isHorizontal()?"left":"top",r?(g=Math.floor(i/2)+s+o,b=Math.floor(i/2)+s+l):(g=i+(s-1)+o,b=s+l);var y=Math.max((w||0)-b,0),x=Math.min((w||0)+g,u.length-1),T=(t.slidesGrid[y]||0)-(t.slidesGrid[0]||0);function E(){t.updateSlides(),t.updateProgress(),t.updateSlidesClasses(),t.lazy&&t.params.lazy.enabled&&t.lazy.load()}if(ee.extend(t.virtual,{from:y,to:x,offset:T,slidesGrid:t.slidesGrid}),p===y&&c===x&&!e)return t.slidesGrid!==h&&T!==f&&t.slides.css(m,T+"px"),void t.updateProgress();if(t.params.virtual.renderExternal)return t.params.virtual.renderExternal.call(t,{offset:T,from:y,to:x,slides:function(){for(var e=[],t=y;t<=x;t+=1)e.push(u[t]);return e}()}),void E();var S=[],C=[];if(e)t.$wrapperEl.find("."+t.params.slideClass).remove();else for(var M=p;M<=c;M+=1)(M<y||x<M)&&t.$wrapperEl.find("."+t.params.slideClass+'[data-swiper-slide-index="'+M+'"]').remove();for(var k=0;k<u.length;k+=1)y<=k&&k<=x&&(void 0===c||e?C.push(k):(c<k&&C.push(k),k<p&&S.push(k)));C.forEach(function(e){t.$wrapperEl.append(v(u[e],e))}),S.sort(function(e,t){return t-e}).forEach(function(e){t.$wrapperEl.prepend(v(u[e],e))}),t.$wrapperEl.children(".swiper-slide").css(m,T+"px"),E()},renderSlide:function(e,t){var a=this,i=a.params.virtual;if(i.cache&&a.virtual.cache[t])return a.virtual.cache[t];var s=i.renderSlide?L(i.renderSlide.call(a,e,t)):L('<div class="'+a.params.slideClass+'" data-swiper-slide-index="'+t+'">'+e+"</div>");return s.attr("data-swiper-slide-index")||s.attr("data-swiper-slide-index",t),i.cache&&(a.virtual.cache[t]=s),s},appendSlide:function(e){this.virtual.slides.push(e),this.virtual.update(!0)},prependSlide:function(e){var t=this;if(t.virtual.slides.unshift(e),t.params.virtual.cache){var a=t.virtual.cache,i={};Object.keys(a).forEach(function(e){i[e+1]=a[e]}),t.virtual.cache=i}t.virtual.update(!0),t.slideNext(0)}},O={name:"virtual",params:{virtual:{enabled:!1,slides:[],cache:!0,renderSlide:null,renderExternal:null,addSlidesBefore:0,addSlidesAfter:0}},create:function(){var e=this;ee.extend(e,{virtual:{update:D.update.bind(e),appendSlide:D.appendSlide.bind(e),prependSlide:D.prependSlide.bind(e),renderSlide:D.renderSlide.bind(e),slides:e.params.virtual.slides,cache:{}}})},on:{beforeInit:function(){var e=this;if(e.params.virtual.enabled){e.classNames.push(e.params.containerModifierClass+"virtual");var t={watchSlidesProgress:!0};ee.extend(e.params,t),ee.extend(e.originalParams,t),e.params.initialSlide||e.virtual.update()}},setTranslate:function(){this.params.virtual.enabled&&this.virtual.update()}}},A={handle:function(e){var t=this,a=t.rtlTranslate,i=e;i.originalEvent&&(i=i.originalEvent);var s=i.keyCode||i.charCode;if(!t.allowSlideNext&&(t.isHorizontal()&&39===s||t.isVertical()&&40===s))return!1;if(!t.allowSlidePrev&&(t.isHorizontal()&&37===s||t.isVertical()&&38===s))return!1;if(!(i.shiftKey||i.altKey||i.ctrlKey||i.metaKey||f.activeElement&&f.activeElement.nodeName&&("input"===f.activeElement.nodeName.toLowerCase()||"textarea"===f.activeElement.nodeName.toLowerCase()))){if(t.params.keyboard.onlyInViewport&&(37===s||39===s||38===s||40===s)){var r=!1;if(0<t.$el.parents("."+t.params.slideClass).length&&0===t.$el.parents("."+t.params.slideActiveClass).length)return;var n=J.innerWidth,o=J.innerHeight,l=t.$el.offset();a&&(l.left-=t.$el[0].scrollLeft);for(var d=[[l.left,l.top],[l.left+t.width,l.top],[l.left,l.top+t.height],[l.left+t.width,l.top+t.height]],p=0;p<d.length;p+=1){var c=d[p];0<=c[0]&&c[0]<=n&&0<=c[1]&&c[1]<=o&&(r=!0)}if(!r)return}t.isHorizontal()?(37!==s&&39!==s||(i.preventDefault?i.preventDefault():i.returnValue=!1),(39===s&&!a||37===s&&a)&&t.slideNext(),(37===s&&!a||39===s&&a)&&t.slidePrev()):(38!==s&&40!==s||(i.preventDefault?i.preventDefault():i.returnValue=!1),40===s&&t.slideNext(),38===s&&t.slidePrev()),t.emit("keyPress",s)}},enable:function(){this.keyboard.enabled||(L(f).on("keydown",this.keyboard.handle),this.keyboard.enabled=!0)},disable:function(){this.keyboard.enabled&&(L(f).off("keydown",this.keyboard.handle),this.keyboard.enabled=!1)}},N={name:"keyboard",params:{keyboard:{enabled:!1,onlyInViewport:!0}},create:function(){ee.extend(this,{keyboard:{enabled:!1,enable:A.enable.bind(this),disable:A.disable.bind(this),handle:A.handle.bind(this)}})},on:{init:function(){this.params.keyboard.enabled&&this.keyboard.enable()},destroy:function(){this.keyboard.enabled&&this.keyboard.disable()}}};var H={lastScrollTime:ee.now(),event:-1<J.navigator.userAgent.indexOf("firefox")?"DOMMouseScroll":function(){var e="onwheel",t=e in f;if(!t){var a=f.createElement("div");a.setAttribute(e,"return;"),t="function"==typeof a[e]}return!t&&f.implementation&&f.implementation.hasFeature&&!0!==f.implementation.hasFeature("","")&&(t=f.implementation.hasFeature("Events.wheel","3.0")),t}()?"wheel":"mousewheel",normalize:function(e){var t=0,a=0,i=0,s=0;return"detail"in e&&(a=e.detail),"wheelDelta"in e&&(a=-e.wheelDelta/120),"wheelDeltaY"in e&&(a=-e.wheelDeltaY/120),"wheelDeltaX"in e&&(t=-e.wheelDeltaX/120),"axis"in e&&e.axis===e.HORIZONTAL_AXIS&&(t=a,a=0),i=10*t,s=10*a,"deltaY"in e&&(s=e.deltaY),"deltaX"in e&&(i=e.deltaX),(i||s)&&e.deltaMode&&(1===e.deltaMode?(i*=40,s*=40):(i*=800,s*=800)),i&&!t&&(t=i<1?-1:1),s&&!a&&(a=s<1?-1:1),{spinX:t,spinY:a,pixelX:i,pixelY:s}},handleMouseEnter:function(){this.mouseEntered=!0},handleMouseLeave:function(){this.mouseEntered=!1},handle:function(e){var t=e,a=this,i=a.params.mousewheel;if(!a.mouseEntered&&!i.releaseOnEdges)return!0;t.originalEvent&&(t=t.originalEvent);var s=0,r=a.rtlTranslate?-1:1,n=H.normalize(t);if(i.forceToAxis)if(a.isHorizontal()){if(!(Math.abs(n.pixelX)>Math.abs(n.pixelY)))return!0;s=n.pixelX*r}else{if(!(Math.abs(n.pixelY)>Math.abs(n.pixelX)))return!0;s=n.pixelY}else s=Math.abs(n.pixelX)>Math.abs(n.pixelY)?-n.pixelX*r:-n.pixelY;if(0===s)return!0;if(i.invert&&(s=-s),a.params.freeMode){a.params.loop&&a.loopFix();var o=a.getTranslate()+s*i.sensitivity,l=a.isBeginning,d=a.isEnd;if(o>=a.minTranslate()&&(o=a.minTranslate()),o<=a.maxTranslate()&&(o=a.maxTranslate()),a.setTransition(0),a.setTranslate(o),a.updateProgress(),a.updateActiveIndex(),a.updateSlidesClasses(),(!l&&a.isBeginning||!d&&a.isEnd)&&a.updateSlidesClasses(),a.params.freeModeSticky&&(clearTimeout(a.mousewheel.timeout),a.mousewheel.timeout=ee.nextTick(function(){a.slideToClosest()},300)),a.emit("scroll",t),a.params.autoplay&&a.params.autoplayDisableOnInteraction&&a.autoplay.stop(),o===a.minTranslate()||o===a.maxTranslate())return!0}else{if(60<ee.now()-a.mousewheel.lastScrollTime)if(s<0)if(a.isEnd&&!a.params.loop||a.animating){if(i.releaseOnEdges)return!0}else a.slideNext(),a.emit("scroll",t);else if(a.isBeginning&&!a.params.loop||a.animating){if(i.releaseOnEdges)return!0}else a.slidePrev(),a.emit("scroll",t);a.mousewheel.lastScrollTime=(new J.Date).getTime()}return t.preventDefault?t.preventDefault():t.returnValue=!1,!1},enable:function(){var e=this;if(!H.event)return!1;if(e.mousewheel.enabled)return!1;var t=e.$el;return"container"!==e.params.mousewheel.eventsTarged&&(t=L(e.params.mousewheel.eventsTarged)),t.on("mouseenter",e.mousewheel.handleMouseEnter),t.on("mouseleave",e.mousewheel.handleMouseLeave),t.on(H.event,e.mousewheel.handle),e.mousewheel.enabled=!0},disable:function(){var e=this;if(!H.event)return!1;if(!e.mousewheel.enabled)return!1;var t=e.$el;return"container"!==e.params.mousewheel.eventsTarged&&(t=L(e.params.mousewheel.eventsTarged)),t.off(H.event,e.mousewheel.handle),!(e.mousewheel.enabled=!1)}},G={update:function(){var e=this,t=e.params.navigation;if(!e.params.loop){var a=e.navigation,i=a.$nextEl,s=a.$prevEl;s&&0<s.length&&(e.isBeginning?s.addClass(t.disabledClass):s.removeClass(t.disabledClass),s[e.params.watchOverflow&&e.isLocked?"addClass":"removeClass"](t.lockClass)),i&&0<i.length&&(e.isEnd?i.addClass(t.disabledClass):i.removeClass(t.disabledClass),i[e.params.watchOverflow&&e.isLocked?"addClass":"removeClass"](t.lockClass))}},onPrevClick:function(e){e.preventDefault(),this.isBeginning&&!this.params.loop||this.slidePrev()},onNextClick:function(e){e.preventDefault(),this.isEnd&&!this.params.loop||this.slideNext()},init:function(){var e,t,a=this,i=a.params.navigation;(i.nextEl||i.prevEl)&&(i.nextEl&&(e=L(i.nextEl),a.params.uniqueNavElements&&"string"==typeof i.nextEl&&1<e.length&&1===a.$el.find(i.nextEl).length&&(e=a.$el.find(i.nextEl))),i.prevEl&&(t=L(i.prevEl),a.params.uniqueNavElements&&"string"==typeof i.prevEl&&1<t.length&&1===a.$el.find(i.prevEl).length&&(t=a.$el.find(i.prevEl))),e&&0<e.length&&e.on("click",a.navigation.onNextClick),t&&0<t.length&&t.on("click",a.navigation.onPrevClick),ee.extend(a.navigation,{$nextEl:e,nextEl:e&&e[0],$prevEl:t,prevEl:t&&t[0]}))},destroy:function(){var e=this,t=e.navigation,a=t.$nextEl,i=t.$prevEl;a&&a.length&&(a.off("click",e.navigation.onNextClick),a.removeClass(e.params.navigation.disabledClass)),i&&i.length&&(i.off("click",e.navigation.onPrevClick),i.removeClass(e.params.navigation.disabledClass))}},B={update:function(){var e=this,t=e.rtl,s=e.params.pagination;if(s.el&&e.pagination.el&&e.pagination.$el&&0!==e.pagination.$el.length){var r,a=e.virtual&&e.params.virtual.enabled?e.virtual.slides.length:e.slides.length,i=e.pagination.$el,n=e.params.loop?Math.ceil((a-2*e.loopedSlides)/e.params.slidesPerGroup):e.snapGrid.length;if(e.params.loop?((r=Math.ceil((e.activeIndex-e.loopedSlides)/e.params.slidesPerGroup))>a-1-2*e.loopedSlides&&(r-=a-2*e.loopedSlides),n-1<r&&(r-=n),r<0&&"bullets"!==e.params.paginationType&&(r=n+r)):r=void 0!==e.snapIndex?e.snapIndex:e.activeIndex||0,"bullets"===s.type&&e.pagination.bullets&&0<e.pagination.bullets.length){var o,l,d,p=e.pagination.bullets;if(s.dynamicBullets&&(e.pagination.bulletSize=p.eq(0)[e.isHorizontal()?"outerWidth":"outerHeight"](!0),i.css(e.isHorizontal()?"width":"height",e.pagination.bulletSize*(s.dynamicMainBullets+4)+"px"),1<s.dynamicMainBullets&&void 0!==e.previousIndex&&(e.pagination.dynamicBulletIndex+=r-e.previousIndex,e.pagination.dynamicBulletIndex>s.dynamicMainBullets-1?e.pagination.dynamicBulletIndex=s.dynamicMainBullets-1:e.pagination.dynamicBulletIndex<0&&(e.pagination.dynamicBulletIndex=0)),o=r-e.pagination.dynamicBulletIndex,d=((l=o+(Math.min(p.length,s.dynamicMainBullets)-1))+o)/2),p.removeClass(s.bulletActiveClass+" "+s.bulletActiveClass+"-next "+s.bulletActiveClass+"-next-next "+s.bulletActiveClass+"-prev "+s.bulletActiveClass+"-prev-prev "+s.bulletActiveClass+"-main"),1<i.length)p.each(function(e,t){var a=L(t),i=a.index();i===r&&a.addClass(s.bulletActiveClass),s.dynamicBullets&&(o<=i&&i<=l&&a.addClass(s.bulletActiveClass+"-main"),i===o&&a.prev().addClass(s.bulletActiveClass+"-prev").prev().addClass(s.bulletActiveClass+"-prev-prev"),i===l&&a.next().addClass(s.bulletActiveClass+"-next").next().addClass(s.bulletActiveClass+"-next-next"))});else if(p.eq(r).addClass(s.bulletActiveClass),s.dynamicBullets){for(var c=p.eq(o),u=p.eq(l),h=o;h<=l;h+=1)p.eq(h).addClass(s.bulletActiveClass+"-main");c.prev().addClass(s.bulletActiveClass+"-prev").prev().addClass(s.bulletActiveClass+"-prev-prev"),u.next().addClass(s.bulletActiveClass+"-next").next().addClass(s.bulletActiveClass+"-next-next")}if(s.dynamicBullets){var v=Math.min(p.length,s.dynamicMainBullets+4),f=(e.pagination.bulletSize*v-e.pagination.bulletSize)/2-d*e.pagination.bulletSize,m=t?"right":"left";p.css(e.isHorizontal()?m:"top",f+"px")}}if("fraction"===s.type&&(i.find("."+s.currentClass).text(s.formatFractionCurrent(r+1)),i.find("."+s.totalClass).text(s.formatFractionTotal(n))),"progressbar"===s.type){var g;g=s.progressbarOpposite?e.isHorizontal()?"vertical":"horizontal":e.isHorizontal()?"horizontal":"vertical";var b=(r+1)/n,w=1,y=1;"horizontal"===g?w=b:y=b,i.find("."+s.progressbarFillClass).transform("translate3d(0,0,0) scaleX("+w+") scaleY("+y+")").transition(e.params.speed)}"custom"===s.type&&s.renderCustom?(i.html(s.renderCustom(e,r+1,n)),e.emit("paginationRender",e,i[0])):e.emit("paginationUpdate",e,i[0]),i[e.params.watchOverflow&&e.isLocked?"addClass":"removeClass"](s.lockClass)}},render:function(){var e=this,t=e.params.pagination;if(t.el&&e.pagination.el&&e.pagination.$el&&0!==e.pagination.$el.length){var a=e.virtual&&e.params.virtual.enabled?e.virtual.slides.length:e.slides.length,i=e.pagination.$el,s="";if("bullets"===t.type){for(var r=e.params.loop?Math.ceil((a-2*e.loopedSlides)/e.params.slidesPerGroup):e.snapGrid.length,n=0;n<r;n+=1)t.renderBullet?s+=t.renderBullet.call(e,n,t.bulletClass):s+="<"+t.bulletElement+' class="'+t.bulletClass+'"></'+t.bulletElement+">";i.html(s),e.pagination.bullets=i.find("."+t.bulletClass)}"fraction"===t.type&&(s=t.renderFraction?t.renderFraction.call(e,t.currentClass,t.totalClass):'<span class="'+t.currentClass+'"></span> / <span class="'+t.totalClass+'"></span>',i.html(s)),"progressbar"===t.type&&(s=t.renderProgressbar?t.renderProgressbar.call(e,t.progressbarFillClass):'<span class="'+t.progressbarFillClass+'"></span>',i.html(s)),"custom"!==t.type&&e.emit("paginationRender",e.pagination.$el[0])}},init:function(){var a=this,e=a.params.pagination;if(e.el){var t=L(e.el);0!==t.length&&(a.params.uniqueNavElements&&"string"==typeof e.el&&1<t.length&&1===a.$el.find(e.el).length&&(t=a.$el.find(e.el)),"bullets"===e.type&&e.clickable&&t.addClass(e.clickableClass),t.addClass(e.modifierClass+e.type),"bullets"===e.type&&e.dynamicBullets&&(t.addClass(""+e.modifierClass+e.type+"-dynamic"),a.pagination.dynamicBulletIndex=0,e.dynamicMainBullets<1&&(e.dynamicMainBullets=1)),"progressbar"===e.type&&e.progressbarOpposite&&t.addClass(e.progressbarOppositeClass),e.clickable&&t.on("click","."+e.bulletClass,function(e){e.preventDefault();var t=L(this).index()*a.params.slidesPerGroup;a.params.loop&&(t+=a.loopedSlides),a.slideTo(t)}),ee.extend(a.pagination,{$el:t,el:t[0]}))}},destroy:function(){var e=this,t=e.params.pagination;if(t.el&&e.pagination.el&&e.pagination.$el&&0!==e.pagination.$el.length){var a=e.pagination.$el;a.removeClass(t.hiddenClass),a.removeClass(t.modifierClass+t.type),e.pagination.bullets&&e.pagination.bullets.removeClass(t.bulletActiveClass),t.clickable&&a.off("click","."+t.bulletClass)}}},X={setTranslate:function(){var e=this;if(e.params.scrollbar.el&&e.scrollbar.el){var t=e.scrollbar,a=e.rtlTranslate,i=e.progress,s=t.dragSize,r=t.trackSize,n=t.$dragEl,o=t.$el,l=e.params.scrollbar,d=s,p=(r-s)*i;a?0<(p=-p)?(d=s-p,p=0):r<-p+s&&(d=r+p):p<0?(d=s+p,p=0):r<p+s&&(d=r-p),e.isHorizontal()?(te.transforms3d?n.transform("translate3d("+p+"px, 0, 0)"):n.transform("translateX("+p+"px)"),n[0].style.width=d+"px"):(te.transforms3d?n.transform("translate3d(0px, "+p+"px, 0)"):n.transform("translateY("+p+"px)"),n[0].style.height=d+"px"),l.hide&&(clearTimeout(e.scrollbar.timeout),o[0].style.opacity=1,e.scrollbar.timeout=setTimeout(function(){o[0].style.opacity=0,o.transition(400)},1e3))}},setTransition:function(e){this.params.scrollbar.el&&this.scrollbar.el&&this.scrollbar.$dragEl.transition(e)},updateSize:function(){var e=this;if(e.params.scrollbar.el&&e.scrollbar.el){var t=e.scrollbar,a=t.$dragEl,i=t.$el;a[0].style.width="",a[0].style.height="";var s,r=e.isHorizontal()?i[0].offsetWidth:i[0].offsetHeight,n=e.size/e.virtualSize,o=n*(r/e.size);s="auto"===e.params.scrollbar.dragSize?r*n:parseInt(e.params.scrollbar.dragSize,10),e.isHorizontal()?a[0].style.width=s+"px":a[0].style.height=s+"px",i[0].style.display=1<=n?"none":"",e.params.scrollbarHide&&(i[0].style.opacity=0),ee.extend(t,{trackSize:r,divider:n,moveDivider:o,dragSize:s}),t.$el[e.params.watchOverflow&&e.isLocked?"addClass":"removeClass"](e.params.scrollbar.lockClass)}},setDragPosition:function(e){var t,a=this,i=a.scrollbar,s=a.rtlTranslate,r=i.$el,n=i.dragSize,o=i.trackSize;t=((a.isHorizontal()?"touchstart"===e.type||"touchmove"===e.type?e.targetTouches[0].pageX:e.pageX||e.clientX:"touchstart"===e.type||"touchmove"===e.type?e.targetTouches[0].pageY:e.pageY||e.clientY)-r.offset()[a.isHorizontal()?"left":"top"]-n/2)/(o-n),t=Math.max(Math.min(t,1),0),s&&(t=1-t);var l=a.minTranslate()+(a.maxTranslate()-a.minTranslate())*t;a.updateProgress(l),a.setTranslate(l),a.updateActiveIndex(),a.updateSlidesClasses()},onDragStart:function(e){var t=this,a=t.params.scrollbar,i=t.scrollbar,s=t.$wrapperEl,r=i.$el,n=i.$dragEl;t.scrollbar.isTouched=!0,e.preventDefault(),e.stopPropagation(),s.transition(100),n.transition(100),i.setDragPosition(e),clearTimeout(t.scrollbar.dragTimeout),r.transition(0),a.hide&&r.css("opacity",1),t.emit("scrollbarDragStart",e)},onDragMove:function(e){var t=this.scrollbar,a=this.$wrapperEl,i=t.$el,s=t.$dragEl;this.scrollbar.isTouched&&(e.preventDefault?e.preventDefault():e.returnValue=!1,t.setDragPosition(e),a.transition(0),i.transition(0),s.transition(0),this.emit("scrollbarDragMove",e))},onDragEnd:function(e){var t=this,a=t.params.scrollbar,i=t.scrollbar.$el;t.scrollbar.isTouched&&(t.scrollbar.isTouched=!1,a.hide&&(clearTimeout(t.scrollbar.dragTimeout),t.scrollbar.dragTimeout=ee.nextTick(function(){i.css("opacity",0),i.transition(400)},1e3)),t.emit("scrollbarDragEnd",e),a.snapOnRelease&&t.slideToClosest())},enableDraggable:function(){var e=this;if(e.params.scrollbar.el){var t=e.scrollbar,a=e.touchEventsTouch,i=e.touchEventsDesktop,s=e.params,r=t.$el[0],n=!(!te.passiveListener||!s.passiveListeners)&&{passive:!1,capture:!1},o=!(!te.passiveListener||!s.passiveListeners)&&{passive:!0,capture:!1};te.touch?(r.addEventListener(a.start,e.scrollbar.onDragStart,n),r.addEventListener(a.move,e.scrollbar.onDragMove,n),r.addEventListener(a.end,e.scrollbar.onDragEnd,o)):(r.addEventListener(i.start,e.scrollbar.onDragStart,n),f.addEventListener(i.move,e.scrollbar.onDragMove,n),f.addEventListener(i.end,e.scrollbar.onDragEnd,o))}},disableDraggable:function(){var e=this;if(e.params.scrollbar.el){var t=e.scrollbar,a=e.touchEventsTouch,i=e.touchEventsDesktop,s=e.params,r=t.$el[0],n=!(!te.passiveListener||!s.passiveListeners)&&{passive:!1,capture:!1},o=!(!te.passiveListener||!s.passiveListeners)&&{passive:!0,capture:!1};te.touch?(r.removeEventListener(a.start,e.scrollbar.onDragStart,n),r.removeEventListener(a.move,e.scrollbar.onDragMove,n),r.removeEventListener(a.end,e.scrollbar.onDragEnd,o)):(r.removeEventListener(i.start,e.scrollbar.onDragStart,n),f.removeEventListener(i.move,e.scrollbar.onDragMove,n),f.removeEventListener(i.end,e.scrollbar.onDragEnd,o))}},init:function(){var e=this;if(e.params.scrollbar.el){var t=e.scrollbar,a=e.$el,i=e.params.scrollbar,s=L(i.el);e.params.uniqueNavElements&&"string"==typeof i.el&&1<s.length&&1===a.find(i.el).length&&(s=a.find(i.el));var r=s.find("."+e.params.scrollbar.dragClass);0===r.length&&(r=L('<div class="'+e.params.scrollbar.dragClass+'"></div>'),s.append(r)),ee.extend(t,{$el:s,el:s[0],$dragEl:r,dragEl:r[0]}),i.draggable&&t.enableDraggable()}},destroy:function(){this.scrollbar.disableDraggable()}},Y={setTransform:function(e,t){var a=this.rtl,i=L(e),s=a?-1:1,r=i.attr("data-swiper-parallax")||"0",n=i.attr("data-swiper-parallax-x"),o=i.attr("data-swiper-parallax-y"),l=i.attr("data-swiper-parallax-scale"),d=i.attr("data-swiper-parallax-opacity");if(n||o?(n=n||"0",o=o||"0"):this.isHorizontal()?(n=r,o="0"):(o=r,n="0"),n=0<=n.indexOf("%")?parseInt(n,10)*t*s+"%":n*t*s+"px",o=0<=o.indexOf("%")?parseInt(o,10)*t+"%":o*t+"px",null!=d){var p=d-(d-1)*(1-Math.abs(t));i[0].style.opacity=p}if(null==l)i.transform("translate3d("+n+", "+o+", 0px)");else{var c=l-(l-1)*(1-Math.abs(t));i.transform("translate3d("+n+", "+o+", 0px) scale("+c+")")}},setTranslate:function(){var i=this,e=i.$el,t=i.slides,s=i.progress,r=i.snapGrid;e.children("[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y]").each(function(e,t){i.parallax.setTransform(t,s)}),t.each(function(e,t){var a=t.progress;1<i.params.slidesPerGroup&&"auto"!==i.params.slidesPerView&&(a+=Math.ceil(e/2)-s*(r.length-1)),a=Math.min(Math.max(a,-1),1),L(t).find("[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y]").each(function(e,t){i.parallax.setTransform(t,a)})})},setTransition:function(s){void 0===s&&(s=this.params.speed);this.$el.find("[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y]").each(function(e,t){var a=L(t),i=parseInt(a.attr("data-swiper-parallax-duration"),10)||s;0===s&&(i=0),a.transition(i)})}},V={getDistanceBetweenTouches:function(e){if(e.targetTouches.length<2)return 1;var t=e.targetTouches[0].pageX,a=e.targetTouches[0].pageY,i=e.targetTouches[1].pageX,s=e.targetTouches[1].pageY;return Math.sqrt(Math.pow(i-t,2)+Math.pow(s-a,2))},onGestureStart:function(e){var t=this,a=t.params.zoom,i=t.zoom,s=i.gesture;if(i.fakeGestureTouched=!1,i.fakeGestureMoved=!1,!te.gestures){if("touchstart"!==e.type||"touchstart"===e.type&&e.targetTouches.length<2)return;i.fakeGestureTouched=!0,s.scaleStart=V.getDistanceBetweenTouches(e)}s.$slideEl&&s.$slideEl.length||(s.$slideEl=L(e.target).closest(".swiper-slide"),0===s.$slideEl.length&&(s.$slideEl=t.slides.eq(t.activeIndex)),s.$imageEl=s.$slideEl.find("img, svg, canvas"),s.$imageWrapEl=s.$imageEl.parent("."+a.containerClass),s.maxRatio=s.$imageWrapEl.attr("data-swiper-zoom")||a.maxRatio,0!==s.$imageWrapEl.length)?(s.$imageEl.transition(0),t.zoom.isScaling=!0):s.$imageEl=void 0},onGestureChange:function(e){var t=this.params.zoom,a=this.zoom,i=a.gesture;if(!te.gestures){if("touchmove"!==e.type||"touchmove"===e.type&&e.targetTouches.length<2)return;a.fakeGestureMoved=!0,i.scaleMove=V.getDistanceBetweenTouches(e)}i.$imageEl&&0!==i.$imageEl.length&&(a.scale=te.gestures?e.scale*a.currentScale:i.scaleMove/i.scaleStart*a.currentScale,a.scale>i.maxRatio&&(a.scale=i.maxRatio-1+Math.pow(a.scale-i.maxRatio+1,.5)),a.scale<t.minRatio&&(a.scale=t.minRatio+1-Math.pow(t.minRatio-a.scale+1,.5)),i.$imageEl.transform("translate3d(0,0,0) scale("+a.scale+")"))},onGestureEnd:function(e){var t=this.params.zoom,a=this.zoom,i=a.gesture;if(!te.gestures){if(!a.fakeGestureTouched||!a.fakeGestureMoved)return;if("touchend"!==e.type||"touchend"===e.type&&e.changedTouches.length<2&&!m.android)return;a.fakeGestureTouched=!1,a.fakeGestureMoved=!1}i.$imageEl&&0!==i.$imageEl.length&&(a.scale=Math.max(Math.min(a.scale,i.maxRatio),t.minRatio),i.$imageEl.transition(this.params.speed).transform("translate3d(0,0,0) scale("+a.scale+")"),a.currentScale=a.scale,a.isScaling=!1,1===a.scale&&(i.$slideEl=void 0))},onTouchStart:function(e){var t=this.zoom,a=t.gesture,i=t.image;a.$imageEl&&0!==a.$imageEl.length&&(i.isTouched||(m.android&&e.preventDefault(),i.isTouched=!0,i.touchesStart.x="touchstart"===e.type?e.targetTouches[0].pageX:e.pageX,i.touchesStart.y="touchstart"===e.type?e.targetTouches[0].pageY:e.pageY))},onTouchMove:function(e){var t=this,a=t.zoom,i=a.gesture,s=a.image,r=a.velocity;if(i.$imageEl&&0!==i.$imageEl.length&&(t.allowClick=!1,s.isTouched&&i.$slideEl)){s.isMoved||(s.width=i.$imageEl[0].offsetWidth,s.height=i.$imageEl[0].offsetHeight,s.startX=ee.getTranslate(i.$imageWrapEl[0],"x")||0,s.startY=ee.getTranslate(i.$imageWrapEl[0],"y")||0,i.slideWidth=i.$slideEl[0].offsetWidth,i.slideHeight=i.$slideEl[0].offsetHeight,i.$imageWrapEl.transition(0),t.rtl&&(s.startX=-s.startX,s.startY=-s.startY));var n=s.width*a.scale,o=s.height*a.scale;if(!(n<i.slideWidth&&o<i.slideHeight)){if(s.minX=Math.min(i.slideWidth/2-n/2,0),s.maxX=-s.minX,s.minY=Math.min(i.slideHeight/2-o/2,0),s.maxY=-s.minY,s.touchesCurrent.x="touchmove"===e.type?e.targetTouches[0].pageX:e.pageX,s.touchesCurrent.y="touchmove"===e.type?e.targetTouches[0].pageY:e.pageY,!s.isMoved&&!a.isScaling){if(t.isHorizontal()&&(Math.floor(s.minX)===Math.floor(s.startX)&&s.touchesCurrent.x<s.touchesStart.x||Math.floor(s.maxX)===Math.floor(s.startX)&&s.touchesCurrent.x>s.touchesStart.x))return void(s.isTouched=!1);if(!t.isHorizontal()&&(Math.floor(s.minY)===Math.floor(s.startY)&&s.touchesCurrent.y<s.touchesStart.y||Math.floor(s.maxY)===Math.floor(s.startY)&&s.touchesCurrent.y>s.touchesStart.y))return void(s.isTouched=!1)}e.preventDefault(),e.stopPropagation(),s.isMoved=!0,s.currentX=s.touchesCurrent.x-s.touchesStart.x+s.startX,s.currentY=s.touchesCurrent.y-s.touchesStart.y+s.startY,s.currentX<s.minX&&(s.currentX=s.minX+1-Math.pow(s.minX-s.currentX+1,.8)),s.currentX>s.maxX&&(s.currentX=s.maxX-1+Math.pow(s.currentX-s.maxX+1,.8)),s.currentY<s.minY&&(s.currentY=s.minY+1-Math.pow(s.minY-s.currentY+1,.8)),s.currentY>s.maxY&&(s.currentY=s.maxY-1+Math.pow(s.currentY-s.maxY+1,.8)),r.prevPositionX||(r.prevPositionX=s.touchesCurrent.x),r.prevPositionY||(r.prevPositionY=s.touchesCurrent.y),r.prevTime||(r.prevTime=Date.now()),r.x=(s.touchesCurrent.x-r.prevPositionX)/(Date.now()-r.prevTime)/2,r.y=(s.touchesCurrent.y-r.prevPositionY)/(Date.now()-r.prevTime)/2,Math.abs(s.touchesCurrent.x-r.prevPositionX)<2&&(r.x=0),Math.abs(s.touchesCurrent.y-r.prevPositionY)<2&&(r.y=0),r.prevPositionX=s.touchesCurrent.x,r.prevPositionY=s.touchesCurrent.y,r.prevTime=Date.now(),i.$imageWrapEl.transform("translate3d("+s.currentX+"px, "+s.currentY+"px,0)")}}},onTouchEnd:function(){var e=this.zoom,t=e.gesture,a=e.image,i=e.velocity;if(t.$imageEl&&0!==t.$imageEl.length){if(!a.isTouched||!a.isMoved)return a.isTouched=!1,void(a.isMoved=!1);a.isTouched=!1,a.isMoved=!1;var s=300,r=300,n=i.x*s,o=a.currentX+n,l=i.y*r,d=a.currentY+l;0!==i.x&&(s=Math.abs((o-a.currentX)/i.x)),0!==i.y&&(r=Math.abs((d-a.currentY)/i.y));var p=Math.max(s,r);a.currentX=o,a.currentY=d;var c=a.width*e.scale,u=a.height*e.scale;a.minX=Math.min(t.slideWidth/2-c/2,0),a.maxX=-a.minX,a.minY=Math.min(t.slideHeight/2-u/2,0),a.maxY=-a.minY,a.currentX=Math.max(Math.min(a.currentX,a.maxX),a.minX),a.currentY=Math.max(Math.min(a.currentY,a.maxY),a.minY),t.$imageWrapEl.transition(p).transform("translate3d("+a.currentX+"px, "+a.currentY+"px,0)")}},onTransitionEnd:function(){var e=this.zoom,t=e.gesture;t.$slideEl&&this.previousIndex!==this.activeIndex&&(t.$imageEl.transform("translate3d(0,0,0) scale(1)"),t.$imageWrapEl.transform("translate3d(0,0,0)"),e.scale=1,e.currentScale=1,t.$slideEl=void 0,t.$imageEl=void 0,t.$imageWrapEl=void 0)},toggle:function(e){var t=this.zoom;t.scale&&1!==t.scale?t.out():t.in(e)},in:function(e){var t,a,i,s,r,n,o,l,d,p,c,u,h,v,f,m,g=this,b=g.zoom,w=g.params.zoom,y=b.gesture,x=b.image;(y.$slideEl||(y.$slideEl=g.clickedSlide?L(g.clickedSlide):g.slides.eq(g.activeIndex),y.$imageEl=y.$slideEl.find("img, svg, canvas"),y.$imageWrapEl=y.$imageEl.parent("."+w.containerClass)),y.$imageEl&&0!==y.$imageEl.length)&&(y.$slideEl.addClass(""+w.zoomedSlideClass),void 0===x.touchesStart.x&&e?(t="touchend"===e.type?e.changedTouches[0].pageX:e.pageX,a="touchend"===e.type?e.changedTouches[0].pageY:e.pageY):(t=x.touchesStart.x,a=x.touchesStart.y),b.scale=y.$imageWrapEl.attr("data-swiper-zoom")||w.maxRatio,b.currentScale=y.$imageWrapEl.attr("data-swiper-zoom")||w.maxRatio,e?(f=y.$slideEl[0].offsetWidth,m=y.$slideEl[0].offsetHeight,i=y.$slideEl.offset().left+f/2-t,s=y.$slideEl.offset().top+m/2-a,o=y.$imageEl[0].offsetWidth,l=y.$imageEl[0].offsetHeight,d=o*b.scale,p=l*b.scale,h=-(c=Math.min(f/2-d/2,0)),v=-(u=Math.min(m/2-p/2,0)),(r=i*b.scale)<c&&(r=c),h<r&&(r=h),(n=s*b.scale)<u&&(n=u),v<n&&(n=v)):n=r=0,y.$imageWrapEl.transition(300).transform("translate3d("+r+"px, "+n+"px,0)"),y.$imageEl.transition(300).transform("translate3d(0,0,0) scale("+b.scale+")"))},out:function(){var e=this,t=e.zoom,a=e.params.zoom,i=t.gesture;i.$slideEl||(i.$slideEl=e.clickedSlide?L(e.clickedSlide):e.slides.eq(e.activeIndex),i.$imageEl=i.$slideEl.find("img, svg, canvas"),i.$imageWrapEl=i.$imageEl.parent("."+a.containerClass)),i.$imageEl&&0!==i.$imageEl.length&&(t.scale=1,t.currentScale=1,i.$imageWrapEl.transition(300).transform("translate3d(0,0,0)"),i.$imageEl.transition(300).transform("translate3d(0,0,0) scale(1)"),i.$slideEl.removeClass(""+a.zoomedSlideClass),i.$slideEl=void 0)},enable:function(){var e=this,t=e.zoom;if(!t.enabled){t.enabled=!0;var a=!("touchstart"!==e.touchEvents.start||!te.passiveListener||!e.params.passiveListeners)&&{passive:!0,capture:!1};te.gestures?(e.$wrapperEl.on("gesturestart",".swiper-slide",t.onGestureStart,a),e.$wrapperEl.on("gesturechange",".swiper-slide",t.onGestureChange,a),e.$wrapperEl.on("gestureend",".swiper-slide",t.onGestureEnd,a)):"touchstart"===e.touchEvents.start&&(e.$wrapperEl.on(e.touchEvents.start,".swiper-slide",t.onGestureStart,a),e.$wrapperEl.on(e.touchEvents.move,".swiper-slide",t.onGestureChange,a),e.$wrapperEl.on(e.touchEvents.end,".swiper-slide",t.onGestureEnd,a)),e.$wrapperEl.on(e.touchEvents.move,"."+e.params.zoom.containerClass,t.onTouchMove)}},disable:function(){var e=this,t=e.zoom;if(t.enabled){e.zoom.enabled=!1;var a=!("touchstart"!==e.touchEvents.start||!te.passiveListener||!e.params.passiveListeners)&&{passive:!0,capture:!1};te.gestures?(e.$wrapperEl.off("gesturestart",".swiper-slide",t.onGestureStart,a),e.$wrapperEl.off("gesturechange",".swiper-slide",t.onGestureChange,a),e.$wrapperEl.off("gestureend",".swiper-slide",t.onGestureEnd,a)):"touchstart"===e.touchEvents.start&&(e.$wrapperEl.off(e.touchEvents.start,".swiper-slide",t.onGestureStart,a),e.$wrapperEl.off(e.touchEvents.move,".swiper-slide",t.onGestureChange,a),e.$wrapperEl.off(e.touchEvents.end,".swiper-slide",t.onGestureEnd,a)),e.$wrapperEl.off(e.touchEvents.move,"."+e.params.zoom.containerClass,t.onTouchMove)}}},F={loadInSlide:function(e,l){void 0===l&&(l=!0);var d=this,p=d.params.lazy;if(void 0!==e&&0!==d.slides.length){var c=d.virtual&&d.params.virtual.enabled?d.$wrapperEl.children("."+d.params.slideClass+'[data-swiper-slide-index="'+e+'"]'):d.slides.eq(e),t=c.find("."+p.elementClass+":not(."+p.loadedClass+"):not(."+p.loadingClass+")");!c.hasClass(p.elementClass)||c.hasClass(p.loadedClass)||c.hasClass(p.loadingClass)||(t=t.add(c[0])),0!==t.length&&t.each(function(e,t){var i=L(t);i.addClass(p.loadingClass);var s=i.attr("data-background"),r=i.attr("data-src"),n=i.attr("data-srcset"),o=i.attr("data-sizes");d.loadImage(i[0],r||s,n,o,!1,function(){if(null!=d&&d&&(!d||d.params)&&!d.destroyed){if(s?(i.css("background-image",'url("'+s+'")'),i.removeAttr("data-background")):(n&&(i.attr("srcset",n),i.removeAttr("data-srcset")),o&&(i.attr("sizes",o),i.removeAttr("data-sizes")),r&&(i.attr("src",r),i.removeAttr("data-src"))),i.addClass(p.loadedClass).removeClass(p.loadingClass),c.find("."+p.preloaderClass).remove(),d.params.loop&&l){var e=c.attr("data-swiper-slide-index");if(c.hasClass(d.params.slideDuplicateClass)){var t=d.$wrapperEl.children('[data-swiper-slide-index="'+e+'"]:not(.'+d.params.slideDuplicateClass+")");d.lazy.loadInSlide(t.index(),!1)}else{var a=d.$wrapperEl.children("."+d.params.slideDuplicateClass+'[data-swiper-slide-index="'+e+'"]');d.lazy.loadInSlide(a.index(),!1)}}d.emit("lazyImageReady",c[0],i[0])}}),d.emit("lazyImageLoad",c[0],i[0])})}},load:function(){var i=this,t=i.$wrapperEl,a=i.params,s=i.slides,e=i.activeIndex,r=i.virtual&&a.virtual.enabled,n=a.lazy,o=a.slidesPerView;function l(e){if(r){if(t.children("."+a.slideClass+'[data-swiper-slide-index="'+e+'"]').length)return!0}else if(s[e])return!0;return!1}function d(e){return r?L(e).attr("data-swiper-slide-index"):L(e).index()}if("auto"===o&&(o=0),i.lazy.initialImageLoaded||(i.lazy.initialImageLoaded=!0),i.params.watchSlidesVisibility)t.children("."+a.slideVisibleClass).each(function(e,t){var a=r?L(t).attr("data-swiper-slide-index"):L(t).index();i.lazy.loadInSlide(a)});else if(1<o)for(var p=e;p<e+o;p+=1)l(p)&&i.lazy.loadInSlide(p);else i.lazy.loadInSlide(e);if(n.loadPrevNext)if(1<o||n.loadPrevNextAmount&&1<n.loadPrevNextAmount){for(var c=n.loadPrevNextAmount,u=o,h=Math.min(e+u+Math.max(c,u),s.length),v=Math.max(e-Math.max(u,c),0),f=e+o;f<h;f+=1)l(f)&&i.lazy.loadInSlide(f);for(var m=v;m<e;m+=1)l(m)&&i.lazy.loadInSlide(m)}else{var g=t.children("."+a.slideNextClass);0<g.length&&i.lazy.loadInSlide(d(g));var b=t.children("."+a.slidePrevClass);0<b.length&&i.lazy.loadInSlide(d(b))}}},R={LinearSpline:function(e,t){var a,i,s,r,n,o=function(e,t){for(i=-1,a=e.length;1<a-i;)e[s=a+i>>1]<=t?i=s:a=s;return a};return this.x=e,this.y=t,this.lastIndex=e.length-1,this.interpolate=function(e){return e?(n=o(this.x,e),r=n-1,(e-this.x[r])*(this.y[n]-this.y[r])/(this.x[n]-this.x[r])+this.y[r]):0},this},getInterpolateFunction:function(e){var t=this;t.controller.spline||(t.controller.spline=t.params.loop?new R.LinearSpline(t.slidesGrid,e.slidesGrid):new R.LinearSpline(t.snapGrid,e.snapGrid))},setTranslate:function(e,t){var a,i,s=this,r=s.controller.control;function n(e){var t=s.rtlTranslate?-s.translate:s.translate;"slide"===s.params.controller.by&&(s.controller.getInterpolateFunction(e),i=-s.controller.spline.interpolate(-t)),i&&"container"!==s.params.controller.by||(a=(e.maxTranslate()-e.minTranslate())/(s.maxTranslate()-s.minTranslate()),i=(t-s.minTranslate())*a+e.minTranslate()),s.params.controller.inverse&&(i=e.maxTranslate()-i),e.updateProgress(i),e.setTranslate(i,s),e.updateActiveIndex(),e.updateSlidesClasses()}if(Array.isArray(r))for(var o=0;o<r.length;o+=1)r[o]!==t&&r[o]instanceof S&&n(r[o]);else r instanceof S&&t!==r&&n(r)},setTransition:function(t,e){var a,i=this,s=i.controller.control;function r(e){e.setTransition(t,i),0!==t&&(e.transitionStart(),e.params.autoHeight&&ee.nextTick(function(){e.updateAutoHeight()}),e.$wrapperEl.transitionEnd(function(){s&&(e.params.loop&&"slide"===i.params.controller.by&&e.loopFix(),e.transitionEnd())}))}if(Array.isArray(s))for(a=0;a<s.length;a+=1)s[a]!==e&&s[a]instanceof S&&r(s[a]);else s instanceof S&&e!==s&&r(s)}},q={makeElFocusable:function(e){return e.attr("tabIndex","0"),e},addElRole:function(e,t){return e.attr("role",t),e},addElLabel:function(e,t){return e.attr("aria-label",t),e},disableEl:function(e){return e.attr("aria-disabled",!0),e},enableEl:function(e){return e.attr("aria-disabled",!1),e},onEnterKey:function(e){var t=this,a=t.params.a11y;if(13===e.keyCode){var i=L(e.target);t.navigation&&t.navigation.$nextEl&&i.is(t.navigation.$nextEl)&&(t.isEnd&&!t.params.loop||t.slideNext(),t.isEnd?t.a11y.notify(a.lastSlideMessage):t.a11y.notify(a.nextSlideMessage)),t.navigation&&t.navigation.$prevEl&&i.is(t.navigation.$prevEl)&&(t.isBeginning&&!t.params.loop||t.slidePrev(),t.isBeginning?t.a11y.notify(a.firstSlideMessage):t.a11y.notify(a.prevSlideMessage)),t.pagination&&i.is("."+t.params.pagination.bulletClass)&&i[0].click()}},notify:function(e){var t=this.a11y.liveRegion;0!==t.length&&(t.html(""),t.html(e))},updateNavigation:function(){var e=this;if(!e.params.loop){var t=e.navigation,a=t.$nextEl,i=t.$prevEl;i&&0<i.length&&(e.isBeginning?e.a11y.disableEl(i):e.a11y.enableEl(i)),a&&0<a.length&&(e.isEnd?e.a11y.disableEl(a):e.a11y.enableEl(a))}},updatePagination:function(){var i=this,s=i.params.a11y;i.pagination&&i.params.pagination.clickable&&i.pagination.bullets&&i.pagination.bullets.length&&i.pagination.bullets.each(function(e,t){var a=L(t);i.a11y.makeElFocusable(a),i.a11y.addElRole(a,"button"),i.a11y.addElLabel(a,s.paginationBulletMessage.replace(/{{index}}/,a.index()+1))})},init:function(){var e=this;e.$el.append(e.a11y.liveRegion);var t,a,i=e.params.a11y;e.navigation&&e.navigation.$nextEl&&(t=e.navigation.$nextEl),e.navigation&&e.navigation.$prevEl&&(a=e.navigation.$prevEl),t&&(e.a11y.makeElFocusable(t),e.a11y.addElRole(t,"button"),e.a11y.addElLabel(t,i.nextSlideMessage),t.on("keydown",e.a11y.onEnterKey)),a&&(e.a11y.makeElFocusable(a),e.a11y.addElRole(a,"button"),e.a11y.addElLabel(a,i.prevSlideMessage),a.on("keydown",e.a11y.onEnterKey)),e.pagination&&e.params.pagination.clickable&&e.pagination.bullets&&e.pagination.bullets.length&&e.pagination.$el.on("keydown","."+e.params.pagination.bulletClass,e.a11y.onEnterKey)},destroy:function(){var e,t,a=this;a.a11y.liveRegion&&0<a.a11y.liveRegion.length&&a.a11y.liveRegion.remove(),a.navigation&&a.navigation.$nextEl&&(e=a.navigation.$nextEl),a.navigation&&a.navigation.$prevEl&&(t=a.navigation.$prevEl),e&&e.off("keydown",a.a11y.onEnterKey),t&&t.off("keydown",a.a11y.onEnterKey),a.pagination&&a.params.pagination.clickable&&a.pagination.bullets&&a.pagination.bullets.length&&a.pagination.$el.off("keydown","."+a.params.pagination.bulletClass,a.a11y.onEnterKey)}},W={init:function(){var e=this;if(e.params.history){if(!J.history||!J.history.pushState)return e.params.history.enabled=!1,void(e.params.hashNavigation.enabled=!0);var t=e.history;t.initialized=!0,t.paths=W.getPathValues(),(t.paths.key||t.paths.value)&&(t.scrollToSlide(0,t.paths.value,e.params.runCallbacksOnInit),e.params.history.replaceState||J.addEventListener("popstate",e.history.setHistoryPopState))}},destroy:function(){this.params.history.replaceState||J.removeEventListener("popstate",this.history.setHistoryPopState)},setHistoryPopState:function(){this.history.paths=W.getPathValues(),this.history.scrollToSlide(this.params.speed,this.history.paths.value,!1)},getPathValues:function(){var e=J.location.pathname.slice(1).split("/").filter(function(e){return""!==e}),t=e.length;return{key:e[t-2],value:e[t-1]}},setHistory:function(e,t){if(this.history.initialized&&this.params.history.enabled){var a=this.slides.eq(t),i=W.slugify(a.attr("data-history"));J.location.pathname.includes(e)||(i=e+"/"+i);var s=J.history.state;s&&s.value===i||(this.params.history.replaceState?J.history.replaceState({value:i},null,i):J.history.pushState({value:i},null,i))}},slugify:function(e){return e.toString().toLowerCase().replace(/\s+/g,"-").replace(/[^\w-]+/g,"").replace(/--+/g,"-").replace(/^-+/,"").replace(/-+$/,"")},scrollToSlide:function(e,t,a){var i=this;if(t)for(var s=0,r=i.slides.length;s<r;s+=1){var n=i.slides.eq(s);if(W.slugify(n.attr("data-history"))===t&&!n.hasClass(i.params.slideDuplicateClass)){var o=n.index();i.slideTo(o,e,a)}}else i.slideTo(0,e,a)}},j={onHashCange:function(){var e=this,t=f.location.hash.replace("#","");if(t!==e.slides.eq(e.activeIndex).attr("data-hash")){var a=e.$wrapperEl.children("."+e.params.slideClass+'[data-hash="'+t+'"]').index();if(void 0===a)return;e.slideTo(a)}},setHash:function(){var e=this;if(e.hashNavigation.initialized&&e.params.hashNavigation.enabled)if(e.params.hashNavigation.replaceState&&J.history&&J.history.replaceState)J.history.replaceState(null,null,"#"+e.slides.eq(e.activeIndex).attr("data-hash")||"");else{var t=e.slides.eq(e.activeIndex),a=t.attr("data-hash")||t.attr("data-history");f.location.hash=a||""}},init:function(){var e=this;if(!(!e.params.hashNavigation.enabled||e.params.history&&e.params.history.enabled)){e.hashNavigation.initialized=!0;var t=f.location.hash.replace("#","");if(t)for(var a=0,i=e.slides.length;a<i;a+=1){var s=e.slides.eq(a);if((s.attr("data-hash")||s.attr("data-history"))===t&&!s.hasClass(e.params.slideDuplicateClass)){var r=s.index();e.slideTo(r,0,e.params.runCallbacksOnInit,!0)}}e.params.hashNavigation.watchState&&L(J).on("hashchange",e.hashNavigation.onHashCange)}},destroy:function(){this.params.hashNavigation.watchState&&L(J).off("hashchange",this.hashNavigation.onHashCange)}},U={run:function(){var e=this,t=e.slides.eq(e.activeIndex),a=e.params.autoplay.delay;t.attr("data-swiper-autoplay")&&(a=t.attr("data-swiper-autoplay")||e.params.autoplay.delay),e.autoplay.timeout=ee.nextTick(function(){e.params.autoplay.reverseDirection?e.params.loop?(e.loopFix(),e.slidePrev(e.params.speed,!0,!0),e.emit("autoplay")):e.isBeginning?e.params.autoplay.stopOnLastSlide?e.autoplay.stop():(e.slideTo(e.slides.length-1,e.params.speed,!0,!0),e.emit("autoplay")):(e.slidePrev(e.params.speed,!0,!0),e.emit("autoplay")):e.params.loop?(e.loopFix(),e.slideNext(e.params.speed,!0,!0),e.emit("autoplay")):e.isEnd?e.params.autoplay.stopOnLastSlide?e.autoplay.stop():(e.slideTo(0,e.params.speed,!0,!0),e.emit("autoplay")):(e.slideNext(e.params.speed,!0,!0),e.emit("autoplay"))},a)},start:function(){var e=this;return void 0===e.autoplay.timeout&&(!e.autoplay.running&&(e.autoplay.running=!0,e.emit("autoplayStart"),e.autoplay.run(),!0))},stop:function(){var e=this;return!!e.autoplay.running&&(void 0!==e.autoplay.timeout&&(e.autoplay.timeout&&(clearTimeout(e.autoplay.timeout),e.autoplay.timeout=void 0),e.autoplay.running=!1,e.emit("autoplayStop"),!0))},pause:function(e){var t=this;t.autoplay.running&&(t.autoplay.paused||(t.autoplay.timeout&&clearTimeout(t.autoplay.timeout),t.autoplay.paused=!0,0!==e&&t.params.autoplay.waitForTransition?(t.$wrapperEl[0].addEventListener("transitionend",t.autoplay.onTransitionEnd),t.$wrapperEl[0].addEventListener("webkitTransitionEnd",t.autoplay.onTransitionEnd)):(t.autoplay.paused=!1,t.autoplay.run())))}},K={setTranslate:function(){for(var e=this,t=e.slides,a=0;a<t.length;a+=1){var i=e.slides.eq(a),s=-i[0].swiperSlideOffset;e.params.virtualTranslate||(s-=e.translate);var r=0;e.isHorizontal()||(r=s,s=0);var n=e.params.fadeEffect.crossFade?Math.max(1-Math.abs(i[0].progress),0):1+Math.min(Math.max(i[0].progress,-1),0);i.css({opacity:n}).transform("translate3d("+s+"px, "+r+"px, 0px)")}},setTransition:function(e){var a=this,t=a.slides,i=a.$wrapperEl;if(t.transition(e),a.params.virtualTranslate&&0!==e){var s=!1;t.transitionEnd(function(){if(!s&&a&&!a.destroyed){s=!0,a.animating=!1;for(var e=["webkitTransitionEnd","transitionend"],t=0;t<e.length;t+=1)i.trigger(e[t])}})}}},_={setTranslate:function(){var e,t=this,a=t.$el,i=t.$wrapperEl,s=t.slides,r=t.width,n=t.height,o=t.rtlTranslate,l=t.size,d=t.params.cubeEffect,p=t.isHorizontal(),c=t.virtual&&t.params.virtual.enabled,u=0;d.shadow&&(p?(0===(e=i.find(".swiper-cube-shadow")).length&&(e=L('<div class="swiper-cube-shadow"></div>'),i.append(e)),e.css({height:r+"px"})):0===(e=a.find(".swiper-cube-shadow")).length&&(e=L('<div class="swiper-cube-shadow"></div>'),a.append(e)));for(var h=0;h<s.length;h+=1){var v=s.eq(h),f=h;c&&(f=parseInt(v.attr("data-swiper-slide-index"),10));var m=90*f,g=Math.floor(m/360);o&&(m=-m,g=Math.floor(-m/360));var b=Math.max(Math.min(v[0].progress,1),-1),w=0,y=0,x=0;f%4==0?(w=4*-g*l,x=0):(f-1)%4==0?(w=0,x=4*-g*l):(f-2)%4==0?(w=l+4*g*l,x=l):(f-3)%4==0&&(w=-l,x=3*l+4*l*g),o&&(w=-w),p||(y=w,w=0);var T="rotateX("+(p?0:-m)+"deg) rotateY("+(p?m:0)+"deg) translate3d("+w+"px, "+y+"px, "+x+"px)";if(b<=1&&-1<b&&(u=90*f+90*b,o&&(u=90*-f-90*b)),v.transform(T),d.slideShadows){var E=p?v.find(".swiper-slide-shadow-left"):v.find(".swiper-slide-shadow-top"),S=p?v.find(".swiper-slide-shadow-right"):v.find(".swiper-slide-shadow-bottom");0===E.length&&(E=L('<div class="swiper-slide-shadow-'+(p?"left":"top")+'"></div>'),v.append(E)),0===S.length&&(S=L('<div class="swiper-slide-shadow-'+(p?"right":"bottom")+'"></div>'),v.append(S)),E.length&&(E[0].style.opacity=Math.max(-b,0)),S.length&&(S[0].style.opacity=Math.max(b,0))}}if(i.css({"-webkit-transform-origin":"50% 50% -"+l/2+"px","-moz-transform-origin":"50% 50% -"+l/2+"px","-ms-transform-origin":"50% 50% -"+l/2+"px","transform-origin":"50% 50% -"+l/2+"px"}),d.shadow)if(p)e.transform("translate3d(0px, "+(r/2+d.shadowOffset)+"px, "+-r/2+"px) rotateX(90deg) rotateZ(0deg) scale("+d.shadowScale+")");else{var C=Math.abs(u)-90*Math.floor(Math.abs(u)/90),M=1.5-(Math.sin(2*C*Math.PI/360)/2+Math.cos(2*C*Math.PI/360)/2),k=d.shadowScale,P=d.shadowScale/M,z=d.shadowOffset;e.transform("scale3d("+k+", 1, "+P+") translate3d(0px, "+(n/2+z)+"px, "+-n/2/P+"px) rotateX(-90deg)")}var $=I.isSafari||I.isUiWebView?-l/2:0;i.transform("translate3d(0px,0,"+$+"px) rotateX("+(t.isHorizontal()?0:u)+"deg) rotateY("+(t.isHorizontal()?-u:0)+"deg)")},setTransition:function(e){var t=this.$el;this.slides.transition(e).find(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").transition(e),this.params.cubeEffect.shadow&&!this.isHorizontal()&&t.find(".swiper-cube-shadow").transition(e)}},Z={setTranslate:function(){for(var e=this,t=e.slides,a=e.rtlTranslate,i=0;i<t.length;i+=1){var s=t.eq(i),r=s[0].progress;e.params.flipEffect.limitRotation&&(r=Math.max(Math.min(s[0].progress,1),-1));var n=-180*r,o=0,l=-s[0].swiperSlideOffset,d=0;if(e.isHorizontal()?a&&(n=-n):(d=l,o=-n,n=l=0),s[0].style.zIndex=-Math.abs(Math.round(r))+t.length,e.params.flipEffect.slideShadows){var p=e.isHorizontal()?s.find(".swiper-slide-shadow-left"):s.find(".swiper-slide-shadow-top"),c=e.isHorizontal()?s.find(".swiper-slide-shadow-right"):s.find(".swiper-slide-shadow-bottom");0===p.length&&(p=L('<div class="swiper-slide-shadow-'+(e.isHorizontal()?"left":"top")+'"></div>'),s.append(p)),0===c.length&&(c=L('<div class="swiper-slide-shadow-'+(e.isHorizontal()?"right":"bottom")+'"></div>'),s.append(c)),p.length&&(p[0].style.opacity=Math.max(-r,0)),c.length&&(c[0].style.opacity=Math.max(r,0))}s.transform("translate3d("+l+"px, "+d+"px, 0px) rotateX("+o+"deg) rotateY("+n+"deg)")}},setTransition:function(e){var a=this,t=a.slides,i=a.activeIndex,s=a.$wrapperEl;if(t.transition(e).find(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").transition(e),a.params.virtualTranslate&&0!==e){var r=!1;t.eq(i).transitionEnd(function(){if(!r&&a&&!a.destroyed){r=!0,a.animating=!1;for(var e=["webkitTransitionEnd","transitionend"],t=0;t<e.length;t+=1)s.trigger(e[t])}})}}},Q={setTranslate:function(){for(var e=this,t=e.width,a=e.height,i=e.slides,s=e.$wrapperEl,r=e.slidesSizesGrid,n=e.params.coverflowEffect,o=e.isHorizontal(),l=e.translate,d=o?t/2-l:a/2-l,p=o?n.rotate:-n.rotate,c=n.depth,u=0,h=i.length;u<h;u+=1){var v=i.eq(u),f=r[u],m=(d-v[0].swiperSlideOffset-f/2)/f*n.modifier,g=o?p*m:0,b=o?0:p*m,w=-c*Math.abs(m),y=o?0:n.stretch*m,x=o?n.stretch*m:0;Math.abs(x)<.001&&(x=0),Math.abs(y)<.001&&(y=0),Math.abs(w)<.001&&(w=0),Math.abs(g)<.001&&(g=0),Math.abs(b)<.001&&(b=0);var T="translate3d("+x+"px,"+y+"px,"+w+"px)  rotateX("+b+"deg) rotateY("+g+"deg)";if(v.transform(T),v[0].style.zIndex=1-Math.abs(Math.round(m)),n.slideShadows){var E=o?v.find(".swiper-slide-shadow-left"):v.find(".swiper-slide-shadow-top"),S=o?v.find(".swiper-slide-shadow-right"):v.find(".swiper-slide-shadow-bottom");0===E.length&&(E=L('<div class="swiper-slide-shadow-'+(o?"left":"top")+'"></div>'),v.append(E)),0===S.length&&(S=L('<div class="swiper-slide-shadow-'+(o?"right":"bottom")+'"></div>'),v.append(S)),E.length&&(E[0].style.opacity=0<m?m:0),S.length&&(S[0].style.opacity=0<-m?-m:0)}}(te.pointerEvents||te.prefixedPointerEvents)&&(s[0].style.perspectiveOrigin=d+"px 50%")},setTransition:function(e){this.slides.transition(e).find(".swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left").transition(e)}},ae={init:function(){var e=this,t=e.params.thumbs,a=e.constructor;t.swiper instanceof a?(e.thumbs.swiper=t.swiper,ee.extend(e.thumbs.swiper.originalParams,{watchSlidesProgress:!0,slideToClickedSlide:!1}),ee.extend(e.thumbs.swiper.params,{watchSlidesProgress:!0,slideToClickedSlide:!1})):ee.isObject(t.swiper)&&(e.thumbs.swiper=new a(ee.extend({},t.swiper,{watchSlidesVisibility:!0,watchSlidesProgress:!0,slideToClickedSlide:!1})),e.thumbs.swiperCreated=!0),e.thumbs.swiper.$el.addClass(e.params.thumbs.thumbsContainerClass),e.thumbs.swiper.on("tap",e.thumbs.onThumbClick)},onThumbClick:function(){var e=this,t=e.thumbs.swiper;if(t){var a=t.clickedIndex,i=t.clickedSlide;if(!(i&&L(i).hasClass(e.params.thumbs.slideThumbActiveClass)||null==a)){var s;if(s=t.params.loop?parseInt(L(t.clickedSlide).attr("data-swiper-slide-index"),10):a,e.params.loop){var r=e.activeIndex;e.slides.eq(r).hasClass(e.params.slideDuplicateClass)&&(e.loopFix(),e._clientLeft=e.$wrapperEl[0].clientLeft,r=e.activeIndex);var n=e.slides.eq(r).prevAll('[data-swiper-slide-index="'+s+'"]').eq(0).index(),o=e.slides.eq(r).nextAll('[data-swiper-slide-index="'+s+'"]').eq(0).index();s=void 0===n?o:void 0===o?n:o-r<r-n?o:n}e.slideTo(s)}}},update:function(e){var t=this,a=t.thumbs.swiper;if(a){var i="auto"===a.params.slidesPerView?a.slidesPerViewDynamic():a.params.slidesPerView;if(t.realIndex!==a.realIndex){var s,r=a.activeIndex;if(a.params.loop){a.slides.eq(r).hasClass(a.params.slideDuplicateClass)&&(a.loopFix(),a._clientLeft=a.$wrapperEl[0].clientLeft,r=a.activeIndex);var n=a.slides.eq(r).prevAll('[data-swiper-slide-index="'+t.realIndex+'"]').eq(0).index(),o=a.slides.eq(r).nextAll('[data-swiper-slide-index="'+t.realIndex+'"]').eq(0).index();s=void 0===n?o:void 0===o?n:o-r==r-n?r:o-r<r-n?o:n}else s=t.realIndex;a.visibleSlidesIndexes.indexOf(s)<0&&(a.params.centeredSlides?s=r<s?s-Math.floor(i/2)+1:s+Math.floor(i/2)-1:r<s&&(s=s-i+1),a.slideTo(s,e?0:void 0))}var l=1,d=t.params.thumbs.slideThumbActiveClass;if(1<t.params.slidesPerView&&!t.params.centeredSlides&&(l=t.params.slidesPerView),a.slides.removeClass(d),a.params.loop)for(var p=0;p<l;p+=1)a.$wrapperEl.children('[data-swiper-slide-index="'+(t.realIndex+p)+'"]').addClass(d);else for(var c=0;c<l;c+=1)a.slides.eq(t.realIndex+c).addClass(d)}}},ie=[C,M,k,P,$,O,N,{name:"mousewheel",params:{mousewheel:{enabled:!1,releaseOnEdges:!1,invert:!1,forceToAxis:!1,sensitivity:1,eventsTarged:"container"}},create:function(){var e=this;ee.extend(e,{mousewheel:{enabled:!1,enable:H.enable.bind(e),disable:H.disable.bind(e),handle:H.handle.bind(e),handleMouseEnter:H.handleMouseEnter.bind(e),handleMouseLeave:H.handleMouseLeave.bind(e),lastScrollTime:ee.now()}})},on:{init:function(){this.params.mousewheel.enabled&&this.mousewheel.enable()},destroy:function(){this.mousewheel.enabled&&this.mousewheel.disable()}}},{name:"navigation",params:{navigation:{nextEl:null,prevEl:null,hideOnClick:!1,disabledClass:"swiper-button-disabled",hiddenClass:"swiper-button-hidden",lockClass:"swiper-button-lock"}},create:function(){var e=this;ee.extend(e,{navigation:{init:G.init.bind(e),update:G.update.bind(e),destroy:G.destroy.bind(e),onNextClick:G.onNextClick.bind(e),onPrevClick:G.onPrevClick.bind(e)}})},on:{init:function(){this.navigation.init(),this.navigation.update()},toEdge:function(){this.navigation.update()},fromEdge:function(){this.navigation.update()},destroy:function(){this.navigation.destroy()},click:function(e){var t=this.navigation,a=t.$nextEl,i=t.$prevEl;!this.params.navigation.hideOnClick||L(e.target).is(i)||L(e.target).is(a)||(a&&a.toggleClass(this.params.navigation.hiddenClass),i&&i.toggleClass(this.params.navigation.hiddenClass))}}},{name:"pagination",params:{pagination:{el:null,bulletElement:"span",clickable:!1,hideOnClick:!1,renderBullet:null,renderProgressbar:null,renderFraction:null,renderCustom:null,progressbarOpposite:!1,type:"bullets",dynamicBullets:!1,dynamicMainBullets:1,formatFractionCurrent:function(e){return e},formatFractionTotal:function(e){return e},bulletClass:"swiper-pagination-bullet",bulletActiveClass:"swiper-pagination-bullet-active",modifierClass:"swiper-pagination-",currentClass:"swiper-pagination-current",totalClass:"swiper-pagination-total",hiddenClass:"swiper-pagination-hidden",progressbarFillClass:"swiper-pagination-progressbar-fill",progressbarOppositeClass:"swiper-pagination-progressbar-opposite",clickableClass:"swiper-pagination-clickable",lockClass:"swiper-pagination-lock"}},create:function(){var e=this;ee.extend(e,{pagination:{init:B.init.bind(e),render:B.render.bind(e),update:B.update.bind(e),destroy:B.destroy.bind(e),dynamicBulletIndex:0}})},on:{init:function(){this.pagination.init(),this.pagination.render(),this.pagination.update()},activeIndexChange:function(){this.params.loop?this.pagination.update():void 0===this.snapIndex&&this.pagination.update()},snapIndexChange:function(){this.params.loop||this.pagination.update()},slidesLengthChange:function(){this.params.loop&&(this.pagination.render(),this.pagination.update())},snapGridLengthChange:function(){this.params.loop||(this.pagination.render(),this.pagination.update())},destroy:function(){this.pagination.destroy()},click:function(e){var t=this;t.params.pagination.el&&t.params.pagination.hideOnClick&&0<t.pagination.$el.length&&!L(e.target).hasClass(t.params.pagination.bulletClass)&&t.pagination.$el.toggleClass(t.params.pagination.hiddenClass)}}},{name:"scrollbar",params:{scrollbar:{el:null,dragSize:"auto",hide:!1,draggable:!1,snapOnRelease:!0,lockClass:"swiper-scrollbar-lock",dragClass:"swiper-scrollbar-drag"}},create:function(){var e=this;ee.extend(e,{scrollbar:{init:X.init.bind(e),destroy:X.destroy.bind(e),updateSize:X.updateSize.bind(e),setTranslate:X.setTranslate.bind(e),setTransition:X.setTransition.bind(e),enableDraggable:X.enableDraggable.bind(e),disableDraggable:X.disableDraggable.bind(e),setDragPosition:X.setDragPosition.bind(e),onDragStart:X.onDragStart.bind(e),onDragMove:X.onDragMove.bind(e),onDragEnd:X.onDragEnd.bind(e),isTouched:!1,timeout:null,dragTimeout:null}})},on:{init:function(){this.scrollbar.init(),this.scrollbar.updateSize(),this.scrollbar.setTranslate()},update:function(){this.scrollbar.updateSize()},resize:function(){this.scrollbar.updateSize()},observerUpdate:function(){this.scrollbar.updateSize()},setTranslate:function(){this.scrollbar.setTranslate()},setTransition:function(e){this.scrollbar.setTransition(e)},destroy:function(){this.scrollbar.destroy()}}},{name:"parallax",params:{parallax:{enabled:!1}},create:function(){ee.extend(this,{parallax:{setTransform:Y.setTransform.bind(this),setTranslate:Y.setTranslate.bind(this),setTransition:Y.setTransition.bind(this)}})},on:{beforeInit:function(){this.params.parallax.enabled&&(this.params.watchSlidesProgress=!0,this.originalParams.watchSlidesProgress=!0)},init:function(){this.params.parallax&&this.parallax.setTranslate()},setTranslate:function(){this.params.parallax&&this.parallax.setTranslate()},setTransition:function(e){this.params.parallax&&this.parallax.setTransition(e)}}},{name:"zoom",params:{zoom:{enabled:!1,maxRatio:3,minRatio:1,toggle:!0,containerClass:"swiper-zoom-container",zoomedSlideClass:"swiper-slide-zoomed"}},create:function(){var i=this,t={enabled:!1,scale:1,currentScale:1,isScaling:!1,gesture:{$slideEl:void 0,slideWidth:void 0,slideHeight:void 0,$imageEl:void 0,$imageWrapEl:void 0,maxRatio:3},image:{isTouched:void 0,isMoved:void 0,currentX:void 0,currentY:void 0,minX:void 0,minY:void 0,maxX:void 0,maxY:void 0,width:void 0,height:void 0,startX:void 0,startY:void 0,touchesStart:{},touchesCurrent:{}},velocity:{x:void 0,y:void 0,prevPositionX:void 0,prevPositionY:void 0,prevTime:void 0}};"onGestureStart onGestureChange onGestureEnd onTouchStart onTouchMove onTouchEnd onTransitionEnd toggle enable disable in out".split(" ").forEach(function(e){t[e]=V[e].bind(i)}),ee.extend(i,{zoom:t});var s=1;Object.defineProperty(i.zoom,"scale",{get:function(){return s},set:function(e){if(s!==e){var t=i.zoom.gesture.$imageEl?i.zoom.gesture.$imageEl[0]:void 0,a=i.zoom.gesture.$slideEl?i.zoom.gesture.$slideEl[0]:void 0;i.emit("zoomChange",e,t,a)}s=e}})},on:{init:function(){this.params.zoom.enabled&&this.zoom.enable()},destroy:function(){this.zoom.disable()},touchStart:function(e){this.zoom.enabled&&this.zoom.onTouchStart(e)},touchEnd:function(e){this.zoom.enabled&&this.zoom.onTouchEnd(e)},doubleTap:function(e){this.params.zoom.enabled&&this.zoom.enabled&&this.params.zoom.toggle&&this.zoom.toggle(e)},transitionEnd:function(){this.zoom.enabled&&this.params.zoom.enabled&&this.zoom.onTransitionEnd()}}},{name:"lazy",params:{lazy:{enabled:!1,loadPrevNext:!1,loadPrevNextAmount:1,loadOnTransitionStart:!1,elementClass:"swiper-lazy",loadingClass:"swiper-lazy-loading",loadedClass:"swiper-lazy-loaded",preloaderClass:"swiper-lazy-preloader"}},create:function(){ee.extend(this,{lazy:{initialImageLoaded:!1,load:F.load.bind(this),loadInSlide:F.loadInSlide.bind(this)}})},on:{beforeInit:function(){this.params.lazy.enabled&&this.params.preloadImages&&(this.params.preloadImages=!1)},init:function(){this.params.lazy.enabled&&!this.params.loop&&0===this.params.initialSlide&&this.lazy.load()},scroll:function(){this.params.freeMode&&!this.params.freeModeSticky&&this.lazy.load()},resize:function(){this.params.lazy.enabled&&this.lazy.load()},scrollbarDragMove:function(){this.params.lazy.enabled&&this.lazy.load()},transitionStart:function(){var e=this;e.params.lazy.enabled&&(e.params.lazy.loadOnTransitionStart||!e.params.lazy.loadOnTransitionStart&&!e.lazy.initialImageLoaded)&&e.lazy.load()},transitionEnd:function(){this.params.lazy.enabled&&!this.params.lazy.loadOnTransitionStart&&this.lazy.load()}}},{name:"controller",params:{controller:{control:void 0,inverse:!1,by:"slide"}},create:function(){var e=this;ee.extend(e,{controller:{control:e.params.controller.control,getInterpolateFunction:R.getInterpolateFunction.bind(e),setTranslate:R.setTranslate.bind(e),setTransition:R.setTransition.bind(e)}})},on:{update:function(){this.controller.control&&this.controller.spline&&(this.controller.spline=void 0,delete this.controller.spline)},resize:function(){this.controller.control&&this.controller.spline&&(this.controller.spline=void 0,delete this.controller.spline)},observerUpdate:function(){this.controller.control&&this.controller.spline&&(this.controller.spline=void 0,delete this.controller.spline)},setTranslate:function(e,t){this.controller.control&&this.controller.setTranslate(e,t)},setTransition:function(e,t){this.controller.control&&this.controller.setTransition(e,t)}}},{name:"a11y",params:{a11y:{enabled:!0,notificationClass:"swiper-notification",prevSlideMessage:"Previous slide",nextSlideMessage:"Next slide",firstSlideMessage:"This is the first slide",lastSlideMessage:"This is the last slide",paginationBulletMessage:"Go to slide {{index}}"}},create:function(){var t=this;ee.extend(t,{a11y:{liveRegion:L('<span class="'+t.params.a11y.notificationClass+'" aria-live="assertive" aria-atomic="true"></span>')}}),Object.keys(q).forEach(function(e){t.a11y[e]=q[e].bind(t)})},on:{init:function(){this.params.a11y.enabled&&(this.a11y.init(),this.a11y.updateNavigation())},toEdge:function(){this.params.a11y.enabled&&this.a11y.updateNavigation()},fromEdge:function(){this.params.a11y.enabled&&this.a11y.updateNavigation()},paginationUpdate:function(){this.params.a11y.enabled&&this.a11y.updatePagination()},destroy:function(){this.params.a11y.enabled&&this.a11y.destroy()}}},{name:"history",params:{history:{enabled:!1,replaceState:!1,key:"slides"}},create:function(){var e=this;ee.extend(e,{history:{init:W.init.bind(e),setHistory:W.setHistory.bind(e),setHistoryPopState:W.setHistoryPopState.bind(e),scrollToSlide:W.scrollToSlide.bind(e),destroy:W.destroy.bind(e)}})},on:{init:function(){this.params.history.enabled&&this.history.init()},destroy:function(){this.params.history.enabled&&this.history.destroy()},transitionEnd:function(){this.history.initialized&&this.history.setHistory(this.params.history.key,this.activeIndex)}}},{name:"hash-navigation",params:{hashNavigation:{enabled:!1,replaceState:!1,watchState:!1}},create:function(){var e=this;ee.extend(e,{hashNavigation:{initialized:!1,init:j.init.bind(e),destroy:j.destroy.bind(e),setHash:j.setHash.bind(e),onHashCange:j.onHashCange.bind(e)}})},on:{init:function(){this.params.hashNavigation.enabled&&this.hashNavigation.init()},destroy:function(){this.params.hashNavigation.enabled&&this.hashNavigation.destroy()},transitionEnd:function(){this.hashNavigation.initialized&&this.hashNavigation.setHash()}}},{name:"autoplay",params:{autoplay:{enabled:!1,delay:3e3,waitForTransition:!0,disableOnInteraction:!0,stopOnLastSlide:!1,reverseDirection:!1}},create:function(){var t=this;ee.extend(t,{autoplay:{running:!1,paused:!1,run:U.run.bind(t),start:U.start.bind(t),stop:U.stop.bind(t),pause:U.pause.bind(t),onTransitionEnd:function(e){t&&!t.destroyed&&t.$wrapperEl&&e.target===this&&(t.$wrapperEl[0].removeEventListener("transitionend",t.autoplay.onTransitionEnd),t.$wrapperEl[0].removeEventListener("webkitTransitionEnd",t.autoplay.onTransitionEnd),t.autoplay.paused=!1,t.autoplay.running?t.autoplay.run():t.autoplay.stop())}}})},on:{init:function(){this.params.autoplay.enabled&&this.autoplay.start()},beforeTransitionStart:function(e,t){this.autoplay.running&&(t||!this.params.autoplay.disableOnInteraction?this.autoplay.pause(e):this.autoplay.stop())},sliderFirstMove:function(){this.autoplay.running&&(this.params.autoplay.disableOnInteraction?this.autoplay.stop():this.autoplay.pause())},destroy:function(){this.autoplay.running&&this.autoplay.stop()}}},{name:"effect-fade",params:{fadeEffect:{crossFade:!1}},create:function(){ee.extend(this,{fadeEffect:{setTranslate:K.setTranslate.bind(this),setTransition:K.setTransition.bind(this)}})},on:{beforeInit:function(){var e=this;if("fade"===e.params.effect){e.classNames.push(e.params.containerModifierClass+"fade");var t={slidesPerView:1,slidesPerColumn:1,slidesPerGroup:1,watchSlidesProgress:!0,spaceBetween:0,virtualTranslate:!0};ee.extend(e.params,t),ee.extend(e.originalParams,t)}},setTranslate:function(){"fade"===this.params.effect&&this.fadeEffect.setTranslate()},setTransition:function(e){"fade"===this.params.effect&&this.fadeEffect.setTransition(e)}}},{name:"effect-cube",params:{cubeEffect:{slideShadows:!0,shadow:!0,shadowOffset:20,shadowScale:.94}},create:function(){ee.extend(this,{cubeEffect:{setTranslate:_.setTranslate.bind(this),setTransition:_.setTransition.bind(this)}})},on:{beforeInit:function(){var e=this;if("cube"===e.params.effect){e.classNames.push(e.params.containerModifierClass+"cube"),e.classNames.push(e.params.containerModifierClass+"3d");var t={slidesPerView:1,slidesPerColumn:1,slidesPerGroup:1,watchSlidesProgress:!0,resistanceRatio:0,spaceBetween:0,centeredSlides:!1,virtualTranslate:!0};ee.extend(e.params,t),ee.extend(e.originalParams,t)}},setTranslate:function(){"cube"===this.params.effect&&this.cubeEffect.setTranslate()},setTransition:function(e){"cube"===this.params.effect&&this.cubeEffect.setTransition(e)}}},{name:"effect-flip",params:{flipEffect:{slideShadows:!0,limitRotation:!0}},create:function(){ee.extend(this,{flipEffect:{setTranslate:Z.setTranslate.bind(this),setTransition:Z.setTransition.bind(this)}})},on:{beforeInit:function(){var e=this;if("flip"===e.params.effect){e.classNames.push(e.params.containerModifierClass+"flip"),e.classNames.push(e.params.containerModifierClass+"3d");var t={slidesPerView:1,slidesPerColumn:1,slidesPerGroup:1,watchSlidesProgress:!0,spaceBetween:0,virtualTranslate:!0};ee.extend(e.params,t),ee.extend(e.originalParams,t)}},setTranslate:function(){"flip"===this.params.effect&&this.flipEffect.setTranslate()},setTransition:function(e){"flip"===this.params.effect&&this.flipEffect.setTransition(e)}}},{name:"effect-coverflow",params:{coverflowEffect:{rotate:50,stretch:0,depth:100,modifier:1,slideShadows:!0}},create:function(){ee.extend(this,{coverflowEffect:{setTranslate:Q.setTranslate.bind(this),setTransition:Q.setTransition.bind(this)}})},on:{beforeInit:function(){var e=this;"coverflow"===e.params.effect&&(e.classNames.push(e.params.containerModifierClass+"coverflow"),e.classNames.push(e.params.containerModifierClass+"3d"),e.params.watchSlidesProgress=!0,e.originalParams.watchSlidesProgress=!0)},setTranslate:function(){"coverflow"===this.params.effect&&this.coverflowEffect.setTranslate()},setTransition:function(e){"coverflow"===this.params.effect&&this.coverflowEffect.setTransition(e)}}},{name:"thumbs",params:{thumbs:{swiper:null,slideThumbActiveClass:"swiper-slide-thumb-active",thumbsContainerClass:"swiper-container-thumbs"}},create:function(){ee.extend(this,{thumbs:{swiper:null,init:ae.init.bind(this),update:ae.update.bind(this),onThumbClick:ae.onThumbClick.bind(this)}})},on:{beforeInit:function(){var e=this.params.thumbs;e&&e.swiper&&(this.thumbs.init(),this.thumbs.update(!0))},slideChange:function(){this.thumbs.swiper&&this.thumbs.update()},update:function(){this.thumbs.swiper&&this.thumbs.update()},resize:function(){this.thumbs.swiper&&this.thumbs.update()},observerUpdate:function(){this.thumbs.swiper&&this.thumbs.update()},setTransition:function(e){var t=this.thumbs.swiper;t&&t.setTransition(e)},beforeDestroy:function(){var e=this.thumbs.swiper;e&&this.thumbs.swiperCreated&&e&&e.destroy()}}}];return void 0===S.use&&(S.use=S.Class.use,S.installModule=S.Class.installModule),S.use(ie),S});

/*
 * jQuery hashchange event - v1.3 - 7/21/2010
 * http://benalman.com/projects/jquery-hashchange-plugin/
 *
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function(e,t,n){"$:nomunge";function f(e){e=e||location.href;return"#"+e.replace(/^[^#]*#?(.*)$/,"$1")}var r="hashchange",i=document,s,o=e.event.special,u=i.documentMode,a="on"+r in t&&(u===n||u>7);e.fn[r]=function(e){return e?this.bind(r,e):this.trigger(r)};e.fn[r].delay=50;o[r]=e.extend(o[r],{setup:function(){if(a){return false}e(s.start)},teardown:function(){if(a){return false}e(s.stop)}});s=function(){function p(){var n=f(),i=h(u);if(n!==u){c(u=n,i);e(t).trigger(r)}else if(i!==u){location.href=location.href.replace(/#.*/,"")+i}o=setTimeout(p,e.fn[r].delay)}var s={},o,u=f(),l=function(e){return e},c=l,h=l;s.start=function(){o||p()};s.stop=function(){o&&clearTimeout(o);o=n};navigator.appName==="Microsoft Internet Explorer"&&!a&&function(){var t,n;s.start=function(){if(!t){n=e.fn[r].src;n=n&&n+f();t=e('<iframe tabindex="-1" title="empty"/>').hide().one("load",function(){n||c(f());p()}).attr("src",n||"javascript:0").insertAfter("body")[0].contentWindow;i.onpropertychange=function(){try{if(event.propertyName==="title"){t.document.title=i.title}}catch(e){}}}};s.stop=l;h=function(){return f(t.location.href)};c=function(n,s){var o=t.document,u=e.fn[r].domain;if(n!==s){o.title=i.title;o.open();u&&o.write('<script>document.domain="'+u+'"</script>');o.close();t.location.hash=n}}}();return s}()})(jQuery,this);

/*!
 * Theia Sticky Sidebar v1.7.0
 * https://github.com/WeCodePixels/theia-sticky-sidebar
 */
!function(i){i.fn.scwStickySidebar=function(t){function e(t,e){var a=o(t,e);a||(console.log("TSS: Body width smaller than options.minWidth. Init is delayed."),i(document).on("scroll."+t.namespace,function(t,e){return function(a){var n=o(t,e);n&&i(this).unbind(a)}}(t,e)),i(window).on("resize."+t.namespace,function(t,e){return function(a){var n=o(t,e);n&&i(this).unbind(a)}}(t,e)))}function o(t,e){return t.initialized===!0||!(i("body").width()<t.minWidth)&&(a(t,e),!0)}function a(t,e){t.initialized=!0;var o=i("#scw-sticky-sidebar-stylesheet-"+t.namespace);0===o.length&&i("head").append(i('<style id="scw-sticky-sidebar-stylesheet-'+t.namespace+'">.scwStickySidebar:after {content: ""; display: table; clear: both;}</style>')),e.each(function(){function e(){a.fixedScrollTop=0,a.sidebar.css({"min-height":"1px"}),a.stickySidebar.css({position:"static",width:"",transform:"none"})}function o(t){var e=t.height();return t.children().each(function(){e=Math.max(e,i(this).height())}),e}var a={};if(a.sidebar=i(this),a.options=t||{},a.container=i(a.options.containerSelector),0==a.container.length&&(a.container=a.sidebar.parent()),a.sidebar.parents().css("-webkit-transform","none"),a.sidebar.css({position:a.options.defaultPosition,overflow:"visible","-webkit-box-sizing":"border-box","-moz-box-sizing":"border-box","box-sizing":"border-box"}),a.stickySidebar=a.sidebar.find(".scwStickySidebar"),0==a.stickySidebar.length){var s=/(?:text|application)\/(?:x-)?(?:javascript|ecmascript)/i;a.sidebar.find("script").filter(function(i,t){return 0===t.type.length||t.type.match(s)}).remove(),a.stickySidebar=i("<div>").addClass("scwStickySidebar").append(a.sidebar.children()),a.sidebar.append(a.stickySidebar)}a.marginBottom=parseInt(a.sidebar.css("margin-bottom")),a.paddingTop=parseInt(a.sidebar.css("padding-top")),a.paddingBottom=parseInt(a.sidebar.css("padding-bottom"));var r=a.stickySidebar.offset().top,d=a.stickySidebar.outerHeight();a.stickySidebar.css("padding-top",1),a.stickySidebar.css("padding-bottom",1),r-=a.stickySidebar.offset().top,d=a.stickySidebar.outerHeight()-d-r,0==r?(a.stickySidebar.css("padding-top",0),a.stickySidebarPaddingTop=0):a.stickySidebarPaddingTop=1,0==d?(a.stickySidebar.css("padding-bottom",0),a.stickySidebarPaddingBottom=0):a.stickySidebarPaddingBottom=1,a.previousScrollTop=null,a.fixedScrollTop=0,e(),a.onScroll=function(a){if(a.stickySidebar.is(":visible")){if(i("body").width()<a.options.minWidth)return void e();if(a.options.disableOnResponsiveLayouts){var s=a.sidebar.outerWidth("none"==a.sidebar.css("float"));if(s+50>a.container.width())return void e()}var r=i(document).scrollTop(),d="static";if(r>=a.sidebar.offset().top+(a.paddingTop-a.options.additionalMarginTop)){var c,p=a.paddingTop+t.additionalMarginTop,b=a.paddingBottom+a.marginBottom+t.additionalMarginBottom,l=a.sidebar.offset().top,f=a.sidebar.offset().top+o(a.container),h=0+t.additionalMarginTop,g=a.stickySidebar.outerHeight()+p+b<i(window).height();c=g?h+a.stickySidebar.outerHeight():i(window).height()-a.marginBottom-a.paddingBottom-t.additionalMarginBottom;var u=l-r+a.paddingTop,S=f-r-a.paddingBottom-a.marginBottom,y=a.stickySidebar.offset().top-r,m=a.previousScrollTop-r;"fixed"==a.stickySidebar.css("position")&&"modern"==a.options.sidebarBehavior&&(y+=m),"stick-to-top"==a.options.sidebarBehavior&&(y=t.additionalMarginTop),"stick-to-bottom"==a.options.sidebarBehavior&&(y=c-a.stickySidebar.outerHeight()),y=m>0?Math.min(y,h):Math.max(y,c-a.stickySidebar.outerHeight()),y=Math.max(y,u),y=Math.min(y,S-a.stickySidebar.outerHeight());var k=a.container.height()==a.stickySidebar.outerHeight();d=(k||y!=h)&&(k||y!=c-a.stickySidebar.outerHeight())?r+y-a.sidebar.offset().top-a.paddingTop<=t.additionalMarginTop?"static":"absolute":"fixed"}if("fixed"==d){var v=i(document).scrollLeft();a.stickySidebar.css({position:"fixed",width:n(a.stickySidebar)+"px",transform:"translateY("+y+"px)",left:a.sidebar.offset().left+parseInt(a.sidebar.css("padding-left"))-v+"px",top:"0px"})}else if("absolute"==d){var x={};"absolute"!=a.stickySidebar.css("position")&&(x.position="absolute",x.transform="translateY("+(r+y-a.sidebar.offset().top-a.stickySidebarPaddingTop-a.stickySidebarPaddingBottom)+"px)",x.top="0px"),x.width=n(a.stickySidebar)+"px",x.left="",a.stickySidebar.css(x)}else"static"==d&&e();"static"!=d&&1==a.options.updateSidebarHeight&&a.sidebar.css({"min-height":a.stickySidebar.outerHeight()+a.stickySidebar.offset().top-a.sidebar.offset().top+a.paddingBottom}),a.previousScrollTop=r}},a.onScroll(a),i(document).on("scroll."+a.options.namespace,function(i){return function(){i.onScroll(i)}}(a)),i(window).on("resize."+a.options.namespace,function(i){return function(){i.stickySidebar.css({position:"static"}),i.onScroll(i)}}(a)),"undefined"!=typeof ResizeSensor&&new ResizeSensor(a.stickySidebar[0],function(i){return function(){i.onScroll(i)}}(a))})}function n(i){var t;try{t=i[0].getBoundingClientRect().width}catch(i){}return"undefined"==typeof t&&(t=i.width()),t}var s={containerSelector:"",additionalMarginTop:0,additionalMarginBottom:0,updateSidebarHeight:!0,minWidth:0,disableOnResponsiveLayouts:!0,sidebarBehavior:"modern",defaultPosition:"relative",namespace:"TSS"};return t=i.extend(s,t),t.additionalMarginTop=parseInt(t.additionalMarginTop)||0,t.additionalMarginBottom=parseInt(t.additionalMarginBottom)||0,e(t,this),this}}(jQuery);
!function(){var e=function(t,i){function s(){this.q=[],this.add=function(e){this.q.push(e)};var e,t;this.call=function(){for(e=0,t=this.q.length;e<t;e++)this.q[e].call()}}function o(e,t){return e.currentStyle?e.currentStyle[t]:window.getComputedStyle?window.getComputedStyle(e,null).getPropertyValue(t):e.style[t]}function n(e,t){if(e.resizedAttached){if(e.resizedAttached)return void e.resizedAttached.add(t)}else e.resizedAttached=new s,e.resizedAttached.add(t);e.resizeSensor=document.createElement("div"),e.resizeSensor.className="resize-sensor";var i="position: absolute; left: 0; top: 0; right: 0; bottom: 0; overflow: hidden; z-index: -1; visibility: hidden;",n="position: absolute; left: 0; top: 0; transition: 0s;";e.resizeSensor.style.cssText=i,e.resizeSensor.innerHTML='<div class="resize-sensor-expand" style="'+i+'"><div style="'+n+'"></div></div><div class="resize-sensor-shrink" style="'+i+'"><div style="'+n+' width: 200%; height: 200%"></div></div>',e.appendChild(e.resizeSensor),{fixed:1,absolute:1}[o(e,"position")]||(e.style.position="relative");var d,r,l=e.resizeSensor.childNodes[0],c=l.childNodes[0],h=e.resizeSensor.childNodes[1],a=(h.childNodes[0],function(){c.style.width=l.offsetWidth+10+"px",c.style.height=l.offsetHeight+10+"px",l.scrollLeft=l.scrollWidth,l.scrollTop=l.scrollHeight,h.scrollLeft=h.scrollWidth,h.scrollTop=h.scrollHeight,d=e.offsetWidth,r=e.offsetHeight});a();var f=function(){e.resizedAttached&&e.resizedAttached.call()},u=function(e,t,i){e.attachEvent?e.attachEvent("on"+t,i):e.addEventListener(t,i)},p=function(){e.offsetWidth==d&&e.offsetHeight==r||f(),a()};u(l,"scroll",p),u(h,"scroll",p)}var d=Object.prototype.toString.call(t),r="[object Array]"===d||"[object NodeList]"===d||"[object HTMLCollection]"===d||"undefined"!=typeof jQuery&&t instanceof jQuery||"undefined"!=typeof Elements&&t instanceof Elements;if(r)for(var l=0,c=t.length;l<c;l++)n(t[l],i);else n(t,i);this.detach=function(){if(r)for(var i=0,s=t.length;i<s;i++)e.detach(t[i]);else e.detach(t)}};e.detach=function(e){e.resizeSensor&&(e.removeChild(e.resizeSensor),delete e.resizeSensor,delete e.resizedAttached)},"undefined"!=typeof module&&"undefined"!=typeof module.exports?module.exports=e:window.ResizeSensor=e}();

/*!
 * jQuery Color Animations v@VERSION
 * https://github.com/jquery/jquery-color
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 *
 * Date: @DATE
 */
!function(r,n){function t(r,n,t){var e=f[n.type]||{};return null==r?t||!n.def?null:n.def:(r=e.floor?~~r:parseFloat(r),isNaN(r)?n.def:e.mod?(r+e.mod)%e.mod:0>r?0:e.max<r?e.max:r)}function e(n){var t=l(),e=t._rgba=[];return n=n.toLowerCase(),h(u,function(r,o){var a,s=o.re.exec(n),i=s&&o.parse(s),u=o.space||"rgba";return i?(a=t[u](i),t[c[u].cache]=a[c[u].cache],e=t._rgba=a._rgba,!1):void 0}),e.length?("0,0,0,0"===e.join()&&r.extend(e,a.transparent),t):a[n]}function o(r,n,t){return t=(t+1)%1,1>6*t?r+(n-r)*t*6:1>2*t?n:2>3*t?r+(n-r)*(2/3-t)*6:r}var a,s="backgroundColor borderBottomColor borderLeftColor borderRightColor borderTopColor color columnRuleColor outlineColor textDecorationColor textEmphasisColor",i=/^([\-+])=\s*(\d+\.?\d*)/,u=[{re:/rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,parse:function(r){return[r[1],r[2],r[3],r[4]]}},{re:/rgba?\(\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,parse:function(r){return[2.55*r[1],2.55*r[2],2.55*r[3],r[4]]}},{re:/#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/,parse:function(r){return[parseInt(r[1],16),parseInt(r[2],16),parseInt(r[3],16)]}},{re:/#([a-f0-9])([a-f0-9])([a-f0-9])/,parse:function(r){return[parseInt(r[1]+r[1],16),parseInt(r[2]+r[2],16),parseInt(r[3]+r[3],16)]}},{re:/hsla?\(\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,space:"hsla",parse:function(r){return[r[1],r[2]/100,r[3]/100,r[4]]}}],l=r.Color=function(n,t,e,o){return new r.Color.fn.parse(n,t,e,o)},c={rgba:{props:{red:{idx:0,type:"byte"},green:{idx:1,type:"byte"},blue:{idx:2,type:"byte"}}},hsla:{props:{hue:{idx:0,type:"degrees"},saturation:{idx:1,type:"percent"},lightness:{idx:2,type:"percent"}}}},f={"byte":{floor:!0,max:255},percent:{max:1},degrees:{mod:360,floor:!0}},p=l.support={},d=r("<p>")[0],h=r.each;d.style.cssText="background-color:rgba(1,1,1,.5)",p.rgba=d.style.backgroundColor.indexOf("rgba")>-1,h(c,function(r,n){n.cache="_"+r,n.props.alpha={idx:3,type:"percent",def:1}}),l.fn=r.extend(l.prototype,{parse:function(o,s,i,u){if(o===n)return this._rgba=[null,null,null,null],this;(o.jquery||o.nodeType)&&(o=r(o).css(s),s=n);var f=this,p=r.type(o),d=this._rgba=[];return s!==n&&(o=[o,s,i,u],p="array"),"string"===p?this.parse(e(o)||a._default):"array"===p?(h(c.rgba.props,function(r,n){d[n.idx]=t(o[n.idx],n)}),this):"object"===p?(o instanceof l?h(c,function(r,n){o[n.cache]&&(f[n.cache]=o[n.cache].slice())}):h(c,function(n,e){var a=e.cache;h(e.props,function(r,n){if(!f[a]&&e.to){if("alpha"===r||null==o[r])return;f[a]=e.to(f._rgba)}f[a][n.idx]=t(o[r],n,!0)}),f[a]&&r.inArray(null,f[a].slice(0,3))<0&&(f[a][3]=1,e.from&&(f._rgba=e.from(f[a])))}),this):void 0},is:function(r){var n=l(r),t=!0,e=this;return h(c,function(r,o){var a,s=n[o.cache];return s&&(a=e[o.cache]||o.to&&o.to(e._rgba)||[],h(o.props,function(r,n){return null!=s[n.idx]?t=s[n.idx]===a[n.idx]:void 0})),t}),t},_space:function(){var r=[],n=this;return h(c,function(t,e){n[e.cache]&&r.push(t)}),r.pop()},transition:function(r,n){var e=l(r),o=e._space(),a=c[o],s=0===this.alpha()?l("transparent"):this,i=s[a.cache]||a.to(s._rgba),u=i.slice();return e=e[a.cache],h(a.props,function(r,o){var a=o.idx,s=i[a],l=e[a],c=f[o.type]||{};null!==l&&(null===s?u[a]=l:(c.mod&&(l-s>c.mod/2?s+=c.mod:s-l>c.mod/2&&(s-=c.mod)),u[a]=t((l-s)*n+s,o)))}),this[o](u)},blend:function(n){if(1===this._rgba[3])return this;var t=this._rgba.slice(),e=t.pop(),o=l(n)._rgba;return l(r.map(t,function(r,n){return(1-e)*o[n]+e*r}))},toRgbaString:function(){var n="rgba(",t=r.map(this._rgba,function(r,n){return null==r?n>2?1:0:r});return 1===t[3]&&(t.pop(),n="rgb("),n+t.join()+")"},toHslaString:function(){var n="hsla(",t=r.map(this.hsla(),function(r,n){return null==r&&(r=n>2?1:0),n&&3>n&&(r=Math.round(100*r)+"%"),r});return 1===t[3]&&(t.pop(),n="hsl("),n+t.join()+")"},toHexString:function(n){var t=this._rgba.slice(),e=t.pop();return n&&t.push(~~(255*e)),"#"+r.map(t,function(r){return r=(r||0).toString(16),1===r.length?"0"+r:r}).join("")},toString:function(){return 0===this._rgba[3]?"transparent":this.toRgbaString()}}),l.fn.parse.prototype=l.fn,c.hsla.to=function(r){if(null==r[0]||null==r[1]||null==r[2])return[null,null,null,r[3]];var n,t,e=r[0]/255,o=r[1]/255,a=r[2]/255,s=r[3],i=Math.max(e,o,a),u=Math.min(e,o,a),l=i-u,c=i+u,f=.5*c;return n=u===i?0:e===i?60*(o-a)/l+360:o===i?60*(a-e)/l+120:60*(e-o)/l+240,t=0===l?0:.5>=f?l/c:l/(2-c),[Math.round(n)%360,t,f,null==s?1:s]},c.hsla.from=function(r){if(null==r[0]||null==r[1]||null==r[2])return[null,null,null,r[3]];var n=r[0]/360,t=r[1],e=r[2],a=r[3],s=.5>=e?e*(1+t):e+t-e*t,i=2*e-s;return[Math.round(255*o(i,s,n+1/3)),Math.round(255*o(i,s,n)),Math.round(255*o(i,s,n-1/3)),a]},h(c,function(e,o){var a=o.props,s=o.cache,u=o.to,c=o.from;l.fn[e]=function(e){if(u&&!this[s]&&(this[s]=u(this._rgba)),e===n)return this[s].slice();var o,i=r.type(e),f="array"===i||"object"===i?e:arguments,p=this[s].slice();return h(a,function(r,n){var e=f["object"===i?r:n.idx];null==e&&(e=p[n.idx]),p[n.idx]=t(e,n)}),c?(o=l(c(p)),o[s]=p,o):l(p)},h(a,function(n,t){l.fn[n]||(l.fn[n]=function(o){var a,s=r.type(o),u="alpha"===n?this._hsla?"hsla":"rgba":e,l=this[u](),c=l[t.idx];return"undefined"===s?c:("function"===s&&(o=o.call(this,c),s=r.type(o)),null==o&&t.empty?this:("string"===s&&(a=i.exec(o),a&&(o=c+parseFloat(a[2])*("+"===a[1]?1:-1))),l[t.idx]=o,this[u](l)))})})}),l.hook=function(n){var t=n.split(" ");h(t,function(n,t){r.cssHooks[t]={set:function(n,o){var a,s,i="";if("transparent"!==o&&("string"!==r.type(o)||(a=e(o)))){if(o=l(a||o),!p.rgba&&1!==o._rgba[3]){for(s="backgroundColor"===t?n.parentNode:n;(""===i||"transparent"===i)&&s&&s.style;)try{i=r.css(s,"backgroundColor"),s=s.parentNode}catch(u){}o=o.blend(i&&"transparent"!==i?i:"_default")}o=o.toRgbaString()}try{n.style[t]=o}catch(u){}}},r.fx.step[t]=function(n){n.colorInit||(n.start=l(n.elem,t),n.end=l(n.end),n.colorInit=!0),r.cssHooks[t].set(n.elem,n.start.transition(n.end,n.pos))}})},l.hook(s),r.cssHooks.borderColor={expand:function(r){var n={};return h(["Top","Right","Bottom","Left"],function(t,e){n["border"+e+"Color"]=r}),n}},a=r.Color.names={aqua:"#00ffff",black:"#000000",blue:"#0000ff",fuchsia:"#ff00ff",gray:"#808080",green:"#008000",lime:"#00ff00",maroon:"#800000",navy:"#000080",olive:"#808000",purple:"#800080",red:"#ff0000",silver:"#c0c0c0",teal:"#008080",white:"#ffffff",yellow:"#ffff00",transparent:[null,null,null,0],_default:"#ffffff"}}(jQuery);

/*!
 * jQuery Form Plugin
 * version: 3.51.0-2014.06.20
 * Requires jQuery v1.5 or later
 * Copyright (c) 2014 M. Alsup
 * Examples and documentation at: http://malsup.com/jquery/form/
 * Project repository: https://github.com/malsup/form
 * Dual licensed under the MIT and GPL licenses.
 * https://github.com/malsup/form#copyright-and-license
 */
!function(e){"use strict";"function"==typeof define&&define.amd?define(["jquery"],e):e("undefined"!=typeof jQuery?jQuery:window.Zepto)}(function(e){"use strict";function t(t){var r=t.data;t.isDefaultPrevented()||(t.preventDefault(),e(t.target).ajaxSubmit(r))}function r(t){var r=t.target,a=e(r);if(!a.is("[type=submit],[type=image]")){var n=a.closest("[type=submit]");if(0===n.length)return;r=n[0]}var i=this;if(i.clk=r,"image"==r.type)if(void 0!==t.offsetX)i.clk_x=t.offsetX,i.clk_y=t.offsetY;else if("function"==typeof e.fn.offset){var o=a.offset();i.clk_x=t.pageX-o.left,i.clk_y=t.pageY-o.top}else i.clk_x=t.pageX-r.offsetLeft,i.clk_y=t.pageY-r.offsetTop;setTimeout(function(){i.clk=i.clk_x=i.clk_y=null},100)}function a(){if(e.fn.ajaxSubmit.debug){var t="[jquery.form] "+Array.prototype.join.call(arguments,"");window.console&&window.console.log?window.console.log(t):window.opera&&window.opera.postError&&window.opera.postError(t)}}var n={};n.fileapi=void 0!==e("<input type='file'/>").get(0).files,n.formdata=void 0!==window.FormData;var i=!!e.fn.prop;e.fn.attr2=function(){if(!i)return this.attr.apply(this,arguments);var e=this.prop.apply(this,arguments);return e&&e.jquery||"string"==typeof e?e:this.attr.apply(this,arguments)},e.fn.ajaxSubmit=function(t){function r(r){var a,n,i=e.param(r,t.traditional).split("&"),o=i.length,s=[];for(a=0;o>a;a++)i[a]=i[a].replace(/\+/g," "),n=i[a].split("="),s.push([decodeURIComponent(n[0]),decodeURIComponent(n[1])]);return s}function o(a){for(var n=new FormData,i=0;i<a.length;i++)n.append(a[i].name,a[i].value);if(t.extraData){var o=r(t.extraData);for(i=0;i<o.length;i++)o[i]&&n.append(o[i][0],o[i][1])}t.data=null;var s=e.extend(!0,{},e.ajaxSettings,t,{contentType:!1,processData:!1,cache:!1,type:u||"POST"});t.uploadProgress&&(s.xhr=function(){var r=e.ajaxSettings.xhr();return r.upload&&r.upload.addEventListener("progress",function(e){var r=0,a=e.loaded||e.position,n=e.total;e.lengthComputable&&(r=Math.ceil(a/n*100)),t.uploadProgress(e,a,n,r)},!1),r}),s.data=null;var c=s.beforeSend;return s.beforeSend=function(e,r){r.data=t.formData?t.formData:n,c&&c.call(this,e,r)},e.ajax(s)}function s(r){function n(e){var t=null;try{e.contentWindow&&(t=e.contentWindow.document)}catch(r){a("cannot get iframe.contentWindow document: "+r)}if(t)return t;try{t=e.contentDocument?e.contentDocument:e.document}catch(r){a("cannot get iframe.contentDocument: "+r),t=e.document}return t}function o(){function t(){try{var e=n(g).readyState;a("state = "+e),e&&"uninitialized"==e.toLowerCase()&&setTimeout(t,50)}catch(r){a("Server abort: ",r," (",r.name,")"),s(k),j&&clearTimeout(j),j=void 0}}var r=f.attr2("target"),i=f.attr2("action"),o="multipart/form-data",c=f.attr("enctype")||f.attr("encoding")||o;w.setAttribute("target",p),(!u||/post/i.test(u))&&w.setAttribute("method","POST"),i!=m.url&&w.setAttribute("action",m.url),m.skipEncodingOverride||u&&!/post/i.test(u)||f.attr({encoding:"multipart/form-data",enctype:"multipart/form-data"}),m.timeout&&(j=setTimeout(function(){T=!0,s(D)},m.timeout));var l=[];try{if(m.extraData)for(var d in m.extraData)m.extraData.hasOwnProperty(d)&&l.push(e.isPlainObject(m.extraData[d])&&m.extraData[d].hasOwnProperty("name")&&m.extraData[d].hasOwnProperty("value")?e('<input type="hidden" name="'+m.extraData[d].name+'">').val(m.extraData[d].value).appendTo(w)[0]:e('<input type="hidden" name="'+d+'">').val(m.extraData[d]).appendTo(w)[0]);m.iframeTarget||v.appendTo("body"),g.attachEvent?g.attachEvent("onload",s):g.addEventListener("load",s,!1),setTimeout(t,15);try{w.submit()}catch(h){var x=document.createElement("form").submit;x.apply(w)}}finally{w.setAttribute("action",i),w.setAttribute("enctype",c),r?w.setAttribute("target",r):f.removeAttr("target"),e(l).remove()}}function s(t){if(!x.aborted&&!F){if(M=n(g),M||(a("cannot access response document"),t=k),t===D&&x)return x.abort("timeout"),void S.reject(x,"timeout");if(t==k&&x)return x.abort("server abort"),void S.reject(x,"error","server abort");if(M&&M.location.href!=m.iframeSrc||T){g.detachEvent?g.detachEvent("onload",s):g.removeEventListener("load",s,!1);var r,i="success";try{if(T)throw"timeout";var o="xml"==m.dataType||M.XMLDocument||e.isXMLDoc(M);if(a("isXml="+o),!o&&window.opera&&(null===M.body||!M.body.innerHTML)&&--O)return a("requeing onLoad callback, DOM not available"),void setTimeout(s,250);var u=M.body?M.body:M.documentElement;x.responseText=u?u.innerHTML:null,x.responseXML=M.XMLDocument?M.XMLDocument:M,o&&(m.dataType="xml"),x.getResponseHeader=function(e){var t={"content-type":m.dataType};return t[e.toLowerCase()]},u&&(x.status=Number(u.getAttribute("status"))||x.status,x.statusText=u.getAttribute("statusText")||x.statusText);var c=(m.dataType||"").toLowerCase(),l=/(json|script|text)/.test(c);if(l||m.textarea){var f=M.getElementsByTagName("textarea")[0];if(f)x.responseText=f.value,x.status=Number(f.getAttribute("status"))||x.status,x.statusText=f.getAttribute("statusText")||x.statusText;else if(l){var p=M.getElementsByTagName("pre")[0],h=M.getElementsByTagName("body")[0];p?x.responseText=p.textContent?p.textContent:p.innerText:h&&(x.responseText=h.textContent?h.textContent:h.innerText)}}else"xml"==c&&!x.responseXML&&x.responseText&&(x.responseXML=X(x.responseText));try{E=_(x,c,m)}catch(y){i="parsererror",x.error=r=y||i}}catch(y){a("error caught: ",y),i="error",x.error=r=y||i}x.aborted&&(a("upload aborted"),i=null),x.status&&(i=x.status>=200&&x.status<300||304===x.status?"success":"error"),"success"===i?(m.success&&m.success.call(m.context,E,"success",x),S.resolve(x.responseText,"success",x),d&&e.event.trigger("ajaxSuccess",[x,m])):i&&(void 0===r&&(r=x.statusText),m.error&&m.error.call(m.context,x,i,r),S.reject(x,"error",r),d&&e.event.trigger("ajaxError",[x,m,r])),d&&e.event.trigger("ajaxComplete",[x,m]),d&&!--e.active&&e.event.trigger("ajaxStop"),m.complete&&m.complete.call(m.context,x,i),F=!0,m.timeout&&clearTimeout(j),setTimeout(function(){m.iframeTarget?v.attr("src",m.iframeSrc):v.remove(),x.responseXML=null},100)}}}var c,l,m,d,p,v,g,x,y,b,T,j,w=f[0],S=e.Deferred();if(S.abort=function(e){x.abort(e)},r)for(l=0;l<h.length;l++)c=e(h[l]),i?c.prop("disabled",!1):c.removeAttr("disabled");if(m=e.extend(!0,{},e.ajaxSettings,t),m.context=m.context||m,p="jqFormIO"+(new Date).getTime(),m.iframeTarget?(v=e(m.iframeTarget),b=v.attr2("name"),b?p=b:v.attr2("name",p)):(v=e('<iframe name="'+p+'" src="'+m.iframeSrc+'" />'),v.css({position:"absolute",top:"-1000px",left:"-1000px"})),g=v[0],x={aborted:0,responseText:null,responseXML:null,status:0,statusText:"n/a",getAllResponseHeaders:function(){},getResponseHeader:function(){},setRequestHeader:function(){},abort:function(t){var r="timeout"===t?"timeout":"aborted";a("aborting upload... "+r),this.aborted=1;try{g.contentWindow.document.execCommand&&g.contentWindow.document.execCommand("Stop")}catch(n){}v.attr("src",m.iframeSrc),x.error=r,m.error&&m.error.call(m.context,x,r,t),d&&e.event.trigger("ajaxError",[x,m,r]),m.complete&&m.complete.call(m.context,x,r)}},d=m.global,d&&0===e.active++&&e.event.trigger("ajaxStart"),d&&e.event.trigger("ajaxSend",[x,m]),m.beforeSend&&m.beforeSend.call(m.context,x,m)===!1)return m.global&&e.active--,S.reject(),S;if(x.aborted)return S.reject(),S;y=w.clk,y&&(b=y.name,b&&!y.disabled&&(m.extraData=m.extraData||{},m.extraData[b]=y.value,"image"==y.type&&(m.extraData[b+".x"]=w.clk_x,m.extraData[b+".y"]=w.clk_y)));var D=1,k=2,A=e("meta[name=csrf-token]").attr("content"),L=e("meta[name=csrf-param]").attr("content");L&&A&&(m.extraData=m.extraData||{},m.extraData[L]=A),m.forceSync?o():setTimeout(o,10);var E,M,F,O=50,X=e.parseXML||function(e,t){return window.ActiveXObject?(t=new ActiveXObject("Microsoft.XMLDOM"),t.async="false",t.loadXML(e)):t=(new DOMParser).parseFromString(e,"text/xml"),t&&t.documentElement&&"parsererror"!=t.documentElement.nodeName?t:null},C=e.parseJSON||function(e){return window.eval("("+e+")")},_=function(t,r,a){var n=t.getResponseHeader("content-type")||"",i="xml"===r||!r&&n.indexOf("xml")>=0,o=i?t.responseXML:t.responseText;return i&&"parsererror"===o.documentElement.nodeName&&e.error&&e.error("parsererror"),a&&a.dataFilter&&(o=a.dataFilter(o,r)),"string"==typeof o&&("json"===r||!r&&n.indexOf("json")>=0?o=C(o):("script"===r||!r&&n.indexOf("javascript")>=0)&&e.globalEval(o)),o};return S}if(!this.length)return a("ajaxSubmit: skipping submit process - no element selected"),this;var u,c,l,f=this;"function"==typeof t?t={success:t}:void 0===t&&(t={}),u=t.type||this.attr2("method"),c=t.url||this.attr2("action"),l="string"==typeof c?e.trim(c):"",l=l||window.location.href||"",l&&(l=(l.match(/^([^#]+)/)||[])[1]),t=e.extend(!0,{url:l,success:e.ajaxSettings.success,type:u||e.ajaxSettings.type,iframeSrc:/^https/i.test(window.location.href||"")?"javascript:false":"about:blank"},t);var m={};if(this.trigger("form-pre-serialize",[this,t,m]),m.veto)return a("ajaxSubmit: submit vetoed via form-pre-serialize trigger"),this;if(t.beforeSerialize&&t.beforeSerialize(this,t)===!1)return a("ajaxSubmit: submit aborted via beforeSerialize callback"),this;var d=t.traditional;void 0===d&&(d=e.ajaxSettings.traditional);var p,h=[],v=this.formToArray(t.semantic,h);if(t.data&&(t.extraData=t.data,p=e.param(t.data,d)),t.beforeSubmit&&t.beforeSubmit(v,this,t)===!1)return a("ajaxSubmit: submit aborted via beforeSubmit callback"),this;if(this.trigger("form-submit-validate",[v,this,t,m]),m.veto)return a("ajaxSubmit: submit vetoed via form-submit-validate trigger"),this;var g=e.param(v,d);p&&(g=g?g+"&"+p:p),"GET"==t.type.toUpperCase()?(t.url+=(t.url.indexOf("?")>=0?"&":"?")+g,t.data=null):t.data=g;var x=[];if(t.resetForm&&x.push(function(){f.resetForm()}),t.clearForm&&x.push(function(){f.clearForm(t.includeHidden)}),!t.dataType&&t.target){var y=t.success||function(){};x.push(function(r){var a=t.replaceTarget?"replaceWith":"html";e(t.target)[a](r).each(y,arguments)})}else t.success&&x.push(t.success);if(t.success=function(e,r,a){for(var n=t.context||this,i=0,o=x.length;o>i;i++)x[i].apply(n,[e,r,a||f,f])},t.error){var b=t.error;t.error=function(e,r,a){var n=t.context||this;b.apply(n,[e,r,a,f])}}if(t.complete){var T=t.complete;t.complete=function(e,r){var a=t.context||this;T.apply(a,[e,r,f])}}var j=e("input[type=file]:enabled",this).filter(function(){return""!==e(this).val()}),w=j.length>0,S="multipart/form-data",D=f.attr("enctype")==S||f.attr("encoding")==S,k=n.fileapi&&n.formdata;a("fileAPI :"+k);var A,L=(w||D)&&!k;t.iframe!==!1&&(t.iframe||L)?t.closeKeepAlive?e.get(t.closeKeepAlive,function(){A=s(v)}):A=s(v):A=(w||D)&&k?o(v):e.ajax(t),f.removeData("jqxhr").data("jqxhr",A);for(var E=0;E<h.length;E++)h[E]=null;return this.trigger("form-submit-notify",[this,t]),this},e.fn.ajaxForm=function(n){if(n=n||{},n.delegation=n.delegation&&e.isFunction(e.fn.on),!n.delegation&&0===this.length){var i={s:this.selector,c:this.context};return!e.isReady&&i.s?(a("DOM not ready, queuing ajaxForm"),e(function(){e(i.s,i.c).ajaxForm(n)}),this):(a("terminating; zero elements found by selector"+(e.isReady?"":" (DOM not ready)")),this)}return n.delegation?(e(document).off("submit.form-plugin",this.selector,t).off("click.form-plugin",this.selector,r).on("submit.form-plugin",this.selector,n,t).on("click.form-plugin",this.selector,n,r),this):this.ajaxFormUnbind().bind("submit.form-plugin",n,t).bind("click.form-plugin",n,r)},e.fn.ajaxFormUnbind=function(){return this.unbind("submit.form-plugin click.form-plugin")},e.fn.formToArray=function(t,r){var a=[];if(0===this.length)return a;var i,o=this[0],s=this.attr("id"),u=t?o.getElementsByTagName("*"):o.elements;if(u&&!/MSIE [678]/.test(navigator.userAgent)&&(u=e(u).get()),s&&(i=e(':input[form="'+s+'"]').get(),i.length&&(u=(u||[]).concat(i))),!u||!u.length)return a;var c,l,f,m,d,p,h;for(c=0,p=u.length;p>c;c++)if(d=u[c],f=d.name,f&&!d.disabled)if(t&&o.clk&&"image"==d.type)o.clk==d&&(a.push({name:f,value:e(d).val(),type:d.type}),a.push({name:f+".x",value:o.clk_x},{name:f+".y",value:o.clk_y}));else if(m=e.fieldValue(d,!0),m&&m.constructor==Array)for(r&&r.push(d),l=0,h=m.length;h>l;l++)a.push({name:f,value:m[l]});else if(n.fileapi&&"file"==d.type){r&&r.push(d);var v=d.files;if(v.length)for(l=0;l<v.length;l++)a.push({name:f,value:v[l],type:d.type});else a.push({name:f,value:"",type:d.type})}else null!==m&&"undefined"!=typeof m&&(r&&r.push(d),a.push({name:f,value:m,type:d.type,required:d.required}));if(!t&&o.clk){var g=e(o.clk),x=g[0];f=x.name,f&&!x.disabled&&"image"==x.type&&(a.push({name:f,value:g.val()}),a.push({name:f+".x",value:o.clk_x},{name:f+".y",value:o.clk_y}))}return a},e.fn.formSerialize=function(t){return e.param(this.formToArray(t))},e.fn.fieldSerialize=function(t){var r=[];return this.each(function(){var a=this.name;if(a){var n=e.fieldValue(this,t);if(n&&n.constructor==Array)for(var i=0,o=n.length;o>i;i++)r.push({name:a,value:n[i]});else null!==n&&"undefined"!=typeof n&&r.push({name:this.name,value:n})}}),e.param(r)},e.fn.fieldValue=function(t){for(var r=[],a=0,n=this.length;n>a;a++){var i=this[a],o=e.fieldValue(i,t);null===o||"undefined"==typeof o||o.constructor==Array&&!o.length||(o.constructor==Array?e.merge(r,o):r.push(o))}return r},e.fieldValue=function(t,r){var a=t.name,n=t.type,i=t.tagName.toLowerCase();if(void 0===r&&(r=!0),r&&(!a||t.disabled||"reset"==n||"button"==n||("checkbox"==n||"radio"==n)&&!t.checked||("submit"==n||"image"==n)&&t.form&&t.form.clk!=t||"select"==i&&-1==t.selectedIndex))return null;if("select"==i){var o=t.selectedIndex;if(0>o)return null;for(var s=[],u=t.options,c="select-one"==n,l=c?o+1:u.length,f=c?o:0;l>f;f++){var m=u[f];if(m.selected){var d=m.value;if(d||(d=m.attributes&&m.attributes.value&&!m.attributes.value.specified?m.text:m.value),c)return d;s.push(d)}}return s}return e(t).val()},e.fn.clearForm=function(t){return this.each(function(){e("input,select,textarea",this).clearFields(t)})},e.fn.clearFields=e.fn.clearInputs=function(t){var r=/^(?:color|date|datetime|email|month|number|password|range|search|tel|text|time|url|week)$/i;return this.each(function(){var a=this.type,n=this.tagName.toLowerCase();r.test(a)||"textarea"==n?this.value="":"checkbox"==a||"radio"==a?this.checked=!1:"select"==n?this.selectedIndex=-1:"file"==a?/MSIE/.test(navigator.userAgent)?e(this).replaceWith(e(this).clone(!0)):e(this).val(""):t&&(t===!0&&/hidden/.test(a)||"string"==typeof t&&e(this).is(t))&&(this.value="")})},e.fn.resetForm=function(){return this.each(function(){("function"==typeof this.reset||"object"==typeof this.reset&&!this.reset.nodeType)&&this.reset()})},e.fn.enable=function(e){return void 0===e&&(e=!0),this.each(function(){this.disabled=!e})},e.fn.selected=function(t){return void 0===t&&(t=!0),this.each(function(){var r=this.type;if("checkbox"==r||"radio"==r)this.checked=t;else if("option"==this.tagName.toLowerCase()){var a=e(this).parent("select");t&&a[0]&&"select-one"==a[0].type&&a.find("option").selected(!1),this.selected=t}})},e.fn.ajaxSubmit.debug=!1});

/*! Magnific Popup - v1.1.0 - 2016-02-20
* http://dimsemenov.com/plugins/magnific-popup/
* Copyright (c) 2016 Dmitry Semenov; */
!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):a("object"==typeof exports?require("jquery"):window.jQuery||window.Zepto)}(function(a){var b,c,d,e,f,g,h="Close",i="BeforeClose",j="AfterClose",k="BeforeAppend",l="MarkupParse",m="Open",n="Change",o="mfp",p="."+o,q="mfp-ready",r="mfp-removing",s="mfp-prevent-close",t=function(){},u=!!window.jQuery,v=a(window),w=function(a,c){b.ev.on(o+a+p,c)},x=function(b,c,d,e){var f=document.createElement("div");return f.className="mfp-"+b,d&&(f.innerHTML=d),e?c&&c.appendChild(f):(f=a(f),c&&f.appendTo(c)),f},y=function(c,d){b.ev.triggerHandler(o+c,d),b.st.callbacks&&(c=c.charAt(0).toLowerCase()+c.slice(1),b.st.callbacks[c]&&b.st.callbacks[c].apply(b,a.isArray(d)?d:[d]))},z=function(c){return c===g&&b.currTemplate.closeBtn||(b.currTemplate.closeBtn=a(b.st.closeMarkup.replace("%title%",b.st.tClose)),g=c),b.currTemplate.closeBtn},A=function(){a.magnificPopup.instance||(b=new t,b.init(),a.magnificPopup.instance=b)},B=function(){var a=document.createElement("p").style,b=["ms","O","Moz","Webkit"];if(void 0!==a.transition)return!0;for(;b.length;)if(b.pop()+"Transition"in a)return!0;return!1};t.prototype={constructor:t,init:function(){var c=navigator.appVersion;b.isLowIE=b.isIE8=document.all&&!document.addEventListener,b.isAndroid=/android/gi.test(c),b.isIOS=/iphone|ipad|ipod/gi.test(c),b.supportsTransition=B(),b.probablyMobile=b.isAndroid||b.isIOS||/(Opera Mini)|Kindle|webOS|BlackBerry|(Opera Mobi)|(Windows Phone)|IEMobile/i.test(navigator.userAgent),d=a(document),b.popupsCache={}},open:function(c){var e;if(c.isObj===!1){b.items=c.items.toArray(),b.index=0;var g,h=c.items;for(e=0;e<h.length;e++)if(g=h[e],g.parsed&&(g=g.el[0]),g===c.el[0]){b.index=e;break}}else b.items=a.isArray(c.items)?c.items:[c.items],b.index=c.index||0;if(b.isOpen)return void b.updateItemHTML();b.types=[],f="",c.mainEl&&c.mainEl.length?b.ev=c.mainEl.eq(0):b.ev=d,c.key?(b.popupsCache[c.key]||(b.popupsCache[c.key]={}),b.currTemplate=b.popupsCache[c.key]):b.currTemplate={},b.st=a.extend(!0,{},a.magnificPopup.defaults,c),b.fixedContentPos="auto"===b.st.fixedContentPos?!b.probablyMobile:b.st.fixedContentPos,b.st.modal&&(b.st.closeOnContentClick=!1,b.st.closeOnBgClick=!1,b.st.showCloseBtn=!1,b.st.enableEscapeKey=!1),b.bgOverlay||(b.bgOverlay=x("bg").on("click"+p,function(){b.close()}),b.wrap=x("wrap").attr("tabindex",-1).on("click"+p,function(a){b._checkIfClose(a.target)&&b.close()}),b.container=x("container",b.wrap)),b.contentContainer=x("content"),b.st.preloader&&(b.preloader=x("preloader",b.container,b.st.tLoading));var i=a.magnificPopup.modules;for(e=0;e<i.length;e++){var j=i[e];j=j.charAt(0).toUpperCase()+j.slice(1),b["init"+j].call(b)}y("BeforeOpen"),b.st.showCloseBtn&&(b.st.closeBtnInside?(w(l,function(a,b,c,d){c.close_replaceWith=z(d.type)}),f+=" mfp-close-btn-in"):b.wrap.append(z())),b.st.alignTop&&(f+=" mfp-align-top"),b.fixedContentPos?b.wrap.css({overflow:b.st.overflowY,overflowX:"hidden",overflowY:b.st.overflowY}):b.wrap.css({top:v.scrollTop(),position:"absolute"}),(b.st.fixedBgPos===!1||"auto"===b.st.fixedBgPos&&!b.fixedContentPos)&&b.bgOverlay.css({height:d.height(),position:"absolute"}),b.st.enableEscapeKey&&d.on("keyup"+p,function(a){27===a.keyCode&&b.close()}),v.on("resize"+p,function(){b.updateSize()}),b.st.closeOnContentClick||(f+=" mfp-auto-cursor"),f&&b.wrap.addClass(f);var k=b.wH=v.height(),n={};if(b.fixedContentPos&&b._hasScrollBar(k)){var o=b._getScrollbarSize();o&&(n.marginRight=o)}b.fixedContentPos&&(b.isIE7?a("body, html").css("overflow","hidden"):n.overflow="hidden");var r=b.st.mainClass;return b.isIE7&&(r+=" mfp-ie7"),r&&b._addClassToMFP(r),b.updateItemHTML(),y("BuildControls"),a("html").css(n),b.bgOverlay.add(b.wrap).prependTo(b.st.prependTo||a(document.body)),b._lastFocusedEl=document.activeElement,setTimeout(function(){b.content?(b._addClassToMFP(q),b._setFocus()):b.bgOverlay.addClass(q),d.on("focusin"+p,b._onFocusIn)},16),b.isOpen=!0,b.updateSize(k),y(m),c},close:function(){b.isOpen&&(y(i),b.isOpen=!1,b.st.removalDelay&&!b.isLowIE&&b.supportsTransition?(b._addClassToMFP(r),setTimeout(function(){b._close()},b.st.removalDelay)):b._close())},_close:function(){y(h);var c=r+" "+q+" ";if(b.bgOverlay.detach(),b.wrap.detach(),b.container.empty(),b.st.mainClass&&(c+=b.st.mainClass+" "),b._removeClassFromMFP(c),b.fixedContentPos){var e={marginRight:""};b.isIE7?a("body, html").css("overflow",""):e.overflow="",a("html").css(e)}d.off("keyup"+p+" focusin"+p),b.ev.off(p),b.wrap.attr("class","mfp-wrap").removeAttr("style"),b.bgOverlay.attr("class","mfp-bg"),b.container.attr("class","mfp-container"),!b.st.showCloseBtn||b.st.closeBtnInside&&b.currTemplate[b.currItem.type]!==!0||b.currTemplate.closeBtn&&b.currTemplate.closeBtn.detach(),b.st.autoFocusLast&&b._lastFocusedEl&&a(b._lastFocusedEl).focus(),b.currItem=null,b.content=null,b.currTemplate=null,b.prevHeight=0,y(j)},updateSize:function(a){if(b.isIOS){var c=document.documentElement.clientWidth/window.innerWidth,d=window.innerHeight*c;b.wrap.css("height",d),b.wH=d}else b.wH=a||v.height();b.fixedContentPos||b.wrap.css("height",b.wH),y("Resize")},updateItemHTML:function(){var c=b.items[b.index];b.contentContainer.detach(),b.content&&b.content.detach(),c.parsed||(c=b.parseEl(b.index));var d=c.type;if(y("BeforeChange",[b.currItem?b.currItem.type:"",d]),b.currItem=c,!b.currTemplate[d]){var f=b.st[d]?b.st[d].markup:!1;y("FirstMarkupParse",f),f?b.currTemplate[d]=a(f):b.currTemplate[d]=!0}e&&e!==c.type&&b.container.removeClass("mfp-"+e+"-holder");var g=b["get"+d.charAt(0).toUpperCase()+d.slice(1)](c,b.currTemplate[d]);b.appendContent(g,d),c.preloaded=!0,y(n,c),e=c.type,b.container.prepend(b.contentContainer),y("AfterChange")},appendContent:function(a,c){b.content=a,a?b.st.showCloseBtn&&b.st.closeBtnInside&&b.currTemplate[c]===!0?b.content.find(".mfp-close").length||b.content.append(z()):b.content=a:b.content="",y(k),b.container.addClass("mfp-"+c+"-holder"),b.contentContainer.append(b.content)},parseEl:function(c){var d,e=b.items[c];if(e.tagName?e={el:a(e)}:(d=e.type,e={data:e,src:e.src}),e.el){for(var f=b.types,g=0;g<f.length;g++)if(e.el.hasClass("mfp-"+f[g])){d=f[g];break}e.src=e.el.attr("data-mfp-src"),e.src||(e.src=e.el.attr("href"))}return e.type=d||b.st.type||"inline",e.index=c,e.parsed=!0,b.items[c]=e,y("ElementParse",e),b.items[c]},addGroup:function(a,c){var d=function(d){d.mfpEl=this,b._openClick(d,a,c)};c||(c={});var e="click.magnificPopup";c.mainEl=a,c.items?(c.isObj=!0,a.off(e).on(e,d)):(c.isObj=!1,c.delegate?a.off(e).on(e,c.delegate,d):(c.items=a,a.off(e).on(e,d)))},_openClick:function(c,d,e){var f=void 0!==e.midClick?e.midClick:a.magnificPopup.defaults.midClick;if(f||!(2===c.which||c.ctrlKey||c.metaKey||c.altKey||c.shiftKey)){var g=void 0!==e.disableOn?e.disableOn:a.magnificPopup.defaults.disableOn;if(g)if(a.isFunction(g)){if(!g.call(b))return!0}else if(v.width()<g)return!0;c.type&&(c.preventDefault(),b.isOpen&&c.stopPropagation()),e.el=a(c.mfpEl),e.delegate&&(e.items=d.find(e.delegate)),b.open(e)}},updateStatus:function(a,d){if(b.preloader){c!==a&&b.container.removeClass("mfp-s-"+c),d||"loading"!==a||(d=b.st.tLoading);var e={status:a,text:d};y("UpdateStatus",e),a=e.status,d=e.text,b.preloader.html(d),b.preloader.find("a").on("click",function(a){a.stopImmediatePropagation()}),b.container.addClass("mfp-s-"+a),c=a}},_checkIfClose:function(c){if(!a(c).hasClass(s)){var d=b.st.closeOnContentClick,e=b.st.closeOnBgClick;if(d&&e)return!0;if(!b.content||a(c).hasClass("mfp-close")||b.preloader&&c===b.preloader[0])return!0;if(c===b.content[0]||a.contains(b.content[0],c)){if(d)return!0}else if(e&&a.contains(document,c))return!0;return!1}},_addClassToMFP:function(a){b.bgOverlay.addClass(a),b.wrap.addClass(a)},_removeClassFromMFP:function(a){this.bgOverlay.removeClass(a),b.wrap.removeClass(a)},_hasScrollBar:function(a){return(b.isIE7?d.height():document.body.scrollHeight)>(a||v.height())},_setFocus:function(){(b.st.focus?b.content.find(b.st.focus).eq(0):b.wrap).focus()},_onFocusIn:function(c){return c.target===b.wrap[0]||a.contains(b.wrap[0],c.target)?void 0:(b._setFocus(),!1)},_parseMarkup:function(b,c,d){var e;d.data&&(c=a.extend(d.data,c)),y(l,[b,c,d]),a.each(c,function(c,d){if(void 0===d||d===!1)return!0;if(e=c.split("_"),e.length>1){var f=b.find(p+"-"+e[0]);if(f.length>0){var g=e[1];"replaceWith"===g?f[0]!==d[0]&&f.replaceWith(d):"img"===g?f.is("img")?f.attr("src",d):f.replaceWith(a("<img>").attr("src",d).attr("class",f.attr("class"))):f.attr(e[1],d)}}else b.find(p+"-"+c).html(d)})},_getScrollbarSize:function(){if(void 0===b.scrollbarSize){var a=document.createElement("div");a.style.cssText="width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;",document.body.appendChild(a),b.scrollbarSize=a.offsetWidth-a.clientWidth,document.body.removeChild(a)}return b.scrollbarSize}},a.magnificPopup={instance:null,proto:t.prototype,modules:[],open:function(b,c){return A(),b=b?a.extend(!0,{},b):{},b.isObj=!0,b.index=c||0,this.instance.open(b)},close:function(){return a.magnificPopup.instance&&a.magnificPopup.instance.close()},registerModule:function(b,c){c.options&&(a.magnificPopup.defaults[b]=c.options),a.extend(this.proto,c.proto),this.modules.push(b)},defaults:{disableOn:0,key:null,midClick:!1,mainClass:"",preloader:!0,focus:"",closeOnContentClick:!1,closeOnBgClick:!0,closeBtnInside:!0,showCloseBtn:!0,enableEscapeKey:!0,modal:!1,alignTop:!1,removalDelay:0,prependTo:null,fixedContentPos:"auto",fixedBgPos:"auto",overflowY:"auto",closeMarkup:'<button title="%title%" type="button" class="mfp-close">&#215;</button>',tClose:"Close (Esc)",tLoading:"Loading...",autoFocusLast:!0}},a.fn.magnificPopup=function(c){A();var d=a(this);if("string"==typeof c)if("open"===c){var e,f=u?d.data("magnificPopup"):d[0].magnificPopup,g=parseInt(arguments[1],10)||0;f.items?e=f.items[g]:(e=d,f.delegate&&(e=e.find(f.delegate)),e=e.eq(g)),b._openClick({mfpEl:e},d,f)}else b.isOpen&&b[c].apply(b,Array.prototype.slice.call(arguments,1));else c=a.extend(!0,{},c),u?d.data("magnificPopup",c):d[0].magnificPopup=c,b.addGroup(d,c);return d};var C,D,E,F="inline",G=function(){E&&(D.after(E.addClass(C)).detach(),E=null)};a.magnificPopup.registerModule(F,{options:{hiddenClass:"hide",markup:"",tNotFound:"Content not found"},proto:{initInline:function(){b.types.push(F),w(h+"."+F,function(){G()})},getInline:function(c,d){if(G(),c.src){var e=b.st.inline,f=a(c.src);if(f.length){var g=f[0].parentNode;g&&g.tagName&&(D||(C=e.hiddenClass,D=x(C),C="mfp-"+C),E=f.after(D).detach().removeClass(C)),b.updateStatus("ready")}else b.updateStatus("error",e.tNotFound),f=a("<div>");return c.inlineElement=f,f}return b.updateStatus("ready"),b._parseMarkup(d,{},c),d}}});var H,I="ajax",J=function(){H&&a(document.body).removeClass(H)},K=function(){J(),b.req&&b.req.abort()};a.magnificPopup.registerModule(I,{options:{settings:null,cursor:"mfp-ajax-cur",tError:'<a href="%url%">The content</a> could not be loaded.'},proto:{initAjax:function(){b.types.push(I),H=b.st.ajax.cursor,w(h+"."+I,K),w("BeforeChange."+I,K)},getAjax:function(c){H&&a(document.body).addClass(H),b.updateStatus("loading");var d=a.extend({url:c.src,success:function(d,e,f){var g={data:d,xhr:f};y("ParseAjax",g),b.appendContent(a(g.data),I),c.finished=!0,J(),b._setFocus(),setTimeout(function(){b.wrap.addClass(q)},16),b.updateStatus("ready"),y("AjaxContentAdded")},error:function(){J(),c.finished=c.loadError=!0,b.updateStatus("error",b.st.ajax.tError.replace("%url%",c.src))}},b.st.ajax.settings);return b.req=a.ajax(d),""}}});var L,M=function(c){if(c.data&&void 0!==c.data.title)return c.data.title;var d=b.st.image.titleSrc;if(d){if(a.isFunction(d))return d.call(b,c);if(c.el)return c.el.attr(d)||""}return""};a.magnificPopup.registerModule("image",{options:{markup:'<div class="mfp-figure"><div class="mfp-close"></div><figure><div class="mfp-img"></div><figcaption><div class="mfp-bottom-bar"><div class="mfp-title"></div><div class="mfp-counter"></div></div></figcaption></figure></div>',cursor:"mfp-zoom-out-cur",titleSrc:"title",verticalFit:!0,tError:'<a href="%url%">The image</a> could not be loaded.'},proto:{initImage:function(){var c=b.st.image,d=".image";b.types.push("image"),w(m+d,function(){"image"===b.currItem.type&&c.cursor&&a(document.body).addClass(c.cursor)}),w(h+d,function(){c.cursor&&a(document.body).removeClass(c.cursor),v.off("resize"+p)}),w("Resize"+d,b.resizeImage),b.isLowIE&&w("AfterChange",b.resizeImage)},resizeImage:function(){var a=b.currItem;if(a&&a.img&&b.st.image.verticalFit){var c=0;b.isLowIE&&(c=parseInt(a.img.css("padding-top"),10)+parseInt(a.img.css("padding-bottom"),10)),a.img.css("max-height",b.wH-c)}},_onImageHasSize:function(a){a.img&&(a.hasSize=!0,L&&clearInterval(L),a.isCheckingImgSize=!1,y("ImageHasSize",a),a.imgHidden&&(b.content&&b.content.removeClass("mfp-loading"),a.imgHidden=!1))},findImageSize:function(a){var c=0,d=a.img[0],e=function(f){L&&clearInterval(L),L=setInterval(function(){return d.naturalWidth>0?void b._onImageHasSize(a):(c>200&&clearInterval(L),c++,void(3===c?e(10):40===c?e(50):100===c&&e(500)))},f)};e(1)},getImage:function(c,d){var e=0,f=function(){c&&(c.img[0].complete?(c.img.off(".mfploader"),c===b.currItem&&(b._onImageHasSize(c),b.updateStatus("ready")),c.hasSize=!0,c.loaded=!0,y("ImageLoadComplete")):(e++,200>e?setTimeout(f,100):g()))},g=function(){c&&(c.img.off(".mfploader"),c===b.currItem&&(b._onImageHasSize(c),b.updateStatus("error",h.tError.replace("%url%",c.src))),c.hasSize=!0,c.loaded=!0,c.loadError=!0)},h=b.st.image,i=d.find(".mfp-img");if(i.length){var j=document.createElement("img");j.className="mfp-img",c.el&&c.el.find("img").length&&(j.alt=c.el.find("img").attr("alt")),c.img=a(j).on("load.mfploader",f).on("error.mfploader",g),j.src=c.src,i.is("img")&&(c.img=c.img.clone()),j=c.img[0],j.naturalWidth>0?c.hasSize=!0:j.width||(c.hasSize=!1)}return b._parseMarkup(d,{title:M(c),img_replaceWith:c.img},c),b.resizeImage(),c.hasSize?(L&&clearInterval(L),c.loadError?(d.addClass("mfp-loading"),b.updateStatus("error",h.tError.replace("%url%",c.src))):(d.removeClass("mfp-loading"),b.updateStatus("ready")),d):(b.updateStatus("loading"),c.loading=!0,c.hasSize||(c.imgHidden=!0,d.addClass("mfp-loading"),b.findImageSize(c)),d)}}});var N,O=function(){return void 0===N&&(N=void 0!==document.createElement("p").style.MozTransform),N};a.magnificPopup.registerModule("zoom",{options:{enabled:!1,easing:"ease-in-out",duration:300,opener:function(a){return a.is("img")?a:a.find("img")}},proto:{initZoom:function(){var a,c=b.st.zoom,d=".zoom";if(c.enabled&&b.supportsTransition){var e,f,g=c.duration,j=function(a){var b=a.clone().removeAttr("style").removeAttr("class").addClass("mfp-animated-image"),d="all "+c.duration/1e3+"s "+c.easing,e={position:"fixed",zIndex:9999,left:0,top:0,"-webkit-backface-visibility":"hidden"},f="transition";return e["-webkit-"+f]=e["-moz-"+f]=e["-o-"+f]=e[f]=d,b.css(e),b},k=function(){b.content.css("visibility","visible")};w("BuildControls"+d,function(){if(b._allowZoom()){if(clearTimeout(e),b.content.css("visibility","hidden"),a=b._getItemToZoom(),!a)return void k();f=j(a),f.css(b._getOffset()),b.wrap.append(f),e=setTimeout(function(){f.css(b._getOffset(!0)),e=setTimeout(function(){k(),setTimeout(function(){f.remove(),a=f=null,y("ZoomAnimationEnded")},16)},g)},16)}}),w(i+d,function(){if(b._allowZoom()){if(clearTimeout(e),b.st.removalDelay=g,!a){if(a=b._getItemToZoom(),!a)return;f=j(a)}f.css(b._getOffset(!0)),b.wrap.append(f),b.content.css("visibility","hidden"),setTimeout(function(){f.css(b._getOffset())},16)}}),w(h+d,function(){b._allowZoom()&&(k(),f&&f.remove(),a=null)})}},_allowZoom:function(){return"image"===b.currItem.type},_getItemToZoom:function(){return b.currItem.hasSize?b.currItem.img:!1},_getOffset:function(c){var d;d=c?b.currItem.img:b.st.zoom.opener(b.currItem.el||b.currItem);var e=d.offset(),f=parseInt(d.css("padding-top"),10),g=parseInt(d.css("padding-bottom"),10);e.top-=a(window).scrollTop()-f;var h={width:d.width(),height:(u?d.innerHeight():d[0].offsetHeight)-g-f};return O()?h["-moz-transform"]=h.transform="translate("+e.left+"px,"+e.top+"px)":(h.left=e.left,h.top=e.top),h}}});var P="iframe",Q="//about:blank",R=function(a){if(b.currTemplate[P]){var c=b.currTemplate[P].find("iframe");c.length&&(a||(c[0].src=Q),b.isIE8&&c.css("display",a?"block":"none"))}};a.magnificPopup.registerModule(P,{options:{markup:'<div class="mfp-iframe-scaler"><div class="mfp-close"></div><iframe class="mfp-iframe" src="//about:blank" frameborder="0" allowfullscreen></iframe></div>',srcAction:"iframe_src",patterns:{youtube:{index:"youtube.com",id:"v=",src:"//www.youtube.com/embed/%id%?autoplay=1"},vimeo:{index:"vimeo.com/",id:"/",src:"//player.vimeo.com/video/%id%?autoplay=1"},gmaps:{index:"//maps.google.",src:"%id%&output=embed"}}},proto:{initIframe:function(){b.types.push(P),w("BeforeChange",function(a,b,c){b!==c&&(b===P?R():c===P&&R(!0))}),w(h+"."+P,function(){R()})},getIframe:function(c,d){var e=c.src,f=b.st.iframe;a.each(f.patterns,function(){return e.indexOf(this.index)>-1?(this.id&&(e="string"==typeof this.id?e.substr(e.lastIndexOf(this.id)+this.id.length,e.length):this.id.call(this,e)),e=this.src.replace("%id%",e),!1):void 0});var g={};return f.srcAction&&(g[f.srcAction]=e),b._parseMarkup(d,g,c),b.updateStatus("ready"),d}}});var S=function(a){var c=b.items.length;return a>c-1?a-c:0>a?c+a:a},T=function(a,b,c){return a.replace(/%curr%/gi,b+1).replace(/%total%/gi,c)};a.magnificPopup.registerModule("gallery",{options:{enabled:!1,arrowMarkup:'<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',preload:[0,2],navigateByImgClick:!0,arrows:!0,tPrev:"Previous (Left arrow key)",tNext:"Next (Right arrow key)",tCounter:"%curr% of %total%"},proto:{initGallery:function(){var c=b.st.gallery,e=".mfp-gallery";return b.direction=!0,c&&c.enabled?(f+=" mfp-gallery",w(m+e,function(){c.navigateByImgClick&&b.wrap.on("click"+e,".mfp-img",function(){return b.items.length>1?(b.next(),!1):void 0}),d.on("keydown"+e,function(a){37===a.keyCode?b.prev():39===a.keyCode&&b.next()})}),w("UpdateStatus"+e,function(a,c){c.text&&(c.text=T(c.text,b.currItem.index,b.items.length))}),w(l+e,function(a,d,e,f){var g=b.items.length;e.counter=g>1?T(c.tCounter,f.index,g):""}),w("BuildControls"+e,function(){if(b.items.length>1&&c.arrows&&!b.arrowLeft){var d=c.arrowMarkup,e=b.arrowLeft=a(d.replace(/%title%/gi,c.tPrev).replace(/%dir%/gi,"left")).addClass(s),f=b.arrowRight=a(d.replace(/%title%/gi,c.tNext).replace(/%dir%/gi,"right")).addClass(s);e.click(function(){b.prev()}),f.click(function(){b.next()}),b.container.append(e.add(f))}}),w(n+e,function(){b._preloadTimeout&&clearTimeout(b._preloadTimeout),b._preloadTimeout=setTimeout(function(){b.preloadNearbyImages(),b._preloadTimeout=null},16)}),void w(h+e,function(){d.off(e),b.wrap.off("click"+e),b.arrowRight=b.arrowLeft=null})):!1},next:function(){b.direction=!0,b.index=S(b.index+1),b.updateItemHTML()},prev:function(){b.direction=!1,b.index=S(b.index-1),b.updateItemHTML()},goTo:function(a){b.direction=a>=b.index,b.index=a,b.updateItemHTML()},preloadNearbyImages:function(){var a,c=b.st.gallery.preload,d=Math.min(c[0],b.items.length),e=Math.min(c[1],b.items.length);for(a=1;a<=(b.direction?e:d);a++)b._preloadItem(b.index+a);for(a=1;a<=(b.direction?d:e);a++)b._preloadItem(b.index-a)},_preloadItem:function(c){if(c=S(c),!b.items[c].preloaded){var d=b.items[c];d.parsed||(d=b.parseEl(c)),y("LazyLoad",d),"image"===d.type&&(d.img=a('<img class="mfp-img" />').on("load.mfploader",function(){d.hasSize=!0}).on("error.mfploader",function(){d.hasSize=!0,d.loadError=!0,y("LazyLoadError",d)}).attr("src",d.src)),d.preloaded=!0}}}});var U="retina";a.magnificPopup.registerModule(U,{options:{replaceSrc:function(a){return a.src.replace(/\.\w+$/,function(a){return"@2x"+a})},ratio:1},proto:{initRetina:function(){if(window.devicePixelRatio>1){var a=b.st.retina,c=a.ratio;c=isNaN(c)?c():c,c>1&&(w("ImageHasSize."+U,function(a,b){b.img.css({"max-width":b.img[0].naturalWidth/c,width:"100%"})}),w("ElementParse."+U,function(b,d){d.src=a.replaceSrc(d,c)}))}}}}),A()});

// jQuery Paginate v0.4
;(function($){$.fn.pajinate=function(options){var current_page="current_page";var items_per_page="items_per_page";var meta;var defaults={item_container_id:".content",items_per_page:10,nav_panel_id:".page_navigation",nav_info_id:".info_text",num_page_links_to_display:20,start_page:0,wrap_around:false,nav_label_first:"First",nav_label_prev:"Prev",nav_label_next:"Next",nav_label_last:"Last",nav_order:["first","prev","num","next","last"],nav_label_info:"Showing {0}-{1} of {2} results",show_first_last:true,abort_on_small_lists:false,jquery_ui:false,jquery_ui_active:"ui-state-highlight",jquery_ui_default:"ui-state-default",jquery_ui_disabled:"ui-state-disabled"};var options=$.extend(defaults,options);var $item_container;var $page_container;var $items;var $nav_panels;var total_page_no_links;var jquery_ui_default_class=options.jquery_ui?options.jquery_ui_default:"";var jquery_ui_active_class=options.jquery_ui?options.jquery_ui_active:"";var jquery_ui_disabled_class=options.jquery_ui?options.jquery_ui_disabled:"";return this.each(function(){$page_container=$(this);$item_container=$(this).find(options.item_container_id);$items=$page_container.find(options.item_container_id).children();if(options.abort_on_small_lists&&options.items_per_page>=$items.size()){return $page_container}meta=$page_container;meta.data(current_page,0);meta.data(items_per_page,options.items_per_page);var total_items=$item_container.children().length;var number_of_pages=Math.ceil(total_items/options.items_per_page);var more='<li class="page-item disabled ellipse more"><a class="page-link" href="#">...</a></li>';var less='<li class="page-item disabled ellipse less"><a class="page-link" href="#">...</a></li>';var first=!options.show_first_last?"":'<li class="page-item first_link '+jquery_ui_default_class+'"><a class="page-link" href="#">'+options.nav_label_first+"</a></li>";var last=!options.show_first_last?"":'<li class="page-item last_link '+jquery_ui_default_class+'"><a class="page-link" href="#">'+options.nav_label_last+"</a></li>";var navigation_html="";for(var i=0;i<options.nav_order.length;i++){switch(options.nav_order[i]){case"first":navigation_html+=first;break;case"last":navigation_html+=last;break;case"next":navigation_html+='<li class="page-item next_link '+jquery_ui_default_class+'"><a class="page-link" href="#">'+options.nav_label_next+"</a></li>";break;case"prev":navigation_html+='<li class="page-item previous_link '+jquery_ui_default_class+'"><a class="page-link" href="#">'+options.nav_label_prev+"</a></li>";break;case"num":navigation_html+=less;var current_link=0;while(number_of_pages>current_link){navigation_html+='<li class="page-item page_link '+jquery_ui_default_class+'" longdesc="'+current_link+'"><a class="page-link" href="#">'+(current_link+1)+"</a></li>";current_link++}navigation_html+=more;break;default:break}}$nav_panels=$page_container.find(options.nav_panel_id);$nav_panels.html(navigation_html).each(function(){$(this).find(".page_link:first").addClass("first");$(this).find(".page_link:last").addClass("last")});$nav_panels.children(".ellipse").hide();$nav_panels.find(".previous_link").next().next().addClass("active "+jquery_ui_active_class);$items.hide();$items.slice(0,meta.data(items_per_page)).show();total_page_no_links=$page_container.find(options.nav_panel_id+":first").children(".page_link").length;options.num_page_links_to_display=Math.min(options.num_page_links_to_display,total_page_no_links);$nav_panels.children(".page_link").hide();$nav_panels.each(function(){$(this).children(".page_link").slice(0,options.num_page_links_to_display).show()});$page_container.find(".first_link").click(function(e){e.preventDefault();movePageNumbersRight($(this),0);gotopage(0)});$page_container.find(".last_link").click(function(e){e.preventDefault();var lastPage=total_page_no_links-1;movePageNumbersLeft($(this),lastPage);gotopage(lastPage)});$page_container.find(".previous_link").click(function(e){e.preventDefault();showPrevPage($(this))});$page_container.find(".next_link").click(function(e){e.preventDefault();showNextPage($(this))});$page_container.find(".page_link").click(function(e){e.preventDefault();gotopage($(this).attr("longdesc"))});gotopage(parseInt(options.start_page));toggleMoreLess();if(!options.wrap_around){tagNextPrev()}});function showPrevPage(e){new_page=parseInt(meta.data(current_page))-1;if($(e).siblings(".active").prev(".page_link").length==true){movePageNumbersRight(e,new_page);gotopage(new_page)}else{if(options.wrap_around){gotopage(total_page_no_links-1)}}}function showNextPage(e){new_page=parseInt(meta.data(current_page))+1;if($(e).siblings(".active").next(".page_link").length==true){movePageNumbersLeft(e,new_page);gotopage(new_page)}else{if(options.wrap_around){gotopage(0)}}}function gotopage(page_num){page_num=parseInt(page_num,10);var ipp=parseInt(meta.data(items_per_page));start_from=page_num*ipp;end_on=start_from+ipp;var items=$items.hide().slice(start_from,end_on);items.fadeIn(700);$page_container.find(options.nav_panel_id).children(".page_link[longdesc="+page_num+"]").addClass("active "+jquery_ui_active_class).siblings(".active").removeClass("active "+jquery_ui_active_class);meta.data(current_page,page_num);var $current_page=parseInt(meta.data(current_page)+1);var total_items=$item_container.children().length;var $number_of_pages=Math.ceil(total_items/options.items_per_page);$page_container.find(options.nav_info_id).html(options.nav_label_info.replace("{0}",start_from+1).replace("{1}",start_from+items.length).replace("{2}",$items.length).replace("{3}",$current_page).replace("{4}",$number_of_pages));toggleMoreLess();tagNextPrev();if(typeof(options.onPageDisplayed)!=="undefined"){options.onPageDisplayed.call(this,page_num+1)}}function movePageNumbersLeft(e,new_p){var new_page=new_p;var $current_active_link=$(e).siblings(".active");if($current_active_link.siblings(".page_link[longdesc="+new_page+"]").css("display")=="none"){$nav_panels.each(function(){$(this).children(".page_link").hide().slice(parseInt(new_page-options.num_page_links_to_display+1),new_page+1).show()})}}function movePageNumbersRight(e,new_p){var new_page=new_p;var $current_active_link=$(e).siblings(".active");if($current_active_link.siblings(".page_link[longdesc="+new_page+"]").css("display")=="none"){$nav_panels.each(function(){$(this).children(".page_link").hide().slice(new_page,new_page+parseInt(options.num_page_links_to_display)).show()})}}function toggleMoreLess(){}function tagNextPrev(){if($nav_panels.children(".last").hasClass("active")){$nav_panels.children(".next_link").add(".last_link").addClass("no_more "+jquery_ui_disabled_class)}else{$nav_panels.children(".next_link").add(".last_link").removeClass("no_more "+jquery_ui_disabled_class)}if($nav_panels.children(".first").hasClass("active")){$nav_panels.children(".previous_link").add(".first_link").addClass("no_more "+jquery_ui_disabled_class)}else{$nav_panels.children(".previous_link").add(".first_link").removeClass("no_more "+jquery_ui_disabled_class)}}}})(jQuery);

/*!
 * Infinite Scroll PACKAGED v3.0.5
 * Automatically add next page
 *
 * Licensed GPLv3 for open source use
 * or Infinite Scroll Commercial License for commercial use
 *
 * https://infinite-scroll.com
 * Copyright 2018 Metafizzy
 */

!function(t,e){"function"==typeof define&&define.amd?define("jquery-bridget/jquery-bridget",["jquery"],function(i){return e(t,i)}):"object"==typeof module&&module.exports?module.exports=e(t,require("jquery")):t.jQueryBridget=e(t,t.jQuery)}(window,function(t,e){"use strict";function i(i,r,l){function a(t,e,n){var o,r="$()."+i+'("'+e+'")';return t.each(function(t,a){var h=l.data(a,i);if(!h)return void s(i+" not initialized. Cannot call methods, i.e. "+r);var c=h[e];if(!c||"_"==e.charAt(0))return void s(r+" is not a valid method");var u=c.apply(h,n);o=void 0===o?u:o}),void 0!==o?o:t}function h(t,e){t.each(function(t,n){var o=l.data(n,i);o?(o.option(e),o._init()):(o=new r(n,e),l.data(n,i,o))})}l=l||e||t.jQuery,l&&(r.prototype.option||(r.prototype.option=function(t){l.isPlainObject(t)&&(this.options=l.extend(!0,this.options,t))}),l.fn[i]=function(t){if("string"==typeof t){var e=o.call(arguments,1);return a(this,t,e)}return h(this,t),this},n(l))}function n(t){!t||t&&t.bridget||(t.bridget=i)}var o=Array.prototype.slice,r=t.console,s="undefined"==typeof r?function(){}:function(t){r.error(t)};return n(e||t.jQuery),i}),function(t,e){"function"==typeof define&&define.amd?define("ev-emitter/ev-emitter",e):"object"==typeof module&&module.exports?module.exports=e():t.EvEmitter=e()}("undefined"!=typeof window?window:this,function(){function t(){}var e=t.prototype;return e.on=function(t,e){if(t&&e){var i=this._events=this._events||{},n=i[t]=i[t]||[];return n.indexOf(e)==-1&&n.push(e),this}},e.once=function(t,e){if(t&&e){this.on(t,e);var i=this._onceEvents=this._onceEvents||{},n=i[t]=i[t]||{};return n[e]=!0,this}},e.off=function(t,e){var i=this._events&&this._events[t];if(i&&i.length){var n=i.indexOf(e);return n!=-1&&i.splice(n,1),this}},e.emitEvent=function(t,e){var i=this._events&&this._events[t];if(i&&i.length){i=i.slice(0),e=e||[];for(var n=this._onceEvents&&this._onceEvents[t],o=0;o<i.length;o++){var r=i[o],s=n&&n[r];s&&(this.off(t,r),delete n[r]),r.apply(this,e)}return this}},e.allOff=function(){delete this._events,delete this._onceEvents},t}),function(t,e){"use strict";"function"==typeof define&&define.amd?define("desandro-matches-selector/matches-selector",e):"object"==typeof module&&module.exports?module.exports=e():t.matchesSelector=e()}(window,function(){"use strict";var t=function(){var t=window.Element.prototype;if(t.matches)return"matches";if(t.matchesSelector)return"matchesSelector";for(var e=["webkit","moz","ms","o"],i=0;i<e.length;i++){var n=e[i],o=n+"MatchesSelector";if(t[o])return o}}();return function(e,i){return e[t](i)}}),function(t,e){"function"==typeof define&&define.amd?define("fizzy-ui-utils/utils",["desandro-matches-selector/matches-selector"],function(i){return e(t,i)}):"object"==typeof module&&module.exports?module.exports=e(t,require("desandro-matches-selector")):t.fizzyUIUtils=e(t,t.matchesSelector)}(window,function(t,e){var i={};i.extend=function(t,e){for(var i in e)t[i]=e[i];return t},i.modulo=function(t,e){return(t%e+e)%e};var n=Array.prototype.slice;i.makeArray=function(t){if(Array.isArray(t))return t;if(null===t||void 0===t)return[];var e="object"==typeof t&&"number"==typeof t.length;return e?n.call(t):[t]},i.removeFrom=function(t,e){var i=t.indexOf(e);i!=-1&&t.splice(i,1)},i.getParent=function(t,i){for(;t.parentNode&&t!=document.body;)if(t=t.parentNode,e(t,i))return t},i.getQueryElement=function(t){return"string"==typeof t?document.querySelector(t):t},i.handleEvent=function(t){var e="on"+t.type;this[e]&&this[e](t)},i.filterFindElements=function(t,n){t=i.makeArray(t);var o=[];return t.forEach(function(t){if(t instanceof HTMLElement){if(!n)return void o.push(t);e(t,n)&&o.push(t);for(var i=t.querySelectorAll(n),r=0;r<i.length;r++)o.push(i[r])}}),o},i.debounceMethod=function(t,e,i){i=i||100;var n=t.prototype[e],o=e+"Timeout";t.prototype[e]=function(){var t=this[o];clearTimeout(t);var e=arguments,r=this;this[o]=setTimeout(function(){n.apply(r,e),delete r[o]},i)}},i.docReady=function(t){var e=document.readyState;"complete"==e||"interactive"==e?setTimeout(t):document.addEventListener("DOMContentLoaded",t)},i.toDashed=function(t){return t.replace(/(.)([A-Z])/g,function(t,e,i){return e+"-"+i}).toLowerCase()};var o=t.console;return i.htmlInit=function(e,n){i.docReady(function(){var r=i.toDashed(n),s="data-"+r,l=document.querySelectorAll("["+s+"]"),a=document.querySelectorAll(".js-"+r),h=i.makeArray(l).concat(i.makeArray(a)),c=s+"-options",u=t.jQuery;h.forEach(function(t){var i,r=t.getAttribute(s)||t.getAttribute(c);try{i=r&&JSON.parse(r)}catch(l){return void(o&&o.error("Error parsing "+s+" on "+t.className+": "+l))}var a=new e(t,i);u&&u.data(t,n,a)})})},i}),function(t,e){"function"==typeof define&&define.amd?define("infinite-scroll/js/core",["ev-emitter/ev-emitter","fizzy-ui-utils/utils"],function(i,n){return e(t,i,n)}):"object"==typeof module&&module.exports?module.exports=e(t,require("ev-emitter"),require("fizzy-ui-utils")):t.InfiniteScroll=e(t,t.EvEmitter,t.fizzyUIUtils)}(window,function(t,e,i){function n(t,e){var s=i.getQueryElement(t);if(!s)return void console.error("Bad element for InfiniteScroll: "+(s||t));if(t=s,t.infiniteScrollGUID){var l=r[t.infiniteScrollGUID];return l.option(e),l}this.element=t,this.options=i.extend({},n.defaults),this.option(e),o&&(this.$element=o(this.element)),this.create()}var o=t.jQuery,r={};n.defaults={},n.create={},n.destroy={};var s=n.prototype;i.extend(s,e.prototype);var l=0;s.create=function(){var t=this.guid=++l;this.element.infiniteScrollGUID=t,r[t]=this,this.pageIndex=1,this.loadCount=0,this.updateGetPath();var e=this.getPath&&this.getPath();if(!e)return void console.error("Disabling InfiniteScroll");this.updateGetAbsolutePath(),this.log("initialized",[this.element.className]),this.callOnInit();for(var i in n.create)n.create[i].call(this)},s.option=function(t){i.extend(this.options,t)},s.callOnInit=function(){var t=this.options.onInit;t&&t.call(this,this)},s.dispatchEvent=function(t,e,i){this.log(t,i);var n=e?[e].concat(i):i;if(this.emitEvent(t,n),o&&this.$element){t+=".infiniteScroll";var r=t;if(e){var s=o.Event(e);s.type=t,r=s}this.$element.trigger(r,i)}};var a={initialized:function(t){return"on "+t},request:function(t){return"URL: "+t},load:function(t,e){return(t.title||"")+". URL: "+e},error:function(t,e){return t+". URL: "+e},append:function(t,e,i){return i.length+" items. URL: "+e},last:function(t,e){return"URL: "+e},history:function(t,e){return"URL: "+e},pageIndex:function(t,e){return"current page determined to be: "+t+" from "+e}};s.log=function(t,e){if(this.options.debug){var i="[InfiniteScroll] "+t,n=a[t];n&&(i+=". "+n.apply(this,e)),console.log(i)}},s.updateMeasurements=function(){this.windowHeight=t.innerHeight;var e=this.element.getBoundingClientRect();this.top=e.top+t.pageYOffset},s.updateScroller=function(){var e=this.options.elementScroll;if(!e)return void(this.scroller=t);if(this.scroller=e===!0?this.element:i.getQueryElement(e),!this.scroller)throw"Unable to find elementScroll: "+e},s.updateGetPath=function(){var t=this.options.path;if(!t)return void console.error("InfiniteScroll path option required. Set as: "+t);var e=typeof t;if("function"==e)return void(this.getPath=t);var i="string"==e&&t.match("{{#}}");return i?void this.updateGetPathTemplate(t):void this.updateGetPathSelector(t)},s.updateGetPathTemplate=function(t){this.getPath=function(){var e=this.pageIndex+1;return t.replace("{{#}}",e)}.bind(this);var e=t.replace("{{#}}","(\\d\\d?\\d?)"),i=new RegExp(e),n=location.href.match(i);n&&(this.pageIndex=parseInt(n[1],10),this.log("pageIndex",[this.pageIndex,"template string"]))};var h=[/^(.*?\/?page\/?)(\d\d?\d?)(.*?$)/,/^(.*?\/?\?page=)(\d\d?\d?)(.*?$)/,/(.*?)(\d\d?\d?)(?!.*\d)(.*?$)/];return s.updateGetPathSelector=function(t){var e=document.querySelector(t);if(!e)return void console.error("Bad InfiniteScroll path option. Next link not found: "+t);for(var i,n,o=e.getAttribute("href"),r=0;o&&r<h.length;r++){n=h[r];var s=o.match(n);if(s){i=s.slice(1);break}}return i?(this.isPathSelector=!0,this.getPath=function(){var t=this.pageIndex+1;return i[0]+t+i[2]}.bind(this),this.pageIndex=parseInt(i[1],10)-1,void this.log("pageIndex",[this.pageIndex,"next link"])):void console.error("InfiniteScroll unable to parse next link href: "+o)},s.updateGetAbsolutePath=function(){var t=this.getPath(),e=t.match(/^http/)||t.match(/^\//);if(e)return void(this.getAbsolutePath=this.getPath);var i=location.pathname,n=i.substring(0,i.lastIndexOf("/"));this.getAbsolutePath=function(){return n+"/"+this.getPath()}},n.create.hideNav=function(){var t=i.getQueryElement(this.options.hideNav);t&&(t.style.display="none",this.nav=t)},n.destroy.hideNav=function(){this.nav&&(this.nav.style.display="")},s.destroy=function(){this.allOff();for(var t in n.destroy)n.destroy[t].call(this);delete this.element.infiniteScrollGUID,delete r[this.guid],o&&this.$element&&o.removeData(this.element,"infiniteScroll")},n.throttle=function(t,e){e=e||200;var i,n;return function(){var o=+new Date,r=arguments,s=function(){i=o,t.apply(this,r)}.bind(this);i&&o<i+e?(clearTimeout(n),n=setTimeout(s,e)):s()}},n.data=function(t){t=i.getQueryElement(t);var e=t&&t.infiniteScrollGUID;return e&&r[e]},n.setJQuery=function(t){o=t},i.htmlInit(n,"infinite-scroll"),s._init=function(){},o&&o.bridget&&o.bridget("infiniteScroll",n),n}),function(t,e){"function"==typeof define&&define.amd?define("infinite-scroll/js/page-load",["./core"],function(i){return e(t,i)}):"object"==typeof module&&module.exports?module.exports=e(t,require("./core")):e(t,t.InfiniteScroll)}(window,function(t,e){function i(t){for(var e=document.createDocumentFragment(),i=0;t&&i<t.length;i++)e.appendChild(t[i]);return e}function n(t){for(var e=t.querySelectorAll("script"),i=0;i<e.length;i++){var n=e[i],r=document.createElement("script");o(n,r),r.innerHTML=n.innerHTML,n.parentNode.replaceChild(r,n)}}function o(t,e){for(var i=t.attributes,n=0;n<i.length;n++){var o=i[n];e.setAttribute(o.name,o.value)}}function r(t,e,i,n){var o=new XMLHttpRequest;o.open("GET",t,!0),o.responseType=e||"",o.setRequestHeader("X-Requested-With","XMLHttpRequest"),o.onload=function(){if(200==o.status)i(o.response);else{var t=new Error(o.statusText);n(t)}},o.onerror=function(){var e=new Error("Network error requesting "+t);n(e)},o.send()}var s=e.prototype;return e.defaults.loadOnScroll=!0,e.defaults.checkLastPage=!0,e.defaults.responseType="document",e.create.pageLoad=function(){this.canLoad=!0,this.on("scrollThreshold",this.onScrollThresholdLoad),this.on("load",this.checkLastPage),this.options.outlayer&&this.on("append",this.onAppendOutlayer)},s.onScrollThresholdLoad=function(){this.options.loadOnScroll&&this.loadNextPage()},s.loadNextPage=function(){if(!this.isLoading&&this.canLoad){var t=this.getAbsolutePath();this.isLoading=!0;var e=function(e){this.onPageLoad(e,t)}.bind(this),i=function(e){this.onPageError(e,t)}.bind(this);r(t,this.options.responseType,e,i),this.dispatchEvent("request",null,[t])}},s.onPageLoad=function(t,e){return this.options.append||(this.isLoading=!1),this.pageIndex++,this.loadCount++,this.dispatchEvent("load",null,[t,e]),this.appendNextPage(t,e),t},s.appendNextPage=function(t,e){var n=this.options.append,o="document"==this.options.responseType;if(o&&n){var r=t.querySelectorAll(n),s=i(r),l=function(){this.appendItems(r,s),this.isLoading=!1,this.dispatchEvent("append",null,[t,e,r])}.bind(this);this.options.outlayer?this.appendOutlayerItems(s,l):l()}},s.appendItems=function(t,e){t&&t.length&&(e=e||i(t),n(e),this.element.appendChild(e))},s.appendOutlayerItems=function(i,n){var o=e.imagesLoaded||t.imagesLoaded;return o?void o(i,n):(console.error("[InfiniteScroll] imagesLoaded required for outlayer option"),void(this.isLoading=!1))},s.onAppendOutlayer=function(t,e,i){this.options.outlayer.appended(i)},s.checkLastPage=function(t,e){var i=this.options.checkLastPage;if(i){var n=this.options.path;if("function"==typeof n){var o=this.getPath();if(!o)return void this.lastPageReached(t,e)}var r;if("string"==typeof i?r=i:this.isPathSelector&&(r=n),r&&t.querySelector){var s=t.querySelector(r);s||this.lastPageReached(t,e)}}},s.lastPageReached=function(t,e){this.canLoad=!1,this.dispatchEvent("last",null,[t,e])},s.onPageError=function(t,e){return this.isLoading=!1,this.canLoad=!1,this.dispatchEvent("error",null,[t,e]),t},e.create.prefill=function(){if(this.options.prefill){var t=this.options.append;if(!t)return void console.error("append option required for prefill. Set as :"+t);this.updateMeasurements(),this.updateScroller(),this.isPrefilling=!0,this.on("append",this.prefill),this.once("error",this.stopPrefill),this.once("last",this.stopPrefill),this.prefill()}},s.prefill=function(){var t=this.getPrefillDistance();this.isPrefilling=t>=0,this.isPrefilling?(this.log("prefill"),this.loadNextPage()):this.stopPrefill()},s.getPrefillDistance=function(){return this.options.elementScroll?this.scroller.clientHeight-this.scroller.scrollHeight:this.windowHeight-this.element.clientHeight},s.stopPrefill=function(){this.log("stopPrefill"),this.off("append",this.prefill)},e}),function(t,e){"function"==typeof define&&define.amd?define("infinite-scroll/js/scroll-watch",["./core","fizzy-ui-utils/utils"],function(i,n){return e(t,i,n)}):"object"==typeof module&&module.exports?module.exports=e(t,require("./core"),require("fizzy-ui-utils")):e(t,t.InfiniteScroll,t.fizzyUIUtils)}(window,function(t,e,i){var n=e.prototype;return e.defaults.scrollThreshold=400,e.create.scrollWatch=function(){this.pageScrollHandler=this.onPageScroll.bind(this),this.resizeHandler=this.onResize.bind(this);var t=this.options.scrollThreshold,e=t||0===t;e&&this.enableScrollWatch()},e.destroy.scrollWatch=function(){this.disableScrollWatch()},n.enableScrollWatch=function(){this.isScrollWatching||(this.isScrollWatching=!0,this.updateMeasurements(),this.updateScroller(),this.on("last",this.disableScrollWatch),this.bindScrollWatchEvents(!0))},n.disableScrollWatch=function(){this.isScrollWatching&&(this.bindScrollWatchEvents(!1),delete this.isScrollWatching)},n.bindScrollWatchEvents=function(e){var i=e?"addEventListener":"removeEventListener";this.scroller[i]("scroll",this.pageScrollHandler),t[i]("resize",this.resizeHandler)},n.onPageScroll=e.throttle(function(){var t=this.getBottomDistance();t<=this.options.scrollThreshold&&this.dispatchEvent("scrollThreshold")}),n.getBottomDistance=function(){return this.options.elementScroll?this.getElementBottomDistance():this.getWindowBottomDistance()},n.getWindowBottomDistance=function(){var e=this.top+this.element.clientHeight,i=t.pageYOffset+this.windowHeight;return e-i},n.getElementBottomDistance=function(){var t=this.scroller.scrollHeight,e=this.scroller.scrollTop+this.scroller.clientHeight;return t-e},n.onResize=function(){this.updateMeasurements()},i.debounceMethod(e,"onResize",150),e}),function(t,e){"function"==typeof define&&define.amd?define("infinite-scroll/js/history",["./core","fizzy-ui-utils/utils"],function(i,n){return e(t,i,n)}):"object"==typeof module&&module.exports?module.exports=e(t,require("./core"),require("fizzy-ui-utils")):e(t,t.InfiniteScroll,t.fizzyUIUtils)}(window,function(t,e,i){var n=e.prototype;e.defaults.history="replace";var o=document.createElement("a");return e.create.history=function(){if(this.options.history){o.href=this.getAbsolutePath();var t=o.origin||o.protocol+"//"+o.host,e=t==location.origin;return e?void(this.options.append?this.createHistoryAppend():this.createHistoryPageLoad()):void console.error("[InfiniteScroll] cannot set history with different origin: "+o.origin+" on "+location.origin+" . History behavior disabled.")}},n.createHistoryAppend=function(){this.updateMeasurements(),this.updateScroller(),this.scrollPages=[{top:0,path:location.href,title:document.title}],this.scrollPageIndex=0,this.scrollHistoryHandler=this.onScrollHistory.bind(this),this.unloadHandler=this.onUnload.bind(this),this.scroller.addEventListener("scroll",this.scrollHistoryHandler),this.on("append",this.onAppendHistory),this.bindHistoryAppendEvents(!0)},n.bindHistoryAppendEvents=function(e){var i=e?"addEventListener":"removeEventListener";this.scroller[i]("scroll",this.scrollHistoryHandler),t[i]("unload",this.unloadHandler)},n.createHistoryPageLoad=function(){this.on("load",this.onPageLoadHistory)},e.destroy.history=n.destroyHistory=function(){var t=this.options.history&&this.options.append;t&&this.bindHistoryAppendEvents(!1)},n.onAppendHistory=function(t,e,i){if(i&&i.length){var n=i[0],r=this.getElementScrollY(n);o.href=e,this.scrollPages.push({top:r,path:o.href,title:t.title})}},n.getElementScrollY=function(t){return this.options.elementScroll?this.getElementElementScrollY(t):this.getElementWindowScrollY(t)},n.getElementWindowScrollY=function(e){var i=e.getBoundingClientRect();return i.top+t.pageYOffset},n.getElementElementScrollY=function(t){return t.offsetTop-this.top},n.onScrollHistory=function(){for(var t,e,i=this.getScrollViewY(),n=0;n<this.scrollPages.length;n++){var o=this.scrollPages[n];if(o.top>=i)break;t=n,e=o}t!=this.scrollPageIndex&&(this.scrollPageIndex=t,this.setHistory(e.title,e.path))},i.debounceMethod(e,"onScrollHistory",150),n.getScrollViewY=function(){return this.options.elementScroll?this.scroller.scrollTop+this.scroller.clientHeight/2:t.pageYOffset+this.windowHeight/2},n.setHistory=function(t,e){var i=this.options.history,n=i&&history[i+"State"];n&&(history[i+"State"](null,t,e),this.options.historyTitle&&(document.title=t),this.dispatchEvent("history",null,[t,e]))},n.onUnload=function(){var e=this.scrollPageIndex;if(0!==e){var i=this.scrollPages[e],n=t.pageYOffset-i.top+this.top;this.destroyHistory(),scrollTo(0,n)}},n.onPageLoadHistory=function(t,e){this.setHistory(t.title,e)},e}),function(t,e){"function"==typeof define&&define.amd?define("infinite-scroll/js/button",["./core","fizzy-ui-utils/utils"],function(i,n){return e(t,i,n)}):"object"==typeof module&&module.exports?module.exports=e(t,require("./core"),require("fizzy-ui-utils")):e(t,t.InfiniteScroll,t.fizzyUIUtils)}(window,function(t,e,i){function n(t,e){this.element=t,this.infScroll=e,this.clickHandler=this.onClick.bind(this),this.element.addEventListener("click",this.clickHandler),e.on("request",this.disable.bind(this)),e.on("load",this.enable.bind(this)),e.on("error",this.hide.bind(this)),e.on("last",this.hide.bind(this))}return e.create.button=function(){var t=i.getQueryElement(this.options.button);if(t)return void(this.button=new n(t,this))},e.destroy.button=function(){this.button&&this.button.destroy()},n.prototype.onClick=function(t){t.preventDefault(),this.infScroll.loadNextPage()},n.prototype.enable=function(){this.element.removeAttribute("disabled")},n.prototype.disable=function(){this.element.disabled="disabled"},n.prototype.hide=function(){this.element.style.display="none"},n.prototype.destroy=function(){this.element.removeEventListener("click",this.clickHandler)},e.Button=n,e}),function(t,e){"function"==typeof define&&define.amd?define("infinite-scroll/js/status",["./core","fizzy-ui-utils/utils"],function(i,n){return e(t,i,n)}):"object"==typeof module&&module.exports?module.exports=e(t,require("./core"),require("fizzy-ui-utils")):e(t,t.InfiniteScroll,t.fizzyUIUtils)}(window,function(t,e,i){function n(t){r(t,"none")}function o(t){r(t,"block")}function r(t,e){t&&(t.style.display=e)}var s=e.prototype;return e.create.status=function(){var t=i.getQueryElement(this.options.status);t&&(this.statusElement=t,this.statusEventElements={request:t.querySelector(".infinite-scroll-request"),error:t.querySelector(".infinite-scroll-error"),last:t.querySelector(".infinite-scroll-last")},this.on("request",this.showRequestStatus),this.on("error",this.showErrorStatus),this.on("last",this.showLastStatus),this.bindHideStatus("on"))},s.bindHideStatus=function(t){var e=this.options.append?"append":"load";this[t](e,this.hideAllStatus)},s.showRequestStatus=function(){this.showStatus("request")},s.showErrorStatus=function(){this.showStatus("error")},s.showLastStatus=function(){this.showStatus("last"),this.bindHideStatus("off")},s.showStatus=function(t){o(this.statusElement),this.hideStatusEventElements();var e=this.statusEventElements[t];o(e)},s.hideAllStatus=function(){n(this.statusElement),this.hideStatusEventElements()},s.hideStatusEventElements=function(){for(var t in this.statusEventElements){var e=this.statusEventElements[t];n(e)}},e}),function(t,e){"function"==typeof define&&define.amd?define(["infinite-scroll/js/core","infinite-scroll/js/page-load","infinite-scroll/js/scroll-watch","infinite-scroll/js/history","infinite-scroll/js/button","infinite-scroll/js/status"],e):"object"==typeof module&&module.exports&&(module.exports=e(require("./core"),require("./page-load"),require("./scroll-watch"),require("./history"),require("./button"),require("./status")))}(window,function(t){return t}),function(t,e){"use strict";"function"==typeof define&&define.amd?define("imagesloaded/imagesloaded",["ev-emitter/ev-emitter"],function(i){return e(t,i)}):"object"==typeof module&&module.exports?module.exports=e(t,require("ev-emitter")):t.imagesLoaded=e(t,t.EvEmitter)}("undefined"!=typeof window?window:this,function(t,e){function i(t,e){for(var i in e)t[i]=e[i];return t}function n(t){if(Array.isArray(t))return t;var e="object"==typeof t&&"number"==typeof t.length;return e?h.call(t):[t]}function o(t,e,r){if(!(this instanceof o))return new o(t,e,r);var s=t;return"string"==typeof t&&(s=document.querySelectorAll(t)),s?(this.elements=n(s),this.options=i({},this.options),"function"==typeof e?r=e:i(this.options,e),r&&this.on("always",r),this.getImages(),l&&(this.jqDeferred=new l.Deferred),void setTimeout(this.check.bind(this))):void a.error("Bad element for imagesLoaded "+(s||t))}function r(t){this.img=t}function s(t,e){this.url=t,this.element=e,this.img=new Image}var l=t.jQuery,a=t.console,h=Array.prototype.slice;o.prototype=Object.create(e.prototype),o.prototype.options={},o.prototype.getImages=function(){this.images=[],this.elements.forEach(this.addElementImages,this)},o.prototype.addElementImages=function(t){"IMG"==t.nodeName&&this.addImage(t),this.options.background===!0&&this.addElementBackgroundImages(t);var e=t.nodeType;if(e&&c[e]){for(var i=t.querySelectorAll("img"),n=0;n<i.length;n++){var o=i[n];this.addImage(o)}if("string"==typeof this.options.background){var r=t.querySelectorAll(this.options.background);for(n=0;n<r.length;n++){var s=r[n];this.addElementBackgroundImages(s)}}}};var c={1:!0,9:!0,11:!0};return o.prototype.addElementBackgroundImages=function(t){var e=getComputedStyle(t);if(e)for(var i=/url\((['"])?(.*?)\1\)/gi,n=i.exec(e.backgroundImage);null!==n;){var o=n&&n[2];o&&this.addBackground(o,t),n=i.exec(e.backgroundImage)}},o.prototype.addImage=function(t){var e=new r(t);this.images.push(e)},o.prototype.addBackground=function(t,e){var i=new s(t,e);this.images.push(i)},o.prototype.check=function(){function t(t,i,n){setTimeout(function(){e.progress(t,i,n)})}var e=this;return this.progressedCount=0,this.hasAnyBroken=!1,this.images.length?void this.images.forEach(function(e){e.once("progress",t),e.check()}):void this.complete()},o.prototype.progress=function(t,e,i){this.progressedCount++,this.hasAnyBroken=this.hasAnyBroken||!t.isLoaded,this.emitEvent("progress",[this,t,e]),this.jqDeferred&&this.jqDeferred.notify&&this.jqDeferred.notify(this,t),this.progressedCount==this.images.length&&this.complete(),this.options.debug&&a&&a.log("progress: "+i,t,e)},o.prototype.complete=function(){var t=this.hasAnyBroken?"fail":"done";if(this.isComplete=!0,this.emitEvent(t,[this]),this.emitEvent("always",[this]),this.jqDeferred){var e=this.hasAnyBroken?"reject":"resolve";this.jqDeferred[e](this)}},r.prototype=Object.create(e.prototype),r.prototype.check=function(){var t=this.getIsImageComplete();return t?void this.confirm(0!==this.img.naturalWidth,"naturalWidth"):(this.proxyImage=new Image,this.proxyImage.addEventListener("load",this),this.proxyImage.addEventListener("error",this),this.img.addEventListener("load",this),this.img.addEventListener("error",this),void(this.proxyImage.src=this.img.src))},r.prototype.getIsImageComplete=function(){return this.img.complete&&this.img.naturalWidth},r.prototype.confirm=function(t,e){this.isLoaded=t,this.emitEvent("progress",[this,this.img,e])},r.prototype.handleEvent=function(t){var e="on"+t.type;this[e]&&this[e](t)},r.prototype.onload=function(){this.confirm(!0,"onload"),this.unbindEvents()},r.prototype.onerror=function(){this.confirm(!1,"onerror"),this.unbindEvents()},r.prototype.unbindEvents=function(){this.proxyImage.removeEventListener("load",this),this.proxyImage.removeEventListener("error",this),this.img.removeEventListener("load",this),this.img.removeEventListener("error",this)},s.prototype=Object.create(r.prototype),s.prototype.check=function(){this.img.addEventListener("load",this),this.img.addEventListener("error",this),this.img.src=this.url;var t=this.getIsImageComplete();t&&(this.confirm(0!==this.img.naturalWidth,"naturalWidth"),this.unbindEvents())},s.prototype.unbindEvents=function(){this.img.removeEventListener("load",this),this.img.removeEventListener("error",this)},s.prototype.confirm=function(t,e){this.isLoaded=t,this.emitEvent("progress",[this,this.element,e])},o.makeJQueryPlugin=function(e){e=e||t.jQuery,e&&(l=e,l.fn.imagesLoaded=function(t,e){var i=new o(this,t,e);return i.jqDeferred.promise(l(this))})},o.makeJQueryPlugin(),o});

/*! jQuery UI - v1.12.1 - 2018-01-02
* http://jqueryui.com
* Includes: widget.js, keycode.js, unique-id.js, widgets/tabs.js, effect.js, effects/effect-fade.js, effects/effect-slide.js
* Copyright jQuery Foundation and other contributors; Licensed MIT */
(function(t){"function"==typeof define&&define.amd?define(["jquery"],t):t(jQuery)})(function(t){t.ui=t.ui||{},t.ui.version="1.12.1";var e=0,i=Array.prototype.slice;t.cleanData=function(e){return function(i){var s,n,o;for(o=0;null!=(n=i[o]);o++)try{s=t._data(n,"events"),s&&s.remove&&t(n).triggerHandler("remove")}catch(a){}e(i)}}(t.cleanData),t.widget=function(e,i,s){var n,o,a,r={},l=e.split(".")[0];e=e.split(".")[1];var h=l+"-"+e;return s||(s=i,i=t.Widget),t.isArray(s)&&(s=t.extend.apply(null,[{}].concat(s))),t.expr[":"][h.toLowerCase()]=function(e){return!!t.data(e,h)},t[l]=t[l]||{},n=t[l][e],o=t[l][e]=function(t,e){return this._createWidget?(arguments.length&&this._createWidget(t,e),void 0):new o(t,e)},t.extend(o,n,{version:s.version,_proto:t.extend({},s),_childConstructors:[]}),a=new i,a.options=t.widget.extend({},a.options),t.each(s,function(e,s){return t.isFunction(s)?(r[e]=function(){function t(){return i.prototype[e].apply(this,arguments)}function n(t){return i.prototype[e].apply(this,t)}return function(){var e,i=this._super,o=this._superApply;return this._super=t,this._superApply=n,e=s.apply(this,arguments),this._super=i,this._superApply=o,e}}(),void 0):(r[e]=s,void 0)}),o.prototype=t.widget.extend(a,{widgetEventPrefix:n?a.widgetEventPrefix||e:e},r,{constructor:o,namespace:l,widgetName:e,widgetFullName:h}),n?(t.each(n._childConstructors,function(e,i){var s=i.prototype;t.widget(s.namespace+"."+s.widgetName,o,i._proto)}),delete n._childConstructors):i._childConstructors.push(o),t.widget.bridge(e,o),o},t.widget.extend=function(e){for(var s,n,o=i.call(arguments,1),a=0,r=o.length;r>a;a++)for(s in o[a])n=o[a][s],o[a].hasOwnProperty(s)&&void 0!==n&&(e[s]=t.isPlainObject(n)?t.isPlainObject(e[s])?t.widget.extend({},e[s],n):t.widget.extend({},n):n);return e},t.widget.bridge=function(e,s){var n=s.prototype.widgetFullName||e;t.fn[e]=function(o){var a="string"==typeof o,r=i.call(arguments,1),l=this;return a?this.length||"instance"!==o?this.each(function(){var i,s=t.data(this,n);return"instance"===o?(l=s,!1):s?t.isFunction(s[o])&&"_"!==o.charAt(0)?(i=s[o].apply(s,r),i!==s&&void 0!==i?(l=i&&i.jquery?l.pushStack(i.get()):i,!1):void 0):t.error("no such method '"+o+"' for "+e+" widget instance"):t.error("cannot call methods on "+e+" prior to initialization; "+"attempted to call method '"+o+"'")}):l=void 0:(r.length&&(o=t.widget.extend.apply(null,[o].concat(r))),this.each(function(){var e=t.data(this,n);e?(e.option(o||{}),e._init&&e._init()):t.data(this,n,new s(o,this))})),l}},t.Widget=function(){},t.Widget._childConstructors=[],t.Widget.prototype={widgetName:"widget",widgetEventPrefix:"",defaultElement:"<div>",options:{classes:{},disabled:!1,create:null},_createWidget:function(i,s){s=t(s||this.defaultElement||this)[0],this.element=t(s),this.uuid=e++,this.eventNamespace="."+this.widgetName+this.uuid,this.bindings=t(),this.hoverable=t(),this.focusable=t(),this.classesElementLookup={},s!==this&&(t.data(s,this.widgetFullName,this),this._on(!0,this.element,{remove:function(t){t.target===s&&this.destroy()}}),this.document=t(s.style?s.ownerDocument:s.document||s),this.window=t(this.document[0].defaultView||this.document[0].parentWindow)),this.options=t.widget.extend({},this.options,this._getCreateOptions(),i),this._create(),this.options.disabled&&this._setOptionDisabled(this.options.disabled),this._trigger("create",null,this._getCreateEventData()),this._init()},_getCreateOptions:function(){return{}},_getCreateEventData:t.noop,_create:t.noop,_init:t.noop,destroy:function(){var e=this;this._destroy(),t.each(this.classesElementLookup,function(t,i){e._removeClass(i,t)}),this.element.off(this.eventNamespace).removeData(this.widgetFullName),this.widget().off(this.eventNamespace).removeAttr("aria-disabled"),this.bindings.off(this.eventNamespace)},_destroy:t.noop,widget:function(){return this.element},option:function(e,i){var s,n,o,a=e;if(0===arguments.length)return t.widget.extend({},this.options);if("string"==typeof e)if(a={},s=e.split("."),e=s.shift(),s.length){for(n=a[e]=t.widget.extend({},this.options[e]),o=0;s.length-1>o;o++)n[s[o]]=n[s[o]]||{},n=n[s[o]];if(e=s.pop(),1===arguments.length)return void 0===n[e]?null:n[e];n[e]=i}else{if(1===arguments.length)return void 0===this.options[e]?null:this.options[e];a[e]=i}return this._setOptions(a),this},_setOptions:function(t){var e;for(e in t)this._setOption(e,t[e]);return this},_setOption:function(t,e){return"classes"===t&&this._setOptionClasses(e),this.options[t]=e,"disabled"===t&&this._setOptionDisabled(e),this},_setOptionClasses:function(e){var i,s,n;for(i in e)n=this.classesElementLookup[i],e[i]!==this.options.classes[i]&&n&&n.length&&(s=t(n.get()),this._removeClass(n,i),s.addClass(this._classes({element:s,keys:i,classes:e,add:!0})))},_setOptionDisabled:function(t){this._toggleClass(this.widget(),this.widgetFullName+"-disabled",null,!!t),t&&(this._removeClass(this.hoverable,null,"ui-state-hover"),this._removeClass(this.focusable,null,"ui-state-focus"))},enable:function(){return this._setOptions({disabled:!1})},disable:function(){return this._setOptions({disabled:!0})},_classes:function(e){function i(i,o){var a,r;for(r=0;i.length>r;r++)a=n.classesElementLookup[i[r]]||t(),a=e.add?t(t.unique(a.get().concat(e.element.get()))):t(a.not(e.element).get()),n.classesElementLookup[i[r]]=a,s.push(i[r]),o&&e.classes[i[r]]&&s.push(e.classes[i[r]])}var s=[],n=this;return e=t.extend({element:this.element,classes:this.options.classes||{}},e),this._on(e.element,{remove:"_untrackClassesElement"}),e.keys&&i(e.keys.match(/\S+/g)||[],!0),e.extra&&i(e.extra.match(/\S+/g)||[]),s.join(" ")},_untrackClassesElement:function(e){var i=this;t.each(i.classesElementLookup,function(s,n){-1!==t.inArray(e.target,n)&&(i.classesElementLookup[s]=t(n.not(e.target).get()))})},_removeClass:function(t,e,i){return this._toggleClass(t,e,i,!1)},_addClass:function(t,e,i){return this._toggleClass(t,e,i,!0)},_toggleClass:function(t,e,i,s){s="boolean"==typeof s?s:i;var n="string"==typeof t||null===t,o={extra:n?e:i,keys:n?t:e,element:n?this.element:t,add:s};return o.element.toggleClass(this._classes(o),s),this},_on:function(e,i,s){var n,o=this;"boolean"!=typeof e&&(s=i,i=e,e=!1),s?(i=n=t(i),this.bindings=this.bindings.add(i)):(s=i,i=this.element,n=this.widget()),t.each(s,function(s,a){function r(){return e||o.options.disabled!==!0&&!t(this).hasClass("ui-state-disabled")?("string"==typeof a?o[a]:a).apply(o,arguments):void 0}"string"!=typeof a&&(r.guid=a.guid=a.guid||r.guid||t.guid++);var l=s.match(/^([\w:-]*)\s*(.*)$/),h=l[1]+o.eventNamespace,c=l[2];c?n.on(h,c,r):i.on(h,r)})},_off:function(e,i){i=(i||"").split(" ").join(this.eventNamespace+" ")+this.eventNamespace,e.off(i).off(i),this.bindings=t(this.bindings.not(e).get()),this.focusable=t(this.focusable.not(e).get()),this.hoverable=t(this.hoverable.not(e).get())},_delay:function(t,e){function i(){return("string"==typeof t?s[t]:t).apply(s,arguments)}var s=this;return setTimeout(i,e||0)},_hoverable:function(e){this.hoverable=this.hoverable.add(e),this._on(e,{mouseenter:function(e){this._addClass(t(e.currentTarget),null,"ui-state-hover")},mouseleave:function(e){this._removeClass(t(e.currentTarget),null,"ui-state-hover")}})},_focusable:function(e){this.focusable=this.focusable.add(e),this._on(e,{focusin:function(e){this._addClass(t(e.currentTarget),null,"ui-state-focus")},focusout:function(e){this._removeClass(t(e.currentTarget),null,"ui-state-focus")}})},_trigger:function(e,i,s){var n,o,a=this.options[e];if(s=s||{},i=t.Event(i),i.type=(e===this.widgetEventPrefix?e:this.widgetEventPrefix+e).toLowerCase(),i.target=this.element[0],o=i.originalEvent)for(n in o)n in i||(i[n]=o[n]);return this.element.trigger(i,s),!(t.isFunction(a)&&a.apply(this.element[0],[i].concat(s))===!1||i.isDefaultPrevented())}},t.each({show:"fadeIn",hide:"fadeOut"},function(e,i){t.Widget.prototype["_"+e]=function(s,n,o){"string"==typeof n&&(n={effect:n});var a,r=n?n===!0||"number"==typeof n?i:n.effect||i:e;n=n||{},"number"==typeof n&&(n={duration:n}),a=!t.isEmptyObject(n),n.complete=o,n.delay&&s.delay(n.delay),a&&t.effects&&t.effects.effect[r]?s[e](n):r!==e&&s[r]?s[r](n.duration,n.easing,o):s.queue(function(i){t(this)[e](),o&&o.call(s[0]),i()})}}),t.widget,t.ui.keyCode={BACKSPACE:8,COMMA:188,DELETE:46,DOWN:40,END:35,ENTER:13,ESCAPE:27,HOME:36,LEFT:37,PAGE_DOWN:34,PAGE_UP:33,PERIOD:190,RIGHT:39,SPACE:32,TAB:9,UP:38},t.fn.extend({uniqueId:function(){var t=0;return function(){return this.each(function(){this.id||(this.id="ui-id-"+ ++t)})}}(),removeUniqueId:function(){return this.each(function(){/^ui-id-\d+$/.test(this.id)&&t(this).removeAttr("id")})}}),t.ui.escapeSelector=function(){var t=/([!"#$%&'()*+,./:;<=>?@[\]^`{|}~])/g;return function(e){return e.replace(t,"\\$1")}}(),t.ui.safeActiveElement=function(t){var e;try{e=t.activeElement}catch(i){e=t.body}return e||(e=t.body),e.nodeName||(e=t.body),e},t.widget("ui.tabs",{version:"1.12.1",delay:300,options:{active:null,classes:{"ui-tabs":"ui-corner-all","ui-tabs-nav":"ui-corner-all","ui-tabs-panel":"ui-corner-bottom","ui-tabs-tab":"ui-corner-top"},collapsible:!1,event:"click",heightStyle:"content",hide:null,show:null,activate:null,beforeActivate:null,beforeLoad:null,load:null},_isLocal:function(){var t=/#.*$/;return function(e){var i,s;i=e.href.replace(t,""),s=location.href.replace(t,"");try{i=decodeURIComponent(i)}catch(n){}try{s=decodeURIComponent(s)}catch(n){}return e.hash.length>1&&i===s}}(),_create:function(){var e=this,i=this.options;this.running=!1,this._addClass("ui-tabs","ui-widget ui-widget-content"),this._toggleClass("ui-tabs-collapsible",null,i.collapsible),this._processTabs(),i.active=this._initialActive(),t.isArray(i.disabled)&&(i.disabled=t.unique(i.disabled.concat(t.map(this.tabs.filter(".ui-state-disabled"),function(t){return e.tabs.index(t)}))).sort()),this.active=this.options.active!==!1&&this.anchors.length?this._findActive(i.active):t(),this._refresh(),this.active.length&&this.load(i.active)},_initialActive:function(){var e=this.options.active,i=this.options.collapsible,s=location.hash.substring(1);return null===e&&(s&&this.tabs.each(function(i,n){return t(n).attr("aria-controls")===s?(e=i,!1):void 0}),null===e&&(e=this.tabs.index(this.tabs.filter(".ui-tabs-active"))),(null===e||-1===e)&&(e=this.tabs.length?0:!1)),e!==!1&&(e=this.tabs.index(this.tabs.eq(e)),-1===e&&(e=i?!1:0)),!i&&e===!1&&this.anchors.length&&(e=0),e},_getCreateEventData:function(){return{tab:this.active,panel:this.active.length?this._getPanelForTab(this.active):t()}},_tabKeydown:function(e){var i=t(t.ui.safeActiveElement(this.document[0])).closest("li"),s=this.tabs.index(i),n=!0;if(!this._handlePageNav(e)){switch(e.keyCode){case t.ui.keyCode.RIGHT:case t.ui.keyCode.DOWN:s++;break;case t.ui.keyCode.UP:case t.ui.keyCode.LEFT:n=!1,s--;break;case t.ui.keyCode.END:s=this.anchors.length-1;break;case t.ui.keyCode.HOME:s=0;break;case t.ui.keyCode.SPACE:return e.preventDefault(),clearTimeout(this.activating),this._activate(s),void 0;case t.ui.keyCode.ENTER:return e.preventDefault(),clearTimeout(this.activating),this._activate(s===this.options.active?!1:s),void 0;default:return}e.preventDefault(),clearTimeout(this.activating),s=this._focusNextTab(s,n),e.ctrlKey||e.metaKey||(i.attr("aria-selected","false"),this.tabs.eq(s).attr("aria-selected","true"),this.activating=this._delay(function(){this.option("active",s)},this.delay))}},_panelKeydown:function(e){this._handlePageNav(e)||e.ctrlKey&&e.keyCode===t.ui.keyCode.UP&&(e.preventDefault(),this.active.trigger("focus"))},_handlePageNav:function(e){return e.altKey&&e.keyCode===t.ui.keyCode.PAGE_UP?(this._activate(this._focusNextTab(this.options.active-1,!1)),!0):e.altKey&&e.keyCode===t.ui.keyCode.PAGE_DOWN?(this._activate(this._focusNextTab(this.options.active+1,!0)),!0):void 0},_findNextTab:function(e,i){function s(){return e>n&&(e=0),0>e&&(e=n),e}for(var n=this.tabs.length-1;-1!==t.inArray(s(),this.options.disabled);)e=i?e+1:e-1;return e},_focusNextTab:function(t,e){return t=this._findNextTab(t,e),this.tabs.eq(t).trigger("focus"),t},_setOption:function(t,e){return"active"===t?(this._activate(e),void 0):(this._super(t,e),"collapsible"===t&&(this._toggleClass("ui-tabs-collapsible",null,e),e||this.options.active!==!1||this._activate(0)),"event"===t&&this._setupEvents(e),"heightStyle"===t&&this._setupHeightStyle(e),void 0)},_sanitizeSelector:function(t){return t?t.replace(/[!"$%&'()*+,.\/:;<=>?@\[\]\^`{|}~]/g,"\\$&"):""},refresh:function(){var e=this.options,i=this.tablist.children(":has(a[href])");e.disabled=t.map(i.filter(".ui-state-disabled"),function(t){return i.index(t)}),this._processTabs(),e.active!==!1&&this.anchors.length?this.active.length&&!t.contains(this.tablist[0],this.active[0])?this.tabs.length===e.disabled.length?(e.active=!1,this.active=t()):this._activate(this._findNextTab(Math.max(0,e.active-1),!1)):e.active=this.tabs.index(this.active):(e.active=!1,this.active=t()),this._refresh()},_refresh:function(){this._setOptionDisabled(this.options.disabled),this._setupEvents(this.options.event),this._setupHeightStyle(this.options.heightStyle),this.tabs.not(this.active).attr({"aria-selected":"false","aria-expanded":"false",tabIndex:-1}),this.panels.not(this._getPanelForTab(this.active)).hide().attr({"aria-hidden":"true"}),this.active.length?(this.active.attr({"aria-selected":"true","aria-expanded":"true",tabIndex:0}),this._addClass(this.active,"ui-tabs-active","ui-state-active"),this._getPanelForTab(this.active).show().attr({"aria-hidden":"false"})):this.tabs.eq(0).attr("tabIndex",0)},_processTabs:function(){var e=this,i=this.tabs,s=this.anchors,n=this.panels;this.tablist=this._getList().attr("role","tablist"),this._addClass(this.tablist,"ui-tabs-nav","ui-helper-reset ui-helper-clearfix ui-widget-header"),this.tablist.on("mousedown"+this.eventNamespace,"> li",function(e){t(this).is(".ui-state-disabled")&&e.preventDefault()}).on("focus"+this.eventNamespace,".ui-tabs-anchor",function(){t(this).closest("li").is(".ui-state-disabled")&&this.blur()}),this.tabs=this.tablist.find("> li:has(a[href])").attr({role:"tab",tabIndex:-1}),this._addClass(this.tabs,"ui-tabs-tab","ui-state-default"),this.anchors=this.tabs.map(function(){return t("a",this)[0]}).attr({role:"presentation",tabIndex:-1}),this._addClass(this.anchors,"ui-tabs-anchor"),this.panels=t(),this.anchors.each(function(i,s){var n,o,a,r=t(s).uniqueId().attr("id"),l=t(s).closest("li"),h=l.attr("aria-controls");e._isLocal(s)?(n=s.hash,a=n.substring(1),o=e.element.find(e._sanitizeSelector(n))):(a=l.attr("aria-controls")||t({}).uniqueId()[0].id,n="#"+a,o=e.element.find(n),o.length||(o=e._createPanel(a),o.insertAfter(e.panels[i-1]||e.tablist)),o.attr("aria-live","polite")),o.length&&(e.panels=e.panels.add(o)),h&&l.data("ui-tabs-aria-controls",h),l.attr({"aria-controls":a,"aria-labelledby":r}),o.attr("aria-labelledby",r)}),this.panels.attr("role","tabpanel"),this._addClass(this.panels,"ui-tabs-panel","ui-widget-content"),i&&(this._off(i.not(this.tabs)),this._off(s.not(this.anchors)),this._off(n.not(this.panels)))},_getList:function(){return this.tablist||this.element.find("ol, ul").eq(0)},_createPanel:function(e){return t("<div>").attr("id",e).data("ui-tabs-destroy",!0)},_setOptionDisabled:function(e){var i,s,n;for(t.isArray(e)&&(e.length?e.length===this.anchors.length&&(e=!0):e=!1),n=0;s=this.tabs[n];n++)i=t(s),e===!0||-1!==t.inArray(n,e)?(i.attr("aria-disabled","true"),this._addClass(i,null,"ui-state-disabled")):(i.removeAttr("aria-disabled"),this._removeClass(i,null,"ui-state-disabled"));this.options.disabled=e,this._toggleClass(this.widget(),this.widgetFullName+"-disabled",null,e===!0)},_setupEvents:function(e){var i={};e&&t.each(e.split(" "),function(t,e){i[e]="_eventHandler"}),this._off(this.anchors.add(this.tabs).add(this.panels)),this._on(!0,this.anchors,{click:function(t){t.preventDefault()}}),this._on(this.anchors,i),this._on(this.tabs,{keydown:"_tabKeydown"}),this._on(this.panels,{keydown:"_panelKeydown"}),this._focusable(this.tabs),this._hoverable(this.tabs)},_setupHeightStyle:function(e){var i,s=this.element.parent();"fill"===e?(i=s.height(),i-=this.element.outerHeight()-this.element.height(),this.element.siblings(":visible").each(function(){var e=t(this),s=e.css("position");"absolute"!==s&&"fixed"!==s&&(i-=e.outerHeight(!0))}),this.element.children().not(this.panels).each(function(){i-=t(this).outerHeight(!0)}),this.panels.each(function(){t(this).height(Math.max(0,i-t(this).innerHeight()+t(this).height()))}).css("overflow","auto")):"auto"===e&&(i=0,this.panels.each(function(){i=Math.max(i,t(this).height("").height())}).height(i))},_eventHandler:function(e){var i=this.options,s=this.active,n=t(e.currentTarget),o=n.closest("li"),a=o[0]===s[0],r=a&&i.collapsible,l=r?t():this._getPanelForTab(o),h=s.length?this._getPanelForTab(s):t(),c={oldTab:s,oldPanel:h,newTab:r?t():o,newPanel:l};e.preventDefault(),o.hasClass("ui-state-disabled")||o.hasClass("ui-tabs-loading")||this.running||a&&!i.collapsible||this._trigger("beforeActivate",e,c)===!1||(i.active=r?!1:this.tabs.index(o),this.active=a?t():o,this.xhr&&this.xhr.abort(),h.length||l.length||t.error("jQuery UI Tabs: Mismatching fragment identifier."),l.length&&this.load(this.tabs.index(o),e),this._toggle(e,c))},_toggle:function(e,i){function s(){o.running=!1,o._trigger("activate",e,i)}function n(){o._addClass(i.newTab.closest("li"),"ui-tabs-active","ui-state-active"),a.length&&o.options.show?o._show(a,o.options.show,s):(a.show(),s())}var o=this,a=i.newPanel,r=i.oldPanel;this.running=!0,r.length&&this.options.hide?this._hide(r,this.options.hide,function(){o._removeClass(i.oldTab.closest("li"),"ui-tabs-active","ui-state-active"),n()}):(this._removeClass(i.oldTab.closest("li"),"ui-tabs-active","ui-state-active"),r.hide(),n()),r.attr("aria-hidden","true"),i.oldTab.attr({"aria-selected":"false","aria-expanded":"false"}),a.length&&r.length?i.oldTab.attr("tabIndex",-1):a.length&&this.tabs.filter(function(){return 0===t(this).attr("tabIndex")}).attr("tabIndex",-1),a.attr("aria-hidden","false"),i.newTab.attr({"aria-selected":"true","aria-expanded":"true",tabIndex:0})},_activate:function(e){var i,s=this._findActive(e);s[0]!==this.active[0]&&(s.length||(s=this.active),i=s.find(".ui-tabs-anchor")[0],this._eventHandler({target:i,currentTarget:i,preventDefault:t.noop}))},_findActive:function(e){return e===!1?t():this.tabs.eq(e)},_getIndex:function(e){return"string"==typeof e&&(e=this.anchors.index(this.anchors.filter("[href$='"+t.ui.escapeSelector(e)+"']"))),e},_destroy:function(){this.xhr&&this.xhr.abort(),this.tablist.removeAttr("role").off(this.eventNamespace),this.anchors.removeAttr("role tabIndex").removeUniqueId(),this.tabs.add(this.panels).each(function(){t.data(this,"ui-tabs-destroy")?t(this).remove():t(this).removeAttr("role tabIndex aria-live aria-busy aria-selected aria-labelledby aria-hidden aria-expanded")}),this.tabs.each(function(){var e=t(this),i=e.data("ui-tabs-aria-controls");i?e.attr("aria-controls",i).removeData("ui-tabs-aria-controls"):e.removeAttr("aria-controls")}),this.panels.show(),"content"!==this.options.heightStyle&&this.panels.css("height","")},enable:function(e){var i=this.options.disabled;i!==!1&&(void 0===e?i=!1:(e=this._getIndex(e),i=t.isArray(i)?t.map(i,function(t){return t!==e?t:null}):t.map(this.tabs,function(t,i){return i!==e?i:null})),this._setOptionDisabled(i))},disable:function(e){var i=this.options.disabled;if(i!==!0){if(void 0===e)i=!0;else{if(e=this._getIndex(e),-1!==t.inArray(e,i))return;i=t.isArray(i)?t.merge([e],i).sort():[e]}this._setOptionDisabled(i)}},load:function(e,i){e=this._getIndex(e);var s=this,n=this.tabs.eq(e),o=n.find(".ui-tabs-anchor"),a=this._getPanelForTab(n),r={tab:n,panel:a},l=function(t,e){"abort"===e&&s.panels.stop(!1,!0),s._removeClass(n,"ui-tabs-loading"),a.removeAttr("aria-busy"),t===s.xhr&&delete s.xhr};this._isLocal(o[0])||(this.xhr=t.ajax(this._ajaxSettings(o,i,r)),this.xhr&&"canceled"!==this.xhr.statusText&&(this._addClass(n,"ui-tabs-loading"),a.attr("aria-busy","true"),this.xhr.done(function(t,e,n){setTimeout(function(){a.html(t),s._trigger("load",i,r),l(n,e)},1)}).fail(function(t,e){setTimeout(function(){l(t,e)},1)})))},_ajaxSettings:function(e,i,s){var n=this;return{url:e.attr("href").replace(/#.*$/,""),beforeSend:function(e,o){return n._trigger("beforeLoad",i,t.extend({jqXHR:e,ajaxSettings:o},s))}}},_getPanelForTab:function(e){var i=t(e).attr("aria-controls");return this.element.find(this._sanitizeSelector("#"+i))}}),t.uiBackCompat!==!1&&t.widget("ui.tabs",t.ui.tabs,{_processTabs:function(){this._superApply(arguments),this._addClass(this.tabs,"ui-tab")}}),t.ui.tabs;var s="ui-effects-",n="ui-effects-style",o="ui-effects-animated",a=t;t.effects={effect:{}},function(t,e){function i(t,e,i){var s=u[e.type]||{};return null==t?i||!e.def?null:e.def:(t=s.floor?~~t:parseFloat(t),isNaN(t)?e.def:s.mod?(t+s.mod)%s.mod:0>t?0:t>s.max?s.max:t)}function s(i){var s=h(),n=s._rgba=[];return i=i.toLowerCase(),f(l,function(t,o){var a,r=o.re.exec(i),l=r&&o.parse(r),h=o.space||"rgba";return l?(a=s[h](l),s[c[h].cache]=a[c[h].cache],n=s._rgba=a._rgba,!1):e}),n.length?("0,0,0,0"===n.join()&&t.extend(n,o.transparent),s):o[i]}function n(t,e,i){return i=(i+1)%1,1>6*i?t+6*(e-t)*i:1>2*i?e:2>3*i?t+6*(e-t)*(2/3-i):t}var o,a="backgroundColor borderBottomColor borderLeftColor borderRightColor borderTopColor color columnRuleColor outlineColor textDecorationColor textEmphasisColor",r=/^([\-+])=\s*(\d+\.?\d*)/,l=[{re:/rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,parse:function(t){return[t[1],t[2],t[3],t[4]]}},{re:/rgba?\(\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,parse:function(t){return[2.55*t[1],2.55*t[2],2.55*t[3],t[4]]}},{re:/#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/,parse:function(t){return[parseInt(t[1],16),parseInt(t[2],16),parseInt(t[3],16)]}},{re:/#([a-f0-9])([a-f0-9])([a-f0-9])/,parse:function(t){return[parseInt(t[1]+t[1],16),parseInt(t[2]+t[2],16),parseInt(t[3]+t[3],16)]}},{re:/hsla?\(\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,space:"hsla",parse:function(t){return[t[1],t[2]/100,t[3]/100,t[4]]}}],h=t.Color=function(e,i,s,n){return new t.Color.fn.parse(e,i,s,n)},c={rgba:{props:{red:{idx:0,type:"byte"},green:{idx:1,type:"byte"},blue:{idx:2,type:"byte"}}},hsla:{props:{hue:{idx:0,type:"degrees"},saturation:{idx:1,type:"percent"},lightness:{idx:2,type:"percent"}}}},u={"byte":{floor:!0,max:255},percent:{max:1},degrees:{mod:360,floor:!0}},d=h.support={},p=t("<p>")[0],f=t.each;p.style.cssText="background-color:rgba(1,1,1,.5)",d.rgba=p.style.backgroundColor.indexOf("rgba")>-1,f(c,function(t,e){e.cache="_"+t,e.props.alpha={idx:3,type:"percent",def:1}}),h.fn=t.extend(h.prototype,{parse:function(n,a,r,l){if(n===e)return this._rgba=[null,null,null,null],this;(n.jquery||n.nodeType)&&(n=t(n).css(a),a=e);var u=this,d=t.type(n),p=this._rgba=[];return a!==e&&(n=[n,a,r,l],d="array"),"string"===d?this.parse(s(n)||o._default):"array"===d?(f(c.rgba.props,function(t,e){p[e.idx]=i(n[e.idx],e)}),this):"object"===d?(n instanceof h?f(c,function(t,e){n[e.cache]&&(u[e.cache]=n[e.cache].slice())}):f(c,function(e,s){var o=s.cache;f(s.props,function(t,e){if(!u[o]&&s.to){if("alpha"===t||null==n[t])return;u[o]=s.to(u._rgba)}u[o][e.idx]=i(n[t],e,!0)}),u[o]&&0>t.inArray(null,u[o].slice(0,3))&&(u[o][3]=1,s.from&&(u._rgba=s.from(u[o])))}),this):e},is:function(t){var i=h(t),s=!0,n=this;return f(c,function(t,o){var a,r=i[o.cache];return r&&(a=n[o.cache]||o.to&&o.to(n._rgba)||[],f(o.props,function(t,i){return null!=r[i.idx]?s=r[i.idx]===a[i.idx]:e})),s}),s},_space:function(){var t=[],e=this;return f(c,function(i,s){e[s.cache]&&t.push(i)}),t.pop()},transition:function(t,e){var s=h(t),n=s._space(),o=c[n],a=0===this.alpha()?h("transparent"):this,r=a[o.cache]||o.to(a._rgba),l=r.slice();return s=s[o.cache],f(o.props,function(t,n){var o=n.idx,a=r[o],h=s[o],c=u[n.type]||{};null!==h&&(null===a?l[o]=h:(c.mod&&(h-a>c.mod/2?a+=c.mod:a-h>c.mod/2&&(a-=c.mod)),l[o]=i((h-a)*e+a,n)))}),this[n](l)},blend:function(e){if(1===this._rgba[3])return this;var i=this._rgba.slice(),s=i.pop(),n=h(e)._rgba;return h(t.map(i,function(t,e){return(1-s)*n[e]+s*t}))},toRgbaString:function(){var e="rgba(",i=t.map(this._rgba,function(t,e){return null==t?e>2?1:0:t});return 1===i[3]&&(i.pop(),e="rgb("),e+i.join()+")"},toHslaString:function(){var e="hsla(",i=t.map(this.hsla(),function(t,e){return null==t&&(t=e>2?1:0),e&&3>e&&(t=Math.round(100*t)+"%"),t});return 1===i[3]&&(i.pop(),e="hsl("),e+i.join()+")"},toHexString:function(e){var i=this._rgba.slice(),s=i.pop();return e&&i.push(~~(255*s)),"#"+t.map(i,function(t){return t=(t||0).toString(16),1===t.length?"0"+t:t}).join("")},toString:function(){return 0===this._rgba[3]?"transparent":this.toRgbaString()}}),h.fn.parse.prototype=h.fn,c.hsla.to=function(t){if(null==t[0]||null==t[1]||null==t[2])return[null,null,null,t[3]];var e,i,s=t[0]/255,n=t[1]/255,o=t[2]/255,a=t[3],r=Math.max(s,n,o),l=Math.min(s,n,o),h=r-l,c=r+l,u=.5*c;return e=l===r?0:s===r?60*(n-o)/h+360:n===r?60*(o-s)/h+120:60*(s-n)/h+240,i=0===h?0:.5>=u?h/c:h/(2-c),[Math.round(e)%360,i,u,null==a?1:a]},c.hsla.from=function(t){if(null==t[0]||null==t[1]||null==t[2])return[null,null,null,t[3]];var e=t[0]/360,i=t[1],s=t[2],o=t[3],a=.5>=s?s*(1+i):s+i-s*i,r=2*s-a;return[Math.round(255*n(r,a,e+1/3)),Math.round(255*n(r,a,e)),Math.round(255*n(r,a,e-1/3)),o]},f(c,function(s,n){var o=n.props,a=n.cache,l=n.to,c=n.from;h.fn[s]=function(s){if(l&&!this[a]&&(this[a]=l(this._rgba)),s===e)return this[a].slice();var n,r=t.type(s),u="array"===r||"object"===r?s:arguments,d=this[a].slice();return f(o,function(t,e){var s=u["object"===r?t:e.idx];null==s&&(s=d[e.idx]),d[e.idx]=i(s,e)}),c?(n=h(c(d)),n[a]=d,n):h(d)},f(o,function(e,i){h.fn[e]||(h.fn[e]=function(n){var o,a=t.type(n),l="alpha"===e?this._hsla?"hsla":"rgba":s,h=this[l](),c=h[i.idx];return"undefined"===a?c:("function"===a&&(n=n.call(this,c),a=t.type(n)),null==n&&i.empty?this:("string"===a&&(o=r.exec(n),o&&(n=c+parseFloat(o[2])*("+"===o[1]?1:-1))),h[i.idx]=n,this[l](h)))})})}),h.hook=function(e){var i=e.split(" ");f(i,function(e,i){t.cssHooks[i]={set:function(e,n){var o,a,r="";if("transparent"!==n&&("string"!==t.type(n)||(o=s(n)))){if(n=h(o||n),!d.rgba&&1!==n._rgba[3]){for(a="backgroundColor"===i?e.parentNode:e;(""===r||"transparent"===r)&&a&&a.style;)try{r=t.css(a,"backgroundColor"),a=a.parentNode}catch(l){}n=n.blend(r&&"transparent"!==r?r:"_default")}n=n.toRgbaString()}try{e.style[i]=n}catch(l){}}},t.fx.step[i]=function(e){e.colorInit||(e.start=h(e.elem,i),e.end=h(e.end),e.colorInit=!0),t.cssHooks[i].set(e.elem,e.start.transition(e.end,e.pos))}})},h.hook(a),t.cssHooks.borderColor={expand:function(t){var e={};return f(["Top","Right","Bottom","Left"],function(i,s){e["border"+s+"Color"]=t}),e}},o=t.Color.names={aqua:"#00ffff",black:"#000000",blue:"#0000ff",fuchsia:"#ff00ff",gray:"#808080",green:"#008000",lime:"#00ff00",maroon:"#800000",navy:"#000080",olive:"#808000",purple:"#800080",red:"#ff0000",silver:"#c0c0c0",teal:"#008080",white:"#ffffff",yellow:"#ffff00",transparent:[null,null,null,0],_default:"#ffffff"}}(a),function(){function e(e){var i,s,n=e.ownerDocument.defaultView?e.ownerDocument.defaultView.getComputedStyle(e,null):e.currentStyle,o={};if(n&&n.length&&n[0]&&n[n[0]])for(s=n.length;s--;)i=n[s],"string"==typeof n[i]&&(o[t.camelCase(i)]=n[i]);else for(i in n)"string"==typeof n[i]&&(o[i]=n[i]);return o}function i(e,i){var s,o,a={};for(s in i)o=i[s],e[s]!==o&&(n[s]||(t.fx.step[s]||!isNaN(parseFloat(o)))&&(a[s]=o));return a}var s=["add","remove","toggle"],n={border:1,borderBottom:1,borderColor:1,borderLeft:1,borderRight:1,borderTop:1,borderWidth:1,margin:1,padding:1};t.each(["borderLeftStyle","borderRightStyle","borderBottomStyle","borderTopStyle"],function(e,i){t.fx.step[i]=function(t){("none"!==t.end&&!t.setAttr||1===t.pos&&!t.setAttr)&&(a.style(t.elem,i,t.end),t.setAttr=!0)}}),t.fn.addBack||(t.fn.addBack=function(t){return this.add(null==t?this.prevObject:this.prevObject.filter(t))}),t.effects.animateClass=function(n,o,a,r){var l=t.speed(o,a,r);return this.queue(function(){var o,a=t(this),r=a.attr("class")||"",h=l.children?a.find("*").addBack():a;h=h.map(function(){var i=t(this);return{el:i,start:e(this)}}),o=function(){t.each(s,function(t,e){n[e]&&a[e+"Class"](n[e])})},o(),h=h.map(function(){return this.end=e(this.el[0]),this.diff=i(this.start,this.end),this}),a.attr("class",r),h=h.map(function(){var e=this,i=t.Deferred(),s=t.extend({},l,{queue:!1,complete:function(){i.resolve(e)}});return this.el.animate(this.diff,s),i.promise()}),t.when.apply(t,h.get()).done(function(){o(),t.each(arguments,function(){var e=this.el;t.each(this.diff,function(t){e.css(t,"")})}),l.complete.call(a[0])})})},t.fn.extend({addClass:function(e){return function(i,s,n,o){return s?t.effects.animateClass.call(this,{add:i},s,n,o):e.apply(this,arguments)}}(t.fn.addClass),removeClass:function(e){return function(i,s,n,o){return arguments.length>1?t.effects.animateClass.call(this,{remove:i},s,n,o):e.apply(this,arguments)}}(t.fn.removeClass),toggleClass:function(e){return function(i,s,n,o,a){return"boolean"==typeof s||void 0===s?n?t.effects.animateClass.call(this,s?{add:i}:{remove:i},n,o,a):e.apply(this,arguments):t.effects.animateClass.call(this,{toggle:i},s,n,o)}}(t.fn.toggleClass),switchClass:function(e,i,s,n,o){return t.effects.animateClass.call(this,{add:i,remove:e},s,n,o)}})}(),function(){function e(e,i,s,n){return t.isPlainObject(e)&&(i=e,e=e.effect),e={effect:e},null==i&&(i={}),t.isFunction(i)&&(n=i,s=null,i={}),("number"==typeof i||t.fx.speeds[i])&&(n=s,s=i,i={}),t.isFunction(s)&&(n=s,s=null),i&&t.extend(e,i),s=s||i.duration,e.duration=t.fx.off?0:"number"==typeof s?s:s in t.fx.speeds?t.fx.speeds[s]:t.fx.speeds._default,e.complete=n||i.complete,e}function i(e){return!e||"number"==typeof e||t.fx.speeds[e]?!0:"string"!=typeof e||t.effects.effect[e]?t.isFunction(e)?!0:"object"!=typeof e||e.effect?!1:!0:!0}function a(t,e){var i=e.outerWidth(),s=e.outerHeight(),n=/^rect\((-?\d*\.?\d*px|-?\d+%|auto),?\s*(-?\d*\.?\d*px|-?\d+%|auto),?\s*(-?\d*\.?\d*px|-?\d+%|auto),?\s*(-?\d*\.?\d*px|-?\d+%|auto)\)$/,o=n.exec(t)||["",0,i,s,0];return{top:parseFloat(o[1])||0,right:"auto"===o[2]?i:parseFloat(o[2]),bottom:"auto"===o[3]?s:parseFloat(o[3]),left:parseFloat(o[4])||0}}t.expr&&t.expr.filters&&t.expr.filters.animated&&(t.expr.filters.animated=function(e){return function(i){return!!t(i).data(o)||e(i)}}(t.expr.filters.animated)),t.uiBackCompat!==!1&&t.extend(t.effects,{save:function(t,e){for(var i=0,n=e.length;n>i;i++)null!==e[i]&&t.data(s+e[i],t[0].style[e[i]])},restore:function(t,e){for(var i,n=0,o=e.length;o>n;n++)null!==e[n]&&(i=t.data(s+e[n]),t.css(e[n],i))},setMode:function(t,e){return"toggle"===e&&(e=t.is(":hidden")?"show":"hide"),e},createWrapper:function(e){if(e.parent().is(".ui-effects-wrapper"))return e.parent();var i={width:e.outerWidth(!0),height:e.outerHeight(!0),"float":e.css("float")},s=t("<div></div>").addClass("ui-effects-wrapper").css({fontSize:"100%",background:"transparent",border:"none",margin:0,padding:0}),n={width:e.width(),height:e.height()},o=document.activeElement;try{o.id}catch(a){o=document.body}return e.wrap(s),(e[0]===o||t.contains(e[0],o))&&t(o).trigger("focus"),s=e.parent(),"static"===e.css("position")?(s.css({position:"relative"}),e.css({position:"relative"})):(t.extend(i,{position:e.css("position"),zIndex:e.css("z-index")}),t.each(["top","left","bottom","right"],function(t,s){i[s]=e.css(s),isNaN(parseInt(i[s],10))&&(i[s]="auto")}),e.css({position:"relative",top:0,left:0,right:"auto",bottom:"auto"})),e.css(n),s.css(i).show()},removeWrapper:function(e){var i=document.activeElement;return e.parent().is(".ui-effects-wrapper")&&(e.parent().replaceWith(e),(e[0]===i||t.contains(e[0],i))&&t(i).trigger("focus")),e}}),t.extend(t.effects,{version:"1.12.1",define:function(e,i,s){return s||(s=i,i="effect"),t.effects.effect[e]=s,t.effects.effect[e].mode=i,s},scaledDimensions:function(t,e,i){if(0===e)return{height:0,width:0,outerHeight:0,outerWidth:0};var s="horizontal"!==i?(e||100)/100:1,n="vertical"!==i?(e||100)/100:1;return{height:t.height()*n,width:t.width()*s,outerHeight:t.outerHeight()*n,outerWidth:t.outerWidth()*s}},clipToBox:function(t){return{width:t.clip.right-t.clip.left,height:t.clip.bottom-t.clip.top,left:t.clip.left,top:t.clip.top}},unshift:function(t,e,i){var s=t.queue();e>1&&s.splice.apply(s,[1,0].concat(s.splice(e,i))),t.dequeue()},saveStyle:function(t){t.data(n,t[0].style.cssText)},restoreStyle:function(t){t[0].style.cssText=t.data(n)||"",t.removeData(n)},mode:function(t,e){var i=t.is(":hidden");return"toggle"===e&&(e=i?"show":"hide"),(i?"hide"===e:"show"===e)&&(e="none"),e},getBaseline:function(t,e){var i,s;switch(t[0]){case"top":i=0;break;case"middle":i=.5;break;case"bottom":i=1;break;default:i=t[0]/e.height}switch(t[1]){case"left":s=0;break;case"center":s=.5;break;case"right":s=1;break;default:s=t[1]/e.width}return{x:s,y:i}},createPlaceholder:function(e){var i,n=e.css("position"),o=e.position();return e.css({marginTop:e.css("marginTop"),marginBottom:e.css("marginBottom"),marginLeft:e.css("marginLeft"),marginRight:e.css("marginRight")}).outerWidth(e.outerWidth()).outerHeight(e.outerHeight()),/^(static|relative)/.test(n)&&(n="absolute",i=t("<"+e[0].nodeName+">").insertAfter(e).css({display:/^(inline|ruby)/.test(e.css("display"))?"inline-block":"block",visibility:"hidden",marginTop:e.css("marginTop"),marginBottom:e.css("marginBottom"),marginLeft:e.css("marginLeft"),marginRight:e.css("marginRight"),"float":e.css("float")}).outerWidth(e.outerWidth()).outerHeight(e.outerHeight()).addClass("ui-effects-placeholder"),e.data(s+"placeholder",i)),e.css({position:n,left:o.left,top:o.top}),i},removePlaceholder:function(t){var e=s+"placeholder",i=t.data(e);i&&(i.remove(),t.removeData(e))},cleanUp:function(e){t.effects.restoreStyle(e),t.effects.removePlaceholder(e)},setTransition:function(e,i,s,n){return n=n||{},t.each(i,function(t,i){var o=e.cssUnit(i);o[0]>0&&(n[i]=o[0]*s+o[1])}),n}}),t.fn.extend({effect:function(){function i(e){function i(){l.removeData(o),t.effects.cleanUp(l),"hide"===s.mode&&l.hide(),r()}function r(){t.isFunction(h)&&h.call(l[0]),t.isFunction(e)&&e()}var l=t(this);s.mode=u.shift(),t.uiBackCompat===!1||a?"none"===s.mode?(l[c](),r()):n.call(l[0],s,i):(l.is(":hidden")?"hide"===c:"show"===c)?(l[c](),r()):n.call(l[0],s,r)}var s=e.apply(this,arguments),n=t.effects.effect[s.effect],a=n.mode,r=s.queue,l=r||"fx",h=s.complete,c=s.mode,u=[],d=function(e){var i=t(this),s=t.effects.mode(i,c)||a;i.data(o,!0),u.push(s),a&&("show"===s||s===a&&"hide"===s)&&i.show(),a&&"none"===s||t.effects.saveStyle(i),t.isFunction(e)&&e()};return t.fx.off||!n?c?this[c](s.duration,h):this.each(function(){h&&h.call(this)}):r===!1?this.each(d).each(i):this.queue(l,d).queue(l,i)},show:function(t){return function(s){if(i(s))return t.apply(this,arguments);var n=e.apply(this,arguments);return n.mode="show",this.effect.call(this,n)}}(t.fn.show),hide:function(t){return function(s){if(i(s))return t.apply(this,arguments);var n=e.apply(this,arguments);return n.mode="hide",this.effect.call(this,n)}}(t.fn.hide),toggle:function(t){return function(s){if(i(s)||"boolean"==typeof s)return t.apply(this,arguments);var n=e.apply(this,arguments);return n.mode="toggle",this.effect.call(this,n)}}(t.fn.toggle),cssUnit:function(e){var i=this.css(e),s=[];return t.each(["em","px","%","pt"],function(t,e){i.indexOf(e)>0&&(s=[parseFloat(i),e])}),s},cssClip:function(t){return t?this.css("clip","rect("+t.top+"px "+t.right+"px "+t.bottom+"px "+t.left+"px)"):a(this.css("clip"),this)},transfer:function(e,i){var s=t(this),n=t(e.to),o="fixed"===n.css("position"),a=t("body"),r=o?a.scrollTop():0,l=o?a.scrollLeft():0,h=n.offset(),c={top:h.top-r,left:h.left-l,height:n.innerHeight(),width:n.innerWidth()},u=s.offset(),d=t("<div class='ui-effects-transfer'></div>").appendTo("body").addClass(e.className).css({top:u.top-r,left:u.left-l,height:s.innerHeight(),width:s.innerWidth(),position:o?"fixed":"absolute"}).animate(c,e.duration,e.easing,function(){d.remove(),t.isFunction(i)&&i()})}}),t.fx.step.clip=function(e){e.clipInit||(e.start=t(e.elem).cssClip(),"string"==typeof e.end&&(e.end=a(e.end,e.elem)),e.clipInit=!0),t(e.elem).cssClip({top:e.pos*(e.end.top-e.start.top)+e.start.top,right:e.pos*(e.end.right-e.start.right)+e.start.right,bottom:e.pos*(e.end.bottom-e.start.bottom)+e.start.bottom,left:e.pos*(e.end.left-e.start.left)+e.start.left})}}(),function(){var e={};t.each(["Quad","Cubic","Quart","Quint","Expo"],function(t,i){e[i]=function(e){return Math.pow(e,t+2)}}),t.extend(e,{Sine:function(t){return 1-Math.cos(t*Math.PI/2)},Circ:function(t){return 1-Math.sqrt(1-t*t)},Elastic:function(t){return 0===t||1===t?t:-Math.pow(2,8*(t-1))*Math.sin((80*(t-1)-7.5)*Math.PI/15)},Back:function(t){return t*t*(3*t-2)},Bounce:function(t){for(var e,i=4;((e=Math.pow(2,--i))-1)/11>t;);return 1/Math.pow(4,3-i)-7.5625*Math.pow((3*e-2)/22-t,2)}}),t.each(e,function(e,i){t.easing["easeIn"+e]=i,t.easing["easeOut"+e]=function(t){return 1-i(1-t)},t.easing["easeInOut"+e]=function(t){return.5>t?i(2*t)/2:1-i(-2*t+2)/2}})}(),t.effects,t.effects.define("fade","toggle",function(e,i){var s="show"===e.mode;t(this).css("opacity",s?0:1).animate({opacity:s?1:0},{queue:!1,duration:e.duration,easing:e.easing,complete:i})}),t.effects.define("slide","show",function(e,i){var s,n,o=t(this),a={up:["bottom","top"],down:["top","bottom"],left:["right","left"],right:["left","right"]},r=e.mode,l=e.direction||"left",h="up"===l||"down"===l?"top":"left",c="up"===l||"left"===l,u=e.distance||o["top"===h?"outerHeight":"outerWidth"](!0),d={};t.effects.createPlaceholder(o),s=o.cssClip(),n=o.position()[h],d[h]=(c?-1:1)*u+n,d.clip=o.cssClip(),d.clip[a[l][1]]=d.clip[a[l][0]],"show"===r&&(o.cssClip(d.clip),o.css(h,d[h]),d.clip=s,d[h]=n),o.animate(d,{queue:!1,duration:e.duration,easing:e.easing,complete:i})})});

/*! jQuery Validation Plugin - v1.17.0 - 7/29/2017
 * https://jqueryvalidation.org/
 * Copyright (c) 2017 Jörn Zaefferer; Licensed MIT */
!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):"object"==typeof module&&module.exports?module.exports=a(require("jquery")):a(jQuery)}(function(a){a.extend(a.fn,{validate:function(b){if(!this.length)return void(b&&b.debug&&window.console&&console.warn("Nothing selected, can't validate, returning nothing."));var c=a.data(this[0],"validator");return c?c:(this.attr("novalidate","novalidate"),c=new a.validator(b,this[0]),a.data(this[0],"validator",c),c.settings.onsubmit&&(this.on("click.validate",":submit",function(b){c.submitButton=b.currentTarget,a(this).hasClass("cancel")&&(c.cancelSubmit=!0),void 0!==a(this).attr("formnovalidate")&&(c.cancelSubmit=!0)}),this.on("submit.validate",function(b){function d(){var d,e;return c.submitButton&&(c.settings.submitHandler||c.formSubmitted)&&(d=a("<input type='hidden'/>").attr("name",c.submitButton.name).val(a(c.submitButton).val()).appendTo(c.currentForm)),!c.settings.submitHandler||(e=c.settings.submitHandler.call(c,c.currentForm,b),d&&d.remove(),void 0!==e&&e)}return c.settings.debug&&b.preventDefault(),c.cancelSubmit?(c.cancelSubmit=!1,d()):c.form()?c.pendingRequest?(c.formSubmitted=!0,!1):d():(c.focusInvalid(),!1)})),c)},valid:function(){var b,c,d;return a(this[0]).is("form")?b=this.validate().form():(d=[],b=!0,c=a(this[0].form).validate(),this.each(function(){b=c.element(this)&&b,b||(d=d.concat(c.errorList))}),c.errorList=d),b},rules:function(b,c){var d,e,f,g,h,i,j=this[0];if(null!=j&&(!j.form&&j.hasAttribute("contenteditable")&&(j.form=this.closest("form")[0],j.name=this.attr("name")),null!=j.form)){if(b)switch(d=a.data(j.form,"validator").settings,e=d.rules,f=a.validator.staticRules(j),b){case"add":a.extend(f,a.validator.normalizeRule(c)),delete f.messages,e[j.name]=f,c.messages&&(d.messages[j.name]=a.extend(d.messages[j.name],c.messages));break;case"remove":return c?(i={},a.each(c.split(/\s/),function(a,b){i[b]=f[b],delete f[b]}),i):(delete e[j.name],f)}return g=a.validator.normalizeRules(a.extend({},a.validator.classRules(j),a.validator.attributeRules(j),a.validator.dataRules(j),a.validator.staticRules(j)),j),g.required&&(h=g.required,delete g.required,g=a.extend({required:h},g)),g.remote&&(h=g.remote,delete g.remote,g=a.extend(g,{remote:h})),g}}}),a.extend(a.expr.pseudos||a.expr[":"],{blank:function(b){return!a.trim(""+a(b).val())},filled:function(b){var c=a(b).val();return null!==c&&!!a.trim(""+c)},unchecked:function(b){return!a(b).prop("checked")}}),a.validator=function(b,c){this.settings=a.extend(!0,{},a.validator.defaults,b),this.currentForm=c,this.init()},a.validator.format=function(b,c){return 1===arguments.length?function(){var c=a.makeArray(arguments);return c.unshift(b),a.validator.format.apply(this,c)}:void 0===c?b:(arguments.length>2&&c.constructor!==Array&&(c=a.makeArray(arguments).slice(1)),c.constructor!==Array&&(c=[c]),a.each(c,function(a,c){b=b.replace(new RegExp("\\{"+a+"\\}","g"),function(){return c})}),b)},a.extend(a.validator,{defaults:{messages:{},groups:{},rules:{},errorClass:"error",pendingClass:"pending",validClass:"valid",errorElement:"label",focusCleanup:!1,focusInvalid:!0,errorContainer:a([]),errorLabelContainer:a([]),onsubmit:!0,ignore:":hidden",ignoreTitle:!1,onfocusin:function(a){this.lastActive=a,this.settings.focusCleanup&&(this.settings.unhighlight&&this.settings.unhighlight.call(this,a,this.settings.errorClass,this.settings.validClass),this.hideThese(this.errorsFor(a)))},onfocusout:function(a){this.checkable(a)||!(a.name in this.submitted)&&this.optional(a)||this.element(a)},onkeyup:function(b,c){var d=[16,17,18,20,35,36,37,38,39,40,45,144,225];9===c.which&&""===this.elementValue(b)||a.inArray(c.keyCode,d)!==-1||(b.name in this.submitted||b.name in this.invalid)&&this.element(b)},onclick:function(a){a.name in this.submitted?this.element(a):a.parentNode.name in this.submitted&&this.element(a.parentNode)},highlight:function(b,c,d){"radio"===b.type?this.findByName(b.name).addClass(c).removeClass(d):a(b).addClass(c).removeClass(d)},unhighlight:function(b,c,d){"radio"===b.type?this.findByName(b.name).removeClass(c).addClass(d):a(b).removeClass(c).addClass(d)}},setDefaults:function(b){a.extend(a.validator.defaults,b)},messages:{required:"This field is required.",remote:"Please fix this field.",email:"Please enter a valid email address.",url:"Please enter a valid URL.",date:"Please enter a valid date.",dateISO:"Please enter a valid date (ISO).",number:"Please enter a valid number.",digits:"Please enter only digits.",equalTo:"Please enter the same value again.",maxlength:a.validator.format("Please enter no more than {0} characters."),minlength:a.validator.format("Please enter at least {0} characters."),rangelength:a.validator.format("Please enter a value between {0} and {1} characters long."),range:a.validator.format("Please enter a value between {0} and {1}."),max:a.validator.format("Please enter a value less than or equal to {0}."),min:a.validator.format("Please enter a value greater than or equal to {0}."),step:a.validator.format("Please enter a multiple of {0}.")},autoCreateRanges:!1,prototype:{init:function(){function b(b){!this.form&&this.hasAttribute("contenteditable")&&(this.form=a(this).closest("form")[0],this.name=a(this).attr("name"));var c=a.data(this.form,"validator"),d="on"+b.type.replace(/^validate/,""),e=c.settings;e[d]&&!a(this).is(e.ignore)&&e[d].call(c,this,b)}this.labelContainer=a(this.settings.errorLabelContainer),this.errorContext=this.labelContainer.length&&this.labelContainer||a(this.currentForm),this.containers=a(this.settings.errorContainer).add(this.settings.errorLabelContainer),this.submitted={},this.valueCache={},this.pendingRequest=0,this.pending={},this.invalid={},this.reset();var c,d=this.groups={};a.each(this.settings.groups,function(b,c){"string"==typeof c&&(c=c.split(/\s/)),a.each(c,function(a,c){d[c]=b})}),c=this.settings.rules,a.each(c,function(b,d){c[b]=a.validator.normalizeRule(d)}),a(this.currentForm).on("focusin.validate focusout.validate keyup.validate",":text, [type='password'], [type='file'], select, textarea, [type='number'], [type='search'], [type='tel'], [type='url'], [type='email'], [type='datetime'], [type='date'], [type='month'], [type='week'], [type='time'], [type='datetime-local'], [type='range'], [type='color'], [type='radio'], [type='checkbox'], [contenteditable], [type='button']",b).on("click.validate","select, option, [type='radio'], [type='checkbox']",b),this.settings.invalidHandler&&a(this.currentForm).on("invalid-form.validate",this.settings.invalidHandler)},form:function(){return this.checkForm(),a.extend(this.submitted,this.errorMap),this.invalid=a.extend({},this.errorMap),this.valid()||a(this.currentForm).triggerHandler("invalid-form",[this]),this.showErrors(),this.valid()},checkForm:function(){this.prepareForm();for(var a=0,b=this.currentElements=this.elements();b[a];a++)this.check(b[a]);return this.valid()},element:function(b){var c,d,e=this.clean(b),f=this.validationTargetFor(e),g=this,h=!0;return void 0===f?delete this.invalid[e.name]:(this.prepareElement(f),this.currentElements=a(f),d=this.groups[f.name],d&&a.each(this.groups,function(a,b){b===d&&a!==f.name&&(e=g.validationTargetFor(g.clean(g.findByName(a))),e&&e.name in g.invalid&&(g.currentElements.push(e),h=g.check(e)&&h))}),c=this.check(f)!==!1,h=h&&c,c?this.invalid[f.name]=!1:this.invalid[f.name]=!0,this.numberOfInvalids()||(this.toHide=this.toHide.add(this.containers)),this.showErrors(),a(b).attr("aria-invalid",!c)),h},showErrors:function(b){if(b){var c=this;a.extend(this.errorMap,b),this.errorList=a.map(this.errorMap,function(a,b){return{message:a,element:c.findByName(b)[0]}}),this.successList=a.grep(this.successList,function(a){return!(a.name in b)})}this.settings.showErrors?this.settings.showErrors.call(this,this.errorMap,this.errorList):this.defaultShowErrors()},resetForm:function(){a.fn.resetForm&&a(this.currentForm).resetForm(),this.invalid={},this.submitted={},this.prepareForm(),this.hideErrors();var b=this.elements().removeData("previousValue").removeAttr("aria-invalid");this.resetElements(b)},resetElements:function(a){var b;if(this.settings.unhighlight)for(b=0;a[b];b++)this.settings.unhighlight.call(this,a[b],this.settings.errorClass,""),this.findByName(a[b].name).removeClass(this.settings.validClass);else a.removeClass(this.settings.errorClass).removeClass(this.settings.validClass)},numberOfInvalids:function(){return this.objectLength(this.invalid)},objectLength:function(a){var b,c=0;for(b in a)void 0!==a[b]&&null!==a[b]&&a[b]!==!1&&c++;return c},hideErrors:function(){this.hideThese(this.toHide)},hideThese:function(a){a.not(this.containers).text(""),this.addWrapper(a).hide()},valid:function(){return 0===this.size()},size:function(){return this.errorList.length},focusInvalid:function(){if(this.settings.focusInvalid)try{a(this.findLastActive()||this.errorList.length&&this.errorList[0].element||[]).filter(":visible").focus().trigger("focusin")}catch(b){}},findLastActive:function(){var b=this.lastActive;return b&&1===a.grep(this.errorList,function(a){return a.element.name===b.name}).length&&b},elements:function(){var b=this,c={};return a(this.currentForm).find("input, select, textarea, [contenteditable]").not(":submit, :reset, :image, :disabled").not(this.settings.ignore).filter(function(){var d=this.name||a(this).attr("name");return!d&&b.settings.debug&&window.console&&console.error("%o has no name assigned",this),this.hasAttribute("contenteditable")&&(this.form=a(this).closest("form")[0],this.name=d),!(d in c||!b.objectLength(a(this).rules()))&&(c[d]=!0,!0)})},clean:function(b){return a(b)[0]},errors:function(){var b=this.settings.errorClass.split(" ").join(".");return a(this.settings.errorElement+"."+b,this.errorContext)},resetInternals:function(){this.successList=[],this.errorList=[],this.errorMap={},this.toShow=a([]),this.toHide=a([])},reset:function(){this.resetInternals(),this.currentElements=a([])},prepareForm:function(){this.reset(),this.toHide=this.errors().add(this.containers)},prepareElement:function(a){this.reset(),this.toHide=this.errorsFor(a)},elementValue:function(b){var c,d,e=a(b),f=b.type;return"radio"===f||"checkbox"===f?this.findByName(b.name).filter(":checked").val():"number"===f&&"undefined"!=typeof b.validity?b.validity.badInput?"NaN":e.val():(c=b.hasAttribute("contenteditable")?e.text():e.val(),"file"===f?"C:\\fakepath\\"===c.substr(0,12)?c.substr(12):(d=c.lastIndexOf("/"),d>=0?c.substr(d+1):(d=c.lastIndexOf("\\"),d>=0?c.substr(d+1):c)):"string"==typeof c?c.replace(/\r/g,""):c)},check:function(b){b=this.validationTargetFor(this.clean(b));var c,d,e,f,g=a(b).rules(),h=a.map(g,function(a,b){return b}).length,i=!1,j=this.elementValue(b);if("function"==typeof g.normalizer?f=g.normalizer:"function"==typeof this.settings.normalizer&&(f=this.settings.normalizer),f){if(j=f.call(b,j),"string"!=typeof j)throw new TypeError("The normalizer should return a string value.");delete g.normalizer}for(d in g){e={method:d,parameters:g[d]};try{if(c=a.validator.methods[d].call(this,j,b,e.parameters),"dependency-mismatch"===c&&1===h){i=!0;continue}if(i=!1,"pending"===c)return void(this.toHide=this.toHide.not(this.errorsFor(b)));if(!c)return this.formatAndAdd(b,e),!1}catch(k){throw this.settings.debug&&window.console&&console.log("Exception occurred when checking element "+b.id+", check the '"+e.method+"' method.",k),k instanceof TypeError&&(k.message+=".  Exception occurred when checking element "+b.id+", check the '"+e.method+"' method."),k}}if(!i)return this.objectLength(g)&&this.successList.push(b),!0},customDataMessage:function(b,c){return a(b).data("msg"+c.charAt(0).toUpperCase()+c.substring(1).toLowerCase())||a(b).data("msg")},customMessage:function(a,b){var c=this.settings.messages[a];return c&&(c.constructor===String?c:c[b])},findDefined:function(){for(var a=0;a<arguments.length;a++)if(void 0!==arguments[a])return arguments[a]},defaultMessage:function(b,c){"string"==typeof c&&(c={method:c});var d=this.findDefined(this.customMessage(b.name,c.method),this.customDataMessage(b,c.method),!this.settings.ignoreTitle&&b.title||void 0,a.validator.messages[c.method],"<strong>Warning: No message defined for "+b.name+"</strong>"),e=/\$?\{(\d+)\}/g;return"function"==typeof d?d=d.call(this,c.parameters,b):e.test(d)&&(d=a.validator.format(d.replace(e,"{$1}"),c.parameters)),d},formatAndAdd:function(a,b){var c=this.defaultMessage(a,b);this.errorList.push({message:c,element:a,method:b.method}),this.errorMap[a.name]=c,this.submitted[a.name]=c},addWrapper:function(a){return this.settings.wrapper&&(a=a.add(a.parent(this.settings.wrapper))),a},defaultShowErrors:function(){var a,b,c;for(a=0;this.errorList[a];a++)c=this.errorList[a],this.settings.highlight&&this.settings.highlight.call(this,c.element,this.settings.errorClass,this.settings.validClass),this.showLabel(c.element,c.message);if(this.errorList.length&&(this.toShow=this.toShow.add(this.containers)),this.settings.success)for(a=0;this.successList[a];a++)this.showLabel(this.successList[a]);if(this.settings.unhighlight)for(a=0,b=this.validElements();b[a];a++)this.settings.unhighlight.call(this,b[a],this.settings.errorClass,this.settings.validClass);this.toHide=this.toHide.not(this.toShow),this.hideErrors(),this.addWrapper(this.toShow).show()},validElements:function(){return this.currentElements.not(this.invalidElements())},invalidElements:function(){return a(this.errorList).map(function(){return this.element})},showLabel:function(b,c){var d,e,f,g,h=this.errorsFor(b),i=this.idOrName(b),j=a(b).attr("aria-describedby");h.length?(h.removeClass(this.settings.validClass).addClass(this.settings.errorClass),h.html(c)):(h=a("<"+this.settings.errorElement+">").attr("id",i+"-error").addClass(this.settings.errorClass).html(c||""),d=h,this.settings.wrapper&&(d=h.hide().show().wrap("<"+this.settings.wrapper+"/>").parent()),this.labelContainer.length?this.labelContainer.append(d):this.settings.errorPlacement?this.settings.errorPlacement.call(this,d,a(b)):d.insertAfter(b),h.is("label")?h.attr("for",i):0===h.parents("label[for='"+this.escapeCssMeta(i)+"']").length&&(f=h.attr("id"),j?j.match(new RegExp("\\b"+this.escapeCssMeta(f)+"\\b"))||(j+=" "+f):j=f,a(b).attr("aria-describedby",j),e=this.groups[b.name],e&&(g=this,a.each(g.groups,function(b,c){c===e&&a("[name='"+g.escapeCssMeta(b)+"']",g.currentForm).attr("aria-describedby",h.attr("id"))})))),!c&&this.settings.success&&(h.text(""),"string"==typeof this.settings.success?h.addClass(this.settings.success):this.settings.success(h,b)),this.toShow=this.toShow.add(h)},errorsFor:function(b){var c=this.escapeCssMeta(this.idOrName(b)),d=a(b).attr("aria-describedby"),e="label[for='"+c+"'], label[for='"+c+"'] *";return d&&(e=e+", #"+this.escapeCssMeta(d).replace(/\s+/g,", #")),this.errors().filter(e)},escapeCssMeta:function(a){return a.replace(/([\\!"#$%&'()*+,.\/:;<=>?@\[\]^`{|}~])/g,"\\$1")},idOrName:function(a){return this.groups[a.name]||(this.checkable(a)?a.name:a.id||a.name)},validationTargetFor:function(b){return this.checkable(b)&&(b=this.findByName(b.name)),a(b).not(this.settings.ignore)[0]},checkable:function(a){return/radio|checkbox/i.test(a.type)},findByName:function(b){return a(this.currentForm).find("[name='"+this.escapeCssMeta(b)+"']")},getLength:function(b,c){switch(c.nodeName.toLowerCase()){case"select":return a("option:selected",c).length;case"input":if(this.checkable(c))return this.findByName(c.name).filter(":checked").length}return b.length},depend:function(a,b){return!this.dependTypes[typeof a]||this.dependTypes[typeof a](a,b)},dependTypes:{"boolean":function(a){return a},string:function(b,c){return!!a(b,c.form).length},"function":function(a,b){return a(b)}},optional:function(b){var c=this.elementValue(b);return!a.validator.methods.required.call(this,c,b)&&"dependency-mismatch"},startRequest:function(b){this.pending[b.name]||(this.pendingRequest++,a(b).addClass(this.settings.pendingClass),this.pending[b.name]=!0)},stopRequest:function(b,c){this.pendingRequest--,this.pendingRequest<0&&(this.pendingRequest=0),delete this.pending[b.name],a(b).removeClass(this.settings.pendingClass),c&&0===this.pendingRequest&&this.formSubmitted&&this.form()?(a(this.currentForm).submit(),this.submitButton&&a("input:hidden[name='"+this.submitButton.name+"']",this.currentForm).remove(),this.formSubmitted=!1):!c&&0===this.pendingRequest&&this.formSubmitted&&(a(this.currentForm).triggerHandler("invalid-form",[this]),this.formSubmitted=!1)},previousValue:function(b,c){return c="string"==typeof c&&c||"remote",a.data(b,"previousValue")||a.data(b,"previousValue",{old:null,valid:!0,message:this.defaultMessage(b,{method:c})})},destroy:function(){this.resetForm(),a(this.currentForm).off(".validate").removeData("validator").find(".validate-equalTo-blur").off(".validate-equalTo").removeClass("validate-equalTo-blur")}},classRuleSettings:{required:{required:!0},email:{email:!0},url:{url:!0},date:{date:!0},dateISO:{dateISO:!0},number:{number:!0},digits:{digits:!0},creditcard:{creditcard:!0}},addClassRules:function(b,c){b.constructor===String?this.classRuleSettings[b]=c:a.extend(this.classRuleSettings,b)},classRules:function(b){var c={},d=a(b).attr("class");return d&&a.each(d.split(" "),function(){this in a.validator.classRuleSettings&&a.extend(c,a.validator.classRuleSettings[this])}),c},normalizeAttributeRule:function(a,b,c,d){/min|max|step/.test(c)&&(null===b||/number|range|text/.test(b))&&(d=Number(d),isNaN(d)&&(d=void 0)),d||0===d?a[c]=d:b===c&&"range"!==b&&(a[c]=!0)},attributeRules:function(b){var c,d,e={},f=a(b),g=b.getAttribute("type");for(c in a.validator.methods)"required"===c?(d=b.getAttribute(c),""===d&&(d=!0),d=!!d):d=f.attr(c),this.normalizeAttributeRule(e,g,c,d);return e.maxlength&&/-1|2147483647|524288/.test(e.maxlength)&&delete e.maxlength,e},dataRules:function(b){var c,d,e={},f=a(b),g=b.getAttribute("type");for(c in a.validator.methods)d=f.data("rule"+c.charAt(0).toUpperCase()+c.substring(1).toLowerCase()),this.normalizeAttributeRule(e,g,c,d);return e},staticRules:function(b){var c={},d=a.data(b.form,"validator");return d.settings.rules&&(c=a.validator.normalizeRule(d.settings.rules[b.name])||{}),c},normalizeRules:function(b,c){return a.each(b,function(d,e){if(e===!1)return void delete b[d];if(e.param||e.depends){var f=!0;switch(typeof e.depends){case"string":f=!!a(e.depends,c.form).length;break;case"function":f=e.depends.call(c,c)}f?b[d]=void 0===e.param||e.param:(a.data(c.form,"validator").resetElements(a(c)),delete b[d])}}),a.each(b,function(d,e){b[d]=a.isFunction(e)&&"normalizer"!==d?e(c):e}),a.each(["minlength","maxlength"],function(){b[this]&&(b[this]=Number(b[this]))}),a.each(["rangelength","range"],function(){var c;b[this]&&(a.isArray(b[this])?b[this]=[Number(b[this][0]),Number(b[this][1])]:"string"==typeof b[this]&&(c=b[this].replace(/[\[\]]/g,"").split(/[\s,]+/),b[this]=[Number(c[0]),Number(c[1])]))}),a.validator.autoCreateRanges&&(null!=b.min&&null!=b.max&&(b.range=[b.min,b.max],delete b.min,delete b.max),null!=b.minlength&&null!=b.maxlength&&(b.rangelength=[b.minlength,b.maxlength],delete b.minlength,delete b.maxlength)),b},normalizeRule:function(b){if("string"==typeof b){var c={};a.each(b.split(/\s/),function(){c[this]=!0}),b=c}return b},addMethod:function(b,c,d){a.validator.methods[b]=c,a.validator.messages[b]=void 0!==d?d:a.validator.messages[b],c.length<3&&a.validator.addClassRules(b,a.validator.normalizeRule(b))},methods:{required:function(b,c,d){if(!this.depend(d,c))return"dependency-mismatch";if("select"===c.nodeName.toLowerCase()){var e=a(c).val();return e&&e.length>0}return this.checkable(c)?this.getLength(b,c)>0:b.length>0},email:function(a,b){return this.optional(b)||/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(a)},url:function(a,b){return this.optional(b)||/^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})).?)(?::\d{2,5})?(?:[\/?#]\S*)?$/i.test(a)},date:function(a,b){return this.optional(b)||!/Invalid|NaN/.test(new Date(a).toString())},dateISO:function(a,b){return this.optional(b)||/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/.test(a)},number:function(a,b){return this.optional(b)||/^(?:-?\d+|-?\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(a)},digits:function(a,b){return this.optional(b)||/^\d+$/.test(a)},minlength:function(b,c,d){var e=a.isArray(b)?b.length:this.getLength(b,c);return this.optional(c)||e>=d},maxlength:function(b,c,d){var e=a.isArray(b)?b.length:this.getLength(b,c);return this.optional(c)||e<=d},rangelength:function(b,c,d){var e=a.isArray(b)?b.length:this.getLength(b,c);return this.optional(c)||e>=d[0]&&e<=d[1]},min:function(a,b,c){return this.optional(b)||a>=c},max:function(a,b,c){return this.optional(b)||a<=c},range:function(a,b,c){return this.optional(b)||a>=c[0]&&a<=c[1]},step:function(b,c,d){var e,f=a(c).attr("type"),g="Step attribute on input type "+f+" is not supported.",h=["text","number","range"],i=new RegExp("\\b"+f+"\\b"),j=f&&!i.test(h.join()),k=function(a){var b=(""+a).match(/(?:\.(\d+))?$/);return b&&b[1]?b[1].length:0},l=function(a){return Math.round(a*Math.pow(10,e))},m=!0;if(j)throw new Error(g);return e=k(d),(k(b)>e||l(b)%l(d)!==0)&&(m=!1),this.optional(c)||m},equalTo:function(b,c,d){var e=a(d);return this.settings.onfocusout&&e.not(".validate-equalTo-blur").length&&e.addClass("validate-equalTo-blur").on("blur.validate-equalTo",function(){a(c).valid()}),b===e.val()},remote:function(b,c,d,e){if(this.optional(c))return"dependency-mismatch";e="string"==typeof e&&e||"remote";var f,g,h,i=this.previousValue(c,e);return this.settings.messages[c.name]||(this.settings.messages[c.name]={}),i.originalMessage=i.originalMessage||this.settings.messages[c.name][e],this.settings.messages[c.name][e]=i.message,d="string"==typeof d&&{url:d}||d,h=a.param(a.extend({data:b},d.data)),i.old===h?i.valid:(i.old=h,f=this,this.startRequest(c),g={},g[c.name]=b,a.ajax(a.extend(!0,{mode:"abort",port:"validate"+c.name,dataType:"json",data:g,context:f.currentForm,success:function(a){var d,g,h,j=a===!0||"true"===a;f.settings.messages[c.name][e]=i.originalMessage,j?(h=f.formSubmitted,f.resetInternals(),f.toHide=f.errorsFor(c),f.formSubmitted=h,f.successList.push(c),f.invalid[c.name]=!1,f.showErrors()):(d={},g=a||f.defaultMessage(c,{method:e,parameters:b}),d[c.name]=i.message=g,f.invalid[c.name]=!0,f.showErrors(d)),i.valid=j,f.stopRequest(c,j)}},d)),"pending")}}});var b,c={};return a.ajaxPrefilter?a.ajaxPrefilter(function(a,b,d){var e=a.port;"abort"===a.mode&&(c[e]&&c[e].abort(),c[e]=d)}):(b=a.ajax,a.ajax=function(d){var e=("mode"in d?d:a.ajaxSettings).mode,f=("port"in d?d:a.ajaxSettings).port;return"abort"===e?(c[f]&&c[f].abort(),c[f]=b.apply(this,arguments),c[f]):b.apply(this,arguments)}),a});
/**
* jquery-match-height master by @liabru
* http://brm.io/jquery-match-height/
* License: MIT
*/

;(function(factory) { // eslint-disable-line no-extra-semi
    'use strict';
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery'], factory);
    } else if (typeof module !== 'undefined' && module.exports) {
        // CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Global
        factory(jQuery);
    }
})(function($) {
    /*
    *  internal
    */

    var _previousResizeWidth = -1,
        _updateTimeout = -1;

    /*
    *  _parse
    *  value parse utility function
    */

    var _parse = function(value) {
        // parse value and convert NaN to 0
        return parseFloat(value) || 0;
    };

    /*
    *  _rows
    *  utility function returns array of jQuery selections representing each row
    *  (as displayed after float wrapping applied by browser)
    */

    var _rows = function(elements) {
        var tolerance = 1,
            $elements = $(elements),
            lastTop = null,
            rows = [];

        // group elements by their top position
        $elements.each(function(){
            var $that = $(this),
                top = $that.offset().top - _parse($that.css('margin-top')),
                lastRow = rows.length > 0 ? rows[rows.length - 1] : null;

            if (lastRow === null) {
                // first item on the row, so just push it
                rows.push($that);
            } else {
                // if the row top is the same, add to the row group
                if (Math.floor(Math.abs(lastTop - top)) <= tolerance) {
                    rows[rows.length - 1] = lastRow.add($that);
                } else {
                    // otherwise start a new row group
                    rows.push($that);
                }
            }

            // keep track of the last row top
            lastTop = top;
        });

        return rows;
    };

    /*
    *  _parseOptions
    *  handle plugin options
    */

    var _parseOptions = function(options) {
        var opts = {
            byRow: true,
            property: 'height',
            target: null,
            remove: false
        };

        if (typeof options === 'object') {
            return $.extend(opts, options);
        }

        if (typeof options === 'boolean') {
            opts.byRow = options;
        } else if (options === 'remove') {
            opts.remove = true;
        }

        return opts;
    };

    /*
    *  matchHeight
    *  plugin definition
    */

    var matchHeight = $.fn.matchHeight = function(options) {
        var opts = _parseOptions(options);

        // handle remove
        if (opts.remove) {
            var that = this;

            // remove fixed height from all selected elements
            this.css(opts.property, '');

            // remove selected elements from all groups
            $.each(matchHeight._groups, function(key, group) {
                group.elements = group.elements.not(that);
            });

            // TODO: cleanup empty groups

            return this;
        }

        if (this.length <= 1 && !opts.target) {
            return this;
        }

        // keep track of this group so we can re-apply later on load and resize events
        matchHeight._groups.push({
            elements: this,
            options: opts
        });

        // match each element's height to the tallest element in the selection
        matchHeight._apply(this, opts);

        return this;
    };

    /*
    *  plugin global options
    */

    matchHeight.version = 'master';
    matchHeight._groups = [];
    matchHeight._throttle = 80;
    matchHeight._maintainScroll = false;
    matchHeight._beforeUpdate = null;
    matchHeight._afterUpdate = null;
    matchHeight._rows = _rows;
    matchHeight._parse = _parse;
    matchHeight._parseOptions = _parseOptions;

    /*
    *  matchHeight._apply
    *  apply matchHeight to given elements
    */

    matchHeight._apply = function(elements, options) {
        var opts = _parseOptions(options),
            $elements = $(elements),
            rows = [$elements];

        // take note of scroll position
        var scrollTop = $(window).scrollTop(),
            htmlHeight = $('html').outerHeight(true);

        // get hidden parents
        var $hiddenParents = $elements.parents().filter(':hidden');

        // cache the original inline style
        $hiddenParents.each(function() {
            var $that = $(this);
            $that.data('style-cache', $that.attr('style'));
        });

        // temporarily must force hidden parents visible
        $hiddenParents.css('display', 'block');

        // get rows if using byRow, otherwise assume one row
        if (opts.byRow && !opts.target) {

            // must first force an arbitrary equal height so floating elements break evenly
            $elements.each(function() {
                var $that = $(this),
                    display = $that.css('display');

                // temporarily force a usable display value
                if (display !== 'inline-block' && display !== 'flex' && display !== 'inline-flex') {
                    display = 'block';
                }

                // cache the original inline style
                $that.data('style-cache', $that.attr('style'));

                $that.css({
                    'display': display,
                    'padding-top': '0',
                    'padding-bottom': '0',
                    'margin-top': '0',
                    'margin-bottom': '0',
                    'border-top-width': '0',
                    'border-bottom-width': '0',
                    'height': '100px',
                    'overflow': 'hidden'
                });
            });

            // get the array of rows (based on element top position)
            rows = _rows($elements);

            // revert original inline styles
            $elements.each(function() {
                var $that = $(this);
                $that.attr('style', $that.data('style-cache') || '');
            });
        }

        $.each(rows, function(key, row) {
            var $row = $(row),
                targetHeight = 0;

            if (!opts.target) {
                // skip apply to rows with only one item
                if (opts.byRow && $row.length <= 1) {
                    $row.css(opts.property, '');
                    return;
                }

                // iterate the row and find the max height
                $row.each(function(){
                    var $that = $(this),
                        style = $that.attr('style'),
                        display = $that.css('display');

                    // temporarily force a usable display value
                    if (display !== 'inline-block' && display !== 'flex' && display !== 'inline-flex') {
                        display = 'block';
                    }

                    // ensure we get the correct actual height (and not a previously set height value)
                    var css = { 'display': display };
                    css[opts.property] = '';
                    $that.css(css);

                    // find the max height (including padding, but not margin)
                    if ($that.outerHeight(false) > targetHeight) {
                        targetHeight = $that.outerHeight(false);
                    }

                    // revert styles
                    if (style) {
                        $that.attr('style', style);
                    } else {
                        $that.css('display', '');
                    }
                });
            } else {
                // if target set, use the height of the target element
                targetHeight = opts.target.outerHeight(false);
            }

            // iterate the row and apply the height to all elements
            $row.each(function(){
                var $that = $(this),
                    verticalPadding = 0;

                // don't apply to a target
                if (opts.target && $that.is(opts.target)) {
                    return;
                }

                // handle padding and border correctly (required when not using border-box)
                if ($that.css('box-sizing') !== 'border-box') {
                    verticalPadding += _parse($that.css('border-top-width')) + _parse($that.css('border-bottom-width'));
                    verticalPadding += _parse($that.css('padding-top')) + _parse($that.css('padding-bottom'));
                }

                // set the height (accounting for padding and border)
                $that.css(opts.property, (targetHeight - verticalPadding) + 'px');
            });
        });

        // revert hidden parents
        $hiddenParents.each(function() {
            var $that = $(this);
            $that.attr('style', $that.data('style-cache') || null);
        });

        // restore scroll position if enabled
        if (matchHeight._maintainScroll) {
            $(window).scrollTop((scrollTop / htmlHeight) * $('html').outerHeight(true));
        }

        return this;
    };

    /*
    *  matchHeight._applyDataApi
    *  applies matchHeight to all elements with a data-match-height attribute
    */

    matchHeight._applyDataApi = function() {
        var groups = {};

        // generate groups by their groupId set by elements using data-match-height
        $('[data-match-height], [data-mh]').each(function() {
            var $this = $(this),
                groupId = $this.attr('data-mh') || $this.attr('data-match-height');

            if (groupId in groups) {
                groups[groupId] = groups[groupId].add($this);
            } else {
                groups[groupId] = $this;
            }
        });

        // apply matchHeight to each group
        $.each(groups, function() {
            this.matchHeight(true);
        });
    };

    /*
    *  matchHeight._update
    *  updates matchHeight on all current groups with their correct options
    */

    var _update = function(event) {
        if (matchHeight._beforeUpdate) {
            matchHeight._beforeUpdate(event, matchHeight._groups);
        }

        $.each(matchHeight._groups, function() {
            matchHeight._apply(this.elements, this.options);
        });

        if (matchHeight._afterUpdate) {
            matchHeight._afterUpdate(event, matchHeight._groups);
        }
    };

    matchHeight._update = function(throttle, event) {
        // prevent update if fired from a resize event
        // where the viewport width hasn't actually changed
        // fixes an event looping bug in IE8
        if (event && event.type === 'resize') {
            var windowWidth = $(window).width();
            if (windowWidth === _previousResizeWidth) {
                return;
            }
            _previousResizeWidth = windowWidth;
        }

        // throttle updates
        if (!throttle) {
            _update(event);
        } else if (_updateTimeout === -1) {
            _updateTimeout = setTimeout(function() {
                _update(event);
                _updateTimeout = -1;
            }, matchHeight._throttle);
        }
    };

    /*
    *  bind events
    */

    // apply on DOM ready event
    $(matchHeight._applyDataApi);

    // use on or bind where supported
    var on = $.fn.on ? 'on' : 'bind';

    // update heights on load and resize events
    $(window)[on]('load', function(event) {
        matchHeight._update(false, event);
    });

    // throttled update heights on resize events
    $(window)[on]('resize orientationchange', function(event) {
        matchHeight._update(true, event);
    });

});

var $ = jQuery.noConflict();

$.fn.inlineStyle = function (prop) {
    return this.prop("style")[$.camelCase(prop)];
};

$.fn.doOnce = function( func ) {
    this.length && func.apply( this );
    return this;
};

(function() {
    var lastTime = 0;
    var vendors = ['ms', 'moz', 'webkit', 'o'];
    for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
        window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
        window.cancelAnimationFrame = window[vendors[x]+'CancelAnimationFrame']
            || window[vendors[x]+'CancelRequestAnimationFrame'];
    }

    if (!window.requestAnimationFrame)
        window.requestAnimationFrame = function(callback, element) {
            var currTime = new Date().getTime();
            var timeToCall = Math.max(0, 16 - (currTime - lastTime));
            var id = window.setTimeout(function() { callback(currTime + timeToCall); },
                timeToCall);
            lastTime = currTime + timeToCall;
            return id;
        };

    if (!window.cancelAnimationFrame)
        window.cancelAnimationFrame = function(id) {
            clearTimeout(id);
        };
}());



function debounce(func, wait, immediate) {
    var timeout, args, context, timestamp, result;
    return function() {
        context = this;
        args = arguments;
        timestamp = new Date();
        var later = function() {
            var last = (new Date()) - timestamp;
            if (last < wait) {
                timeout = setTimeout(later, wait - last);
            } else {
                timeout = null;
                if (!immediate) result = func.apply(context, args);
            }
        };
        var callNow = immediate && !timeout;
        if (!timeout) {
            timeout = setTimeout(later, wait);
        }
        if (callNow) result = func.apply(context, args);
        return result;
    };
}


var requesting = false;

var killRequesting = debounce(function () {
    requesting = false;
}, 100);

function onScrollSliderParallax() {
    if (!requesting) {
        requesting = true;
        requestAnimationFrame(function(){
            SEMICOLON.slider.sliderParallax();
            SEMICOLON.slider.sliderElementsFade();
        });
    }
    killRequesting();
}



var SEMICOLON = SEMICOLON || {};

(function($){

    // USE STRICT
    "use strict";

    SEMICOLON.initialize = {

        init: function(){

            SEMICOLON.initialize.responsiveClasses();
            SEMICOLON.initialize.stickyElements();
            SEMICOLON.initialize.goToTop();
            SEMICOLON.initialize.lazyLoad();
            SEMICOLON.initialize.fullScreen();
            SEMICOLON.initialize.verticalMiddle();
            SEMICOLON.initialize.lightbox();
            SEMICOLON.initialize.resizeVideos();
            SEMICOLON.initialize.imageFade();
            SEMICOLON.initialize.pageTransition();
            SEMICOLON.initialize.dataResponsiveClasses();
            SEMICOLON.initialize.dataResponsiveHeights();
            SEMICOLON.initialize.stickFooterOnSmall();
            SEMICOLON.initialize.stickyFooter();

            $('.fslider').addClass('preloader2');

        },

        responsiveClasses: function(){

            if( typeof jRespond === 'undefined' ) {
                console.log('responsiveClasses: jRespond not Defined.');
                return true;
            }

            var jRes = jRespond([
                {
                    label: 'smallest',
                    enter: 0,
                    exit: 575
                },{
                    label: 'handheld',
                    enter: 576,
                    exit: 767
                },{
                    label: 'tablet',
                    enter: 768,
                    exit: 991
                },{
                    label: 'laptop',
                    enter: 992,
                    exit: 1199
                },{
                    label: 'desktop',
                    enter: 1200,
                    exit: 10000
                }
            ]);
            jRes.addFunc([
                {
                    breakpoint: 'desktop',
                    enter: function() { $body.addClass('device-xl'); },
                    exit: function() { $body.removeClass('device-xl'); }
                },{
                    breakpoint: 'laptop',
                    enter: function() { $body.addClass('device-lg'); },
                    exit: function() { $body.removeClass('device-lg'); }
                },{
                    breakpoint: 'tablet',
                    enter: function() { $body.addClass('device-md'); },
                    exit: function() { $body.removeClass('device-md'); }
                },{
                    breakpoint: 'handheld',
                    enter: function() { $body.addClass('device-sm'); },
                    exit: function() { $body.removeClass('device-sm'); }
                },{
                    breakpoint: 'smallest',
                    enter: function() { $body.addClass('device-xs'); },
                    exit: function() { $body.removeClass('device-xs'); }
                }
            ]);
        },

        verticalMiddle: function(){
            if( $verticalMiddleEl.length > 0 ) {
                $verticalMiddleEl.each( function(){
                    var element = $(this),
                        verticalMiddleH = element.outerHeight(),
                        headerHeight = $header.outerHeight();

                    if( element.parents('#slider').length > 0 && !element.hasClass('ignore-header') ) {
                        if( $header.hasClass('transparent-header') && ( $body.hasClass('device-xl') || $body.hasClass('device-lg') ) ) {
                            verticalMiddleH = verticalMiddleH - 70;
                            if( $slider.next('#header').length > 0 ) { verticalMiddleH = verticalMiddleH + headerHeight; }
                        }
                    }

                    if( $body.hasClass('device-sm') || $body.hasClass('device-xs') ) {
                        if( element.parents('.full-screen').length && !element.parents('.force-full-screen').length ){
                            if( element.children('.col-padding').length > 0 ) {
                                element.css({ position: 'relative', top: '0', width: 'auto', marginTop: '0' }).addClass('clearfix');
                            } else {
                                element.css({ position: 'relative', top: '0', width: 'auto', marginTop: '0', paddingTop: '60px', paddingBottom: '60px' }).addClass('clearfix');
                            }
                        } else {
                            element.css({ position: 'absolute', top: '50%', width: '100%', paddingTop: '0', paddingBottom: '0', marginTop: -(verticalMiddleH/2)+'px' });
                        }
                    } else {
                        element.css({ position: 'absolute', top: '50%', width: '100%', paddingTop: '0', paddingBottom: '0', marginTop: -(verticalMiddleH/2)+'px' });
                    }
                });
            }
        },

        stickyElements: function(){
            if( $siStickyEl.length > 0 ) {
                var siStickyH = $siStickyEl.outerHeight();
                $siStickyEl.css({ marginTop: -(siStickyH/2)+'px' });
            }

            if( $dotsMenuEl.length > 0 ) {
                var opmdStickyH = $dotsMenuEl.outerHeight();
                $dotsMenuEl.css({ marginTop: -(opmdStickyH/2)+'px' });
            }
        },

        goToTop: function(){
            var elementScrollSpeed = $goToTopEl.attr('data-speed'),
                elementScrollEasing = $goToTopEl.attr('data-easing');

            if( !elementScrollSpeed ) { elementScrollSpeed = 700; }
            if( !elementScrollEasing ) { elementScrollEasing = 'easeOutQuad'; }

            $goToTopEl.off( 'click' ).on( 'click', function() {
                $('body,html').stop(true).animate({
                    'scrollTop': 0
                }, Number( elementScrollSpeed ), elementScrollEasing );
                return false;
            });
        },

        goToTopScroll: function(){
            var elementMobile = $goToTopEl.attr('data-mobile'),
                elementOffset = $goToTopEl.attr('data-offset');

            if( !elementOffset ) { elementOffset = 450; }

            if( elementMobile != 'true' && ( $body.hasClass('device-sm') || $body.hasClass('device-xs') ) ) { return true; }

            if( $window.scrollTop() > Number(elementOffset) ) {
                $goToTopEl.fadeIn();
                $body.addClass('gototop-active');
            } else {
                $goToTopEl.fadeOut();
                $body.removeClass('gototop-active');
            }
        },

        fullScreen: function(){
            if( $fullScreenEl.length > 0 ) {
                $fullScreenEl.each( function(){
                    var element = $(this),
                        scrHeight = window.innerHeight ? window.innerHeight : $window.height(),
                        negativeHeight = element.attr('data-negative-height');

                    if( element.attr('id') == 'slider' ) {
                        var sliderHeightOff = $slider.offset().top;
                        scrHeight = scrHeight - sliderHeightOff;
                        if( element.find('.slider-parallax-inner').length > 0 ) {
                            var transformVal = element.find('.slider-parallax-inner').css('transform'),
                                transformX = transformVal.match(/-?[\d\.]+/g);
                            if( !transformX ) { var transformXvalue = 0; } else { var transformXvalue = transformX[5]; }
                            scrHeight = ( ( window.innerHeight ? window.innerHeight : $window.height() ) + Number( transformXvalue ) ) - sliderHeightOff;
                        }
                        if( $('#slider.with-header').next('#header:not(.transparent-header)').length > 0 && ( $body.hasClass('device-xl') || $body.hasClass('device-lg') ) ) {
                            var headerHeightOff = $header.outerHeight();
                            scrHeight = scrHeight - headerHeightOff;
                        }
                    }
                    if( element.parents('.full-screen').length > 0 ) { scrHeight = element.parents('.full-screen').height(); }

                    if( $body.hasClass('device-sm') || $body.hasClass('device-xs') ) {
                        if( !element.hasClass('force-full-screen') ){ scrHeight = 'auto'; }
                    }

                    if( negativeHeight ){ scrHeight = scrHeight - Number(negativeHeight); }

                    element.css('height', scrHeight);
                    if( element.attr('id') == 'slider' && !element.hasClass('canvas-slider-grid') ) { if( element.has('.swiper-slide') ) { element.find('.swiper-slide').css('height', scrHeight); } }
                });
            }
        },

        testimonialsGrid: function(){
            if( $testimonialsGridEl.length > 0 ) {
                if( $body.hasClass('device-md') || $body.hasClass('device-lg') || $body.hasClass('device-xl') ) {
                    var maxHeight = 0;
                    $testimonialsGridEl.each( function(){
                        $(this).find("li > .testimonial").each(function(){
                            if ($(this).height() > maxHeight) { maxHeight = $(this).height(); }
                        });
                        $(this).find("li").height(maxHeight);
                        maxHeight = 0;
                    });
                } else {
                    $testimonialsGridEl.find("li").css({ 'height': 'auto' });
                }
            }
        },

        lightbox: function(){

            if( !$().magnificPopup ) {
                console.log('lightbox: Magnific Popup not Defined.');
                return true;
            }

            var $lightboxImageEl = $('[data-lightbox="image"]'),
                $lightboxGalleryEl = $('[data-lightbox="gallery"]'),
                $lightboxIframeEl = $('[data-lightbox="iframe"]'),
                $lightboxInlineEl = $('[data-lightbox="inline"]'),
                $lightboxAjaxEl = $('[data-lightbox="ajax"]'),
                $lightboxAjaxGalleryEl = $('[data-lightbox="ajax-gallery"]');

            if( $lightboxImageEl.length > 0 ) {
                $lightboxImageEl.magnificPopup({
                    type: 'image',
                    closeOnContentClick: true,
                    closeBtnInside: false,
                    fixedContentPos: true,
                    mainClass: 'mfp-no-margins mfp-fade', // class to remove default margin from left and right side
                    image: {
                        verticalFit: true
                    }
                });
            }

            if( $lightboxGalleryEl.length > 0 ) {
                $lightboxGalleryEl.each(function() {
                    var element = $(this);

                    if( element.find('a[data-lightbox="gallery-item"]').parent('.clone').hasClass('clone') ) {
                        element.find('a[data-lightbox="gallery-item"]').parent('.clone').find('a[data-lightbox="gallery-item"]').attr('data-lightbox','');
                    }

                    if( element.find('a[data-lightbox="gallery-item"]').parents('.cloned').hasClass('cloned') ) {
                        element.find('a[data-lightbox="gallery-item"]').parents('.cloned').find('a[data-lightbox="gallery-item"]').attr('data-lightbox','');
                    }

                    element.magnificPopup({
                        delegate: 'a[data-lightbox="gallery-item"]',
                        type: 'image',
                        closeOnContentClick: true,
                        closeBtnInside: false,
                        fixedContentPos: true,
                        mainClass: 'mfp-no-margins mfp-fade', // class to remove default margin from left and right side
                        image: {
                            verticalFit: true
                        },
                        gallery: {
                            enabled: true,
                            navigateByImgClick: true,
                            preload: [0,1] // Will preload 0 - before current, and 1 after the current image
                        }
                    });
                });
            }

            if( $lightboxIframeEl.length > 0 ) {
                $lightboxIframeEl.magnificPopup({
                    disableOn: 600,
                    type: 'iframe',
                    removalDelay: 160,
                    preloader: false,
                    fixedContentPos: false
                });
            }

            if( $lightboxInlineEl.length > 0 ) {
                $lightboxInlineEl.magnificPopup({
                    type: 'inline',
                    mainClass: 'mfp-no-margins mfp-fade',
                    closeBtnInside: false,
                    fixedContentPos: true,
                    overflowY: 'scroll'
                });
            }

            if( $lightboxAjaxEl.length > 0 ) {
                $lightboxAjaxEl.magnificPopup({
                    type: 'ajax',
                    closeBtnInside: false,
                    autoFocusLast: false,
                    callbacks: {
                        ajaxContentAdded: function(mfpResponse) {
                            SEMICOLON.widget.loadFlexSlider();
                            SEMICOLON.initialize.resizeVideos();
                            SEMICOLON.widget.masonryThumbs();
                        },
                        open: function() {
                            $body.addClass('ohidden');
                        },
                        close: function() {
                            $body.removeClass('ohidden');
                        }
                    }
                });
            }

            if( $lightboxAjaxGalleryEl.length > 0 ) {
                $lightboxAjaxGalleryEl.magnificPopup({
                    delegate: 'a[data-lightbox="ajax-gallery-item"]',
                    type: 'ajax',
                    closeBtnInside: false,
                    autoFocusLast: false,
                    gallery: {
                        enabled: true,
                        preload: 0,
                        navigateByImgClick: false
                    },
                    callbacks: {
                        ajaxContentAdded: function(mfpResponse) {
                            SEMICOLON.widget.loadFlexSlider();
                            SEMICOLON.initialize.resizeVideos();
                            SEMICOLON.widget.masonryThumbs();
                        },
                        open: function() {
                            $body.addClass('ohidden');
                        },
                        close: function() {
                            $body.removeClass('ohidden');
                        }
                    }
                });
            }

            $('[data-lightbox]').on( 'mfpOpen', function(){
                var lightboxItem	= $.magnificPopup.instance.currItem.el,
                    lightboxClass	= $( lightboxItem ).attr('data-lightbox-class'),
                    lightboxBgClass	= $( lightboxItem ).attr('data-lightbox-bg-class');

                if( lightboxClass != '' ) {
                    $( $.magnificPopup.instance.container ).addClass( lightboxClass );
                }

                if( lightboxBgClass != '' ) {
                    $( $.magnificPopup.instance.bgOverlay ).addClass( lightboxBgClass );
                }
            });

        },

        modal: function(){

            if( !$().magnificPopup ) {
                console.log('modal: Magnific Popup not Defined.');
                return true;
            }

            if( Cookies === 'undefined' ) {
                console.log('cookieNotify: Cookie Function not defined.');
                return true;
            }

            var $modal = $('.modal-on-load:not(.customjs)');
            if( $modal.length > 0 ) {
                $modal.each( function(){
                    var element				= $(this),
                        elementTarget		= element.attr('data-target'),
                        elementTargetValue	= elementTarget.split('#')[1],
                        elementDelay		= element.attr('data-delay'),
                        elementTimeout		= element.attr('data-timeout'),
                        elementAnimateIn	= element.attr('data-animate-in'),
                        elementAnimateOut	= element.attr('data-animate-out');

                    if( !element.hasClass('enable-cookie') ) { Cookies.remove( elementTargetValue ); }

                    if( element.hasClass('enable-cookie') ) {
                        var elementCookie = Cookies.get( elementTargetValue );

                        if( typeof elementCookie !== 'undefined' && elementCookie == '0' ) {
                            return true;
                        }
                    }

                    if( !elementDelay ) {
                        elementDelay = 1500;
                    } else {
                        elementDelay = Number(elementDelay) + 1500;
                    }

                    var t = setTimeout(function() {
                        $.magnificPopup.open({
                            items: { src: elementTarget },
                            type: 'inline',
                            mainClass: 'mfp-no-margins mfp-fade',
                            closeBtnInside: false,
                            fixedContentPos: true,
                            removalDelay: 500,
                            callbacks: {
                                open: function(){
                                    if( elementAnimateIn != '' ) {
                                        $(elementTarget).addClass( elementAnimateIn + ' animated' );
                                    }
                                },
                                beforeClose: function(){
                                    if( elementAnimateOut != '' ) {
                                        $(elementTarget).removeClass( elementAnimateIn ).addClass( elementAnimateOut );
                                    }
                                },
                                afterClose: function() {
                                    if( elementAnimateIn != '' || elementAnimateOut != '' ) {
                                        $(elementTarget).removeClass( elementAnimateIn + ' ' + elementAnimateOut + ' animated' );
                                    }
                                    if( element.hasClass('enable-cookie') ) {
                                        Cookies.set( elementTargetValue, '0' );
                                    }
                                }
                            }
                        }, 0);
                    }, Number(elementDelay) );

                    if( elementTimeout != '' ) {
                        var to = setTimeout(function() {
                            $.magnificPopup.close();
                        }, Number(elementDelay) + Number(elementTimeout) );
                    }
                });
            }
        },

        resizeVideos: function(){

            if( !$().fitVids ) {
                console.log('resizeVideos: FitVids not Defined.');
                return true;
            }

            $("#content,#footer,.slider-element:not(.revslider-wrap),.landing-offer-media,.portfolio-ajax-modal,.mega-menu-column").fitVids({
                customSelector: "iframe[src^='//www.dailymotion.com/embed'], iframe[src*='maps.google.com'], iframe[src*='google.com/maps'], iframe[src*='facebook.com/plugins/video']",
                ignore: '.no-fv'
            });
        },

        imageFade: function(){
            $('.image_fade').hover( function(){
                $(this).filter(':not(:animated)').animate({opacity: 0.8}, 400);
            }, function() {
                $(this).animate({opacity: 1}, 400);
            });
        },

        blogTimelineEntries: function(){
            $('.post-timeline.grid-2').find('.entry').each( function(){
                var position = $(this).inlineStyle('left');
                if( position == '0px' ) {
                    $(this).removeClass('alt');
                } else {
                    $(this).addClass('alt');
                }
                $(this).find('.entry-timeline').fadeIn();
            });

            $('.entry.entry-date-section').next().next().find('.entry-timeline').css({ 'top': '70px' });
        },

        pageTransition: function(){
            if( $body.hasClass('no-transition') ) { return true; }

            if( !$().animsition ) {
                $body.addClass('no-transition');
                console.log('pageTransition: Animsition not Defined.');
                return true;
            }

            window.onpageshow = function(event) {
                if(event.persisted) {
                    window.location.reload();
                }
            };

            var animationIn = $body.attr('data-animation-in'),
                animationOut = $body.attr('data-animation-out'),
                durationIn = $body.attr('data-speed-in'),
                durationOut = $body.attr('data-speed-out'),
                timeOutActive = false,
                loaderTimeOut = $body.attr('data-loader-timeout'),
                loaderStyle = $body.attr('data-loader'),
                loaderColor = $body.attr('data-loader-color'),
                loaderHtml = $body.attr('data-loader-html'),
                loaderStyleHtml = '',
                loaderBefore = '<div class="css3-spinner">',
                loaderAfter = '</div>',
                loaderBgStyle = '',
                loaderBorderStyle = '',
                loaderBgClass = '',
                loaderBorderClass = '',
                loaderBgClass2 = '',
                loaderBorderClass2 = '';

            if( !animationIn ) { animationIn = 'fadeIn'; }
            if( !animationOut ) { animationOut = 'fadeOut'; }
            if( !durationIn ) { durationIn = 1500; }
            if( !durationOut ) { durationOut = 800; }
            if( !loaderHtml ) { loaderStyleHtml = '<div class="css3-spinner-bounce1"></div><div class="css3-spinner-bounce2"></div><div class="css3-spinner-bounce3"></div>'; }

            if( !loaderTimeOut ) {
                timeOutActive = false;
                loaderTimeOut = false;
            } else {
                timeOutActive = true;
                loaderTimeOut = Number(loaderTimeOut);
            }

            if( loaderColor ) {
                if( loaderColor == 'theme' ) {
                    loaderBgClass = ' bgcolor';
                    loaderBorderClass = ' border-color';
                    loaderBgClass2 = ' class="bgcolor"';
                    loaderBorderClass2 = ' class="border-color"';
                } else {
                    loaderBgStyle = ' style="background-color:'+ loaderColor +';"';
                    loaderBorderStyle = ' style="border-color:'+ loaderColor +';"';
                }
                loaderStyleHtml = '<div class="css3-spinner-bounce1'+ loaderBgClass +'"'+ loaderBgStyle +'></div><div class="css3-spinner-bounce2'+ loaderBgClass +'"'+ loaderBgStyle +'></div><div class="css3-spinner-bounce3'+ loaderBgClass +'"'+ loaderBgStyle +'></div>'
            }

            if( loaderStyle == '2' ) {
                loaderStyleHtml = '<div class="css3-spinner-flipper'+ loaderBgClass +'"'+ loaderBgStyle +'></div>';
            } else if( loaderStyle == '3' ) {
                loaderStyleHtml = '<div class="css3-spinner-double-bounce1'+ loaderBgClass +'"'+ loaderBgStyle +'></div><div class="css3-spinner-double-bounce2'+ loaderBgClass +'"'+ loaderBgStyle +'></div>';
            } else if( loaderStyle == '4' ) {
                loaderStyleHtml = '<div class="css3-spinner-rect1'+ loaderBgClass +'"'+ loaderBgStyle +'></div><div class="css3-spinner-rect2'+ loaderBgClass +'"'+ loaderBgStyle +'></div><div class="css3-spinner-rect3'+ loaderBgClass +'"'+ loaderBgStyle +'></div><div class="css3-spinner-rect4'+ loaderBgClass +'"'+ loaderBgStyle +'></div><div class="css3-spinner-rect5'+ loaderBgClass +'"'+ loaderBgStyle +'></div>';
            } else if( loaderStyle == '5' ) {
                loaderStyleHtml = '<div class="css3-spinner-cube1'+ loaderBgClass +'"'+ loaderBgStyle +'></div><div class="css3-spinner-cube2'+ loaderBgClass +'"'+ loaderBgStyle +'></div>';
            } else if( loaderStyle == '6' ) {
                loaderStyleHtml = '<div class="css3-spinner-scaler'+ loaderBgClass +'"'+ loaderBgStyle +'></div>';
            } else if( loaderStyle == '7' ) {
                loaderStyleHtml = '<div class="css3-spinner-grid-pulse"><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div></div>';
            } else if( loaderStyle == '8' ) {
                loaderStyleHtml = '<div class="css3-spinner-clip-rotate"><div'+ loaderBorderClass2 + loaderBorderStyle +'></div></div>';
            } else if( loaderStyle == '9' ) {
                loaderStyleHtml = '<div class="css3-spinner-ball-rotate"><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div></div>';
            } else if( loaderStyle == '10' ) {
                loaderStyleHtml = '<div class="css3-spinner-zig-zag"><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div></div>';
            } else if( loaderStyle == '11' ) {
                loaderStyleHtml = '<div class="css3-spinner-triangle-path"><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div></div>';
            } else if( loaderStyle == '12' ) {
                loaderStyleHtml = '<div class="css3-spinner-ball-scale-multiple"><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div></div>';
            } else if( loaderStyle == '13' ) {
                loaderStyleHtml = '<div class="css3-spinner-ball-pulse-sync"><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div><div'+ loaderBgClass2 + loaderBgStyle +'></div></div>';
            } else if( loaderStyle == '14' ) {
                loaderStyleHtml = '<div class="css3-spinner-scale-ripple"><div'+ loaderBorderClass2 + loaderBorderStyle +'></div><div'+ loaderBorderClass2 + loaderBorderStyle +'></div><div'+ loaderBorderClass2 + loaderBorderStyle +'></div></div>';
            }

            if( !loaderHtml ) {
                loaderHtml = loaderStyleHtml;
            }

            loaderHtml = loaderBefore + loaderHtml + loaderAfter;

            $wrapper.css({ 'opacity': 1 });

            $wrapper.animsition({
                inClass: animationIn,
                outClass: animationOut,
                inDuration: Number(durationIn),
                outDuration: Number(durationOut),
                linkElement: 'body:not(.device-md):not(.device-sm):not(.device-xs) #primary-menu:not(.on-click) ul li a:not([target="_blank"]):not([href*="#"]):not([data-lightbox]):not([href^="mailto"]):not([href^="tel"]):not([href^="sms"]):not([href^="call"])',
                loading: true,
                loadingParentElement: 'body',
                loadingClass: 'page-transition-wrap',
                loadingInner: loaderHtml,
                timeout: timeOutActive,
                timeoutCountdown: loaderTimeOut,
                onLoadEvent: true,
                browser: [ 'animation-duration', '-webkit-animation-duration'],
                overlay: false,
                overlayClass: 'animsition-overlay-slide',
                overlayParentElement: 'body'
            });

        },

        lazyLoad: function() {
            var lazyLoadEl = $('[data-lazyload]');

            if( !$().appear ) {
                console.log('lazyLoad: Appear not Defined.');
                return true;
            }

            if( lazyLoadEl.length > 0 ) {
                lazyLoadEl.each( function(){
                    var element = $(this),
                        elementImg = element.attr( 'data-lazyload' );

                    element.css({ 'background': '#FFF' });

                    element.appear(function () {
                        element.css({ 'background': 'none' }).removeAttr( 'width' ).removeAttr( 'height' ).attr('src', elementImg);
                    },{accX: 0, accY: 120},'easeInCubic');
                });
            }
        },

        topScrollOffset: function() {
            var topOffsetScroll = 0;

            if( ( $body.hasClass('device-xl') || $body.hasClass('device-lg') ) && !SEMICOLON.isMobile.any() ) {
                if( $header.hasClass('sticky-header') ) {
                    if( $pagemenu.hasClass('dots-menu') ) { topOffsetScroll = 100; } else { topOffsetScroll = 144; }
                } else {
                    if( $pagemenu.hasClass('dots-menu') ) { topOffsetScroll = 140; } else { topOffsetScroll = 184; }
                }

                if( !$pagemenu.length ) {
                    if( $header.hasClass('sticky-header') ) { topOffsetScroll = 100; } else { topOffsetScroll = 140; }
                }
            } else {
                topOffsetScroll = 40;
            }

            return topOffsetScroll;
        },

        defineColumns: function( element ){
            var column = 4,
                xlCol = element.attr('data-xl-col'),
                lgCol = element.attr('data-lg-col'),
                mdCol = element.attr('data-md-col'),
                smCol = element.attr('data-sm-col'),
                xsCol = element.attr('data-xs-col');

            if( element.hasClass('portfolio-full') ) {
                if( element.hasClass('portfolio-3') ) column = 3;
                else if( element.hasClass('portfolio-5') ) column = 5;
                else if( element.hasClass('portfolio-6') ) column = 6;
                else column = 4;

                if( $body.hasClass('device-md') && ( column == 4 || column == 5 || column == 6 ) ) {
                    column = 3;
                } else if( $body.hasClass('device-sm') && ( column == 3 || column == 4 || column == 5 || column == 6 ) ) {
                    column = 2;
                } else if( $body.hasClass('device-xs') ) {
                    column = 1;
                }

            } else if( element.hasClass('masonry-thumbs') ) {

                if( element.hasClass('grid-2') ) column = 2;
                else if( element.hasClass('grid-3') ) column = 3;
                else if( element.hasClass('grid-5') ) column = 5;
                else if( element.hasClass('grid-6') ) column = 6;
                else column = 4;

            }

            if( $body.hasClass('device-xl') ) {
                if( xlCol ) { column = Number(xlCol); }
            } else if( $body.hasClass('device-lg') ) {
                if( lgCol ) { column = Number(lgCol); }
            } else if( $body.hasClass('device-md') ) {
                if( mdCol ) { column = Number(mdCol); }
            } else if( $body.hasClass('device-sm') ) {
                if( smCol ) { column = Number(smCol); }
            } else if( $body.hasClass('device-xs') ) {
                if( xsCol ) { column = Number(xsCol); }
            }

            return column;
        },

        setFullColumnWidth: function( element ){

            if( !$().isotope ) {
                console.log('setFullColumnWidth: Isotope not Defined.');
                return true;
            }

            element.css({ 'width': '' });

            if( element.hasClass('portfolio-full') ) {
                var columns = SEMICOLON.initialize.defineColumns( element ),
                    containerWidth = element.width(),
                    postWidth = Math.floor(containerWidth/columns);
                if( $body.hasClass('device-xs') ) { var deviceSmallest = 1; } else { var deviceSmallest = 0; }
                element.find(".portfolio-item").each(function(index){
                    if( deviceSmallest == 0 && $(this).hasClass('wide') ) { var elementSize = ( postWidth*2 ); } else { var elementSize = postWidth; }
                    $(this).css({"width":elementSize+"px"});
                });
            } else if( element.hasClass('masonry-thumbs') ) {
                var columns = SEMICOLON.initialize.defineColumns( element ),
                    containerWidth = element.innerWidth();

                if( containerWidth == windowWidth ){
                    containerWidth = windowWidth*1.005;
                    element.css({ 'width': containerWidth+'px' });
                }

                var postWidth = (containerWidth/columns);

                postWidth = Math.floor(postWidth);

                if( ( postWidth * columns ) >= containerWidth ) { element.css({ 'margin-right': '-1px' }); }

                element.children('a').css({"width":postWidth+"px"});

                var firstElementWidth = element.find('a:eq(0)').outerWidth();

                element.isotope({
                    masonry: {
                        columnWidth: firstElementWidth
                    }
                });

                var bigImageNumbers = element.attr('data-big');
                if( bigImageNumbers ) {
                    bigImageNumbers = bigImageNumbers.split(",");
                    var bigImageNumber = '',
                        bigi = '';
                    for( bigi = 0; bigi < bigImageNumbers.length; bigi++ ){
                        bigImageNumber = Number(bigImageNumbers[bigi]) - 1;
                        element.find('a:eq('+bigImageNumber+')').css({ width: firstElementWidth*2 + 'px' });
                    }
                    var t = setTimeout( function(){
                        element.isotope('layout');
                    }, 1000 );
                }
            }

        },

        aspectResizer: function(){
            var $aspectResizerEl = $('.aspect-resizer');
            if( $aspectResizerEl.length > 0 ) {
                $aspectResizerEl.each( function(){
                    var element = $(this),
                        elementW = element.inlineStyle('width'),
                        elementH = element.inlineStyle('height'),
                        elementPW = element.parent().innerWidth();
                });
            }
        },

        dataResponsiveClasses: function(){
            var $dataClassXs = $('[data-class-xs]'),
                $dataClassSm = $('[data-class-sm]'),
                $dataClassMd = $('[data-class-md]'),
                $dataClassLg = $('[data-class-lg]'),
                $dataClassXl = $('[data-class-xl]');

            if( $dataClassXs.length > 0 ) {
                $dataClassXs.each( function(){
                    var element = $(this),
                        elementClass = element.attr('data-class-xs'),
                        elementClassDelete = element.attr('data-class-sm') + ' ' + element.attr('data-class-md') + ' ' + element.attr('data-class-lg') + ' ' + element.attr('data-class-xl');

                    if( $body.hasClass('device-xs') ) {
                        element.removeClass( elementClassDelete );
                        element.addClass( elementClass );
                    }
                });
            }

            if( $dataClassSm.length > 0 ) {
                $dataClassSm.each( function(){
                    var element = $(this),
                        elementClass = element.attr('data-class-sm'),
                        elementClassDelete = element.attr('data-class-xs') + ' ' + element.attr('data-class-md') + ' ' + element.attr('data-class-lg') + ' ' + element.attr('data-class-xl');

                    if( $body.hasClass('device-sm') ) {
                        element.removeClass( elementClassDelete );
                        element.addClass( elementClass );
                    }
                });
            }

            if( $dataClassMd.length > 0 ) {
                $dataClassMd.each( function(){
                    var element = $(this),
                        elementClass = element.attr('data-class-md'),
                        elementClassDelete = element.attr('data-class-xs') + ' ' + element.attr('data-class-sm') + ' ' + element.attr('data-class-lg') + ' ' + element.attr('data-class-xl');

                    if( $body.hasClass('device-md') ) {
                        element.removeClass( elementClassDelete );
                        element.addClass( elementClass );
                    }
                });
            }

            if( $dataClassLg.length > 0 ) {
                $dataClassLg.each( function(){
                    var element = $(this),
                        elementClass = element.attr('data-class-lg'),
                        elementClassDelete = element.attr('data-class-xs') + ' ' + element.attr('data-class-sm') + ' ' + element.attr('data-class-md') + ' ' + element.attr('data-class-xl');

                    if( $body.hasClass('device-lg') ) {
                        element.removeClass( elementClassDelete );
                        element.addClass( elementClass );
                    }
                });
            }

            if( $dataClassXl.length > 0 ) {
                $dataClassXl.each( function(){
                    var element = $(this),
                        elementClass = element.attr('data-class-xl'),
                        elementClassDelete = element.attr('data-class-xs') + ' ' + element.attr('data-class-sm') + ' ' + element.attr('data-class-md') + ' ' + element.attr('data-class-lg');

                    if( $body.hasClass('device-xl') ) {
                        element.removeClass( elementClassDelete );
                        element.addClass( elementClass );
                    }
                });
            }
        },

        dataResponsiveHeights: function(){
            var $dataHeightXs = $('[data-height-xs]'),
                $dataHeightSm = $('[data-height-sm]'),
                $dataHeightMd = $('[data-height-md]'),
                $dataHeightLg = $('[data-height-lg]'),
                $dataHeightXl = $('[data-height-xl]');

            if( $dataHeightXs.length > 0 ) {
                $dataHeightXs.each( function(){
                    var element = $(this),
                        elementHeight = element.attr('data-height-xs');

                    if( $body.hasClass('device-xs') ) {
                        if( elementHeight != '' ) { element.css( 'height', elementHeight ); }
                    }
                });
            }

            if( $dataHeightSm.length > 0 ) {
                $dataHeightSm.each( function(){
                    var element = $(this),
                        elementHeight = element.attr('data-height-sm');

                    if( $body.hasClass('device-sm') ) {
                        if( elementHeight != '' ) { element.css( 'height', elementHeight ); }
                    }
                });
            }

            if( $dataHeightMd.length > 0 ) {
                $dataHeightMd.each( function(){
                    var element = $(this),
                        elementHeight = element.attr('data-height-md');

                    if( $body.hasClass('device-md') ) {
                        if( elementHeight != '' ) { element.css( 'height', elementHeight ); }
                    }
                });
            }

            if( $dataHeightLg.length > 0 ) {
                $dataHeightLg.each( function(){
                    var element = $(this),
                        elementHeight = element.attr('data-height-lg');

                    if( $body.hasClass('device-lg') ) {
                        if( elementHeight != '' ) { element.css( 'height', elementHeight ); }
                    }
                });
            }

            if( $dataHeightXl.length > 0 ) {
                $dataHeightXl.each( function(){
                    var element = $(this),
                        elementHeight = element.attr('data-height-xl');

                    if( $body.hasClass('device-xl') ) {
                        if( elementHeight != '' ) { element.css( 'height', elementHeight ); }
                    }
                });
            }
        },

        stickFooterOnSmall: function(){
            $footer.css({ 'margin-top': '' });
            var windowH = $window.height(),
                wrapperH = $wrapper.height();

            if( !$body.hasClass('sticky-footer') && $footer.length > 0 && $wrapper.has('#footer') ) {
                if( windowH > wrapperH ) {
                    $footer.css({ 'margin-top': ( windowH - wrapperH ) });
                }
            }
        },

        stickyFooter: function(){
            if( $body.hasClass('sticky-footer') && $footer.length > 0 && ( $body.hasClass('device-xl') || $body.hasClass('device-lg') ) ) {
                var stickyFooter = $footer.outerHeight();
                $content.css({ 'margin-bottom': stickyFooter });
            } else {
                $content.css({ 'margin-bottom': 0 });
            }
        }

    };

    SEMICOLON.header = {

        init: function(){

            SEMICOLON.header.superfish();
            SEMICOLON.header.menufunctions();
            SEMICOLON.header.fullWidthMenu();
            SEMICOLON.header.overlayMenu();
            SEMICOLON.header.stickyMenu();
            SEMICOLON.header.stickyPageMenu();
            SEMICOLON.header.sideHeader();
            SEMICOLON.header.sidePanel();
            SEMICOLON.header.onePageScroll();
            SEMICOLON.header.onepageScroller();
            SEMICOLON.header.logo();
            SEMICOLON.header.topsearch();
            // SEMICOLON.header.topcart();

        },

        superfish: function(){

            if( $body.hasClass('device-xl') || $body.hasClass('device-lg') ) {
                $('#primary-menu ul ul, #primary-menu ul .mega-menu-content').css('display', 'block');
                SEMICOLON.header.menuInvert();
                $('#primary-menu ul ul, #primary-menu ul .mega-menu-content').css('display', '');

                if( !$().superfish ) {
                    $body.addClass('no-superfish');
                    console.log('superfish: Superfish not Defined.');
                    return true;
                }

                $('body:not(.side-header) #primary-menu:not(.on-click) > ul, body:not(.side-header) #primary-menu:not(.on-click) > div > ul:not(.dropdown-menu), .top-links:not(.on-click) > ul').superfish({
                    popUpSelector: 'ul,.mega-menu-content,.top-link-section',
                    delay: 250,
                    speed: 350,
                    animation: {opacity:'show'},
                    animationOut:  {opacity:'hide'},
                    cssArrows: false,
                    onShow: function(){
                        var megaMenuContent = $(this);
                        if( megaMenuContent.find('.owl-carousel.customjs').length > 0 ) {
                            megaMenuContent.find('.owl-carousel').removeClass('customjs');
                            SEMICOLON.widget.carousel();
                        }
                        if( megaMenuContent.find('.grid-container').length > 0 ) {
                            megaMenuContent.find('.grid-container').isotope('layout');
                        }
                    }
                });

                $('body.side-header #primary-menu:not(.on-click) > ul').superfish({
                    popUpSelector: 'ul',
                    delay: 250,
                    speed: 350,
                    animation: {opacity:'show',height:'show'},
                    animationOut:  {opacity:'hide',height:'hide'},
                    cssArrows: false
                });
            }

        },

        menuInvert: function() {

            $('#primary-menu .mega-menu-content, #primary-menu ul ul').each( function( index, element ){
                var $menuChildElement = $(element),
                    menuChildOffset = $menuChildElement.offset(),
                    menuChildWidth = $menuChildElement.width(),
                    menuChildLeft = menuChildOffset.left;

                if(windowWidth - (menuChildWidth + menuChildLeft) < 0) {
                    $menuChildElement.addClass('menu-pos-invert');
                }
            });

        },

        menufunctions: function(){

            $( '#primary-menu ul li:has(ul)' ).addClass('sub-menu');
            $( '.top-links ul li:has(ul) > a, #primary-menu.with-arrows > ul > li:has(ul) > a > div, #primary-menu.with-arrows > div > ul > li:has(ul) > a > div, #page-menu nav ul li:has(ul) > a > div' ).append( '<i class="icon-angle-down"></i>' );
            $( '.top-links > ul' ).addClass( 'clearfix' );

            if( $body.hasClass('device-xl') || $body.hasClass('device-lg') ) {
                $('#primary-menu.sub-title > ul > li').hover(function() {
                    $(this).prev().css({ backgroundImage : 'none' });
                }, function() {
                    $(this).prev().css({ backgroundImage : 'url("images/icons/menu-divider.png")' });
                });

                $('#primary-menu.sub-title').children('ul').children('.current').prev().css({ backgroundImage : 'none' });
            }

            if( $('#primary-menu').hasClass('on-click') || ( $body.hasClass('device-md') || $body.hasClass('device-sm') || $body.hasClass('device-xs') ) ) {
                $('#primary-menu li:has(ul) > a').on( 'click touchend', function(e){
                    $(this).parents('.sub-menu').siblings().find('ul,.mega-menu-content').removeClass('d-block');
                    $(this).parent('li').children('ul,.mega-menu-content').toggleClass('d-block');
                    e.preventDefault();
                });
            }

            if( $('.top-links').hasClass('on-click') || ( $body.hasClass('device-md') || $body.hasClass('device-sm') || $body.hasClass('device-xs') ) ) {
                $('.top-links li:has(ul,.top-link-section) > a').on( 'click touchend', function(e){
                    $(this).parents('li').siblings().find('ul,.top-link-section').removeClass('d-block');
                    $(this).parent('li').children('ul,.top-link-section').toggleClass('d-block');
                    e.preventDefault();
                });
            }

        },

        fullWidthMenu: function(){
            if( $body.hasClass('stretched') ) {
                if( $header.find('.container-fullwidth').length > 0 ) { $('.mega-menu .mega-menu-content').css({ 'width': $wrapper.width() - 120 }); }
                if( $header.hasClass('full-header') ) { $('.mega-menu .mega-menu-content').css({ 'width': $wrapper.width() - 60 }); }
            } else {
                if( $header.find('.container-fullwidth').length > 0 ) { $('.mega-menu .mega-menu-content').css({ 'width': $wrapper.width() - 120 }); }
                if( $header.hasClass('full-header') ) { $('.mega-menu .mega-menu-content').css({ 'width': $wrapper.width() - 80 }); }
            }
        },

        overlayMenu: function(){
            if( $body.hasClass('overlay-menu') ) {
                var overlayMenuItem = $('#primary-menu').children('ul').children('li'),
                    overlayMenuItemHeight = overlayMenuItem.outerHeight(),
                    overlayMenuItemTHeight = overlayMenuItem.length * overlayMenuItemHeight,
                    firstItemOffset = ( $window.height() - overlayMenuItemTHeight ) / 2;

                $('#primary-menu').children('ul').children('li:first-child').css({ 'margin-top': firstItemOffset+'px' });
            }
        },

        stickyMenu: function( headerOffset ){
            if ($window.scrollTop() > headerOffset) {
                if( $body.hasClass('device-xl') || $body.hasClass('device-lg') ) {
                    $('body:not(.side-header) #header:not(.no-sticky)').addClass('sticky-header');
                    if( !$headerWrap.hasClass('force-not-dark') ) { $headerWrap.removeClass('not-dark'); }
                    SEMICOLON.header.stickyMenuClass();
                } else if( $body.hasClass('device-sm') || $body.hasClass('device-xs') || $body.hasClass('device-md') ) {
                    if( $body.hasClass('sticky-responsive-menu') ) {
                        $('#header:not(.no-sticky)').addClass('responsive-sticky-header');
                        SEMICOLON.header.stickyMenuClass();
                    }
                }
            } else {
                SEMICOLON.header.removeStickyness();
            }
        },

        stickyPageMenu: function( pageMenuOffset ){
            if ($window.scrollTop() > pageMenuOffset) {
                if( $body.hasClass('device-xl') || $body.hasClass('device-lg') ) {
                    $('#page-menu:not(.dots-menu,.no-sticky)').addClass('sticky-page-menu');
                } else if( $body.hasClass('device-sm') || $body.hasClass('device-xs') || $body.hasClass('device-md') ) {
                    if( $body.hasClass('sticky-responsive-pagemenu') ) {
                        $('#page-menu:not(.dots-menu,.no-sticky)').addClass('sticky-page-menu');
                    }
                }
            } else {
                $('#page-menu:not(.dots-menu,.no-sticky)').removeClass('sticky-page-menu');
            }
        },

        removeStickyness: function(){
            if( $header.hasClass('sticky-header') ){
                $('body:not(.side-header) #header:not(.no-sticky)').removeClass('sticky-header');
                $header.removeClass().addClass(oldHeaderClasses);
                $headerWrap.removeClass().addClass(oldHeaderWrapClasses);
                if( !$headerWrap.hasClass('force-not-dark') ) { $headerWrap.removeClass('not-dark'); }
                SEMICOLON.slider.swiperSliderMenu();
                SEMICOLON.slider.revolutionSliderMenu();
            }
            if( $header.hasClass('responsive-sticky-header') ){
                $('body.sticky-responsive-menu #header').removeClass('responsive-sticky-header');
            }
            if( ( $body.hasClass('device-sm') || $body.hasClass('device-xs') || $body.hasClass('device-md') ) && ( typeof responsiveMenuClasses === 'undefined' ) ) {
                $header.removeClass().addClass(oldHeaderClasses);
                $headerWrap.removeClass().addClass(oldHeaderWrapClasses);
                if( !$headerWrap.hasClass('force-not-dark') ) { $headerWrap.removeClass('not-dark'); }
            }
        },

        sideHeader: function(){
            $("#header-trigger").off( 'click' ).on( 'click', function(){
                $('body.open-header').toggleClass("side-header-open");
                return false;
            });
        },

        sidePanel: function(){
            $(".side-panel-trigger").off( 'click' ).on( 'click', function(){
                $body.toggleClass("side-panel-open");
                if( $body.hasClass('device-touch') && $body.hasClass('side-push-panel') ) {
                    $body.toggleClass("ohidden");
                }
                return false;
            });
        },

        onePageScroll: function(){
            if( $onePageMenuEl.length > 0 ){
                var onePageSpeed = $onePageMenuEl.attr('data-speed'),
                    onePageOffset = $onePageMenuEl.attr('data-offset'),
                    onePageEasing = $onePageMenuEl.attr('data-easing');

                if( !onePageSpeed ) { onePageSpeed = 1000; }
                if( !onePageEasing ) { onePageEasing = 'easeOutQuad'; }

                $onePageMenuEl.find('a[data-href]').off( 'click' ).on( 'click', function(){
                    var element = $(this),
                        divScrollToAnchor = element.attr('data-href'),
                        divScrollSpeed = element.attr('data-speed'),
                        divScrollOffset = element.attr('data-offset'),
                        divScrollEasing = element.attr('data-easing');

                    if( $( divScrollToAnchor ).length > 0 ) {

                        if( !onePageOffset ) {
                            var onePageOffsetG = SEMICOLON.initialize.topScrollOffset();
                        } else {
                            var onePageOffsetG = onePageOffset;
                        }

                        if( !divScrollSpeed ) { divScrollSpeed = onePageSpeed; }
                        if( !divScrollOffset ) { divScrollOffset = onePageOffsetG; }
                        if( !divScrollEasing ) { divScrollEasing = onePageEasing; }

                        if( $onePageMenuEl.hasClass('no-offset') ) { divScrollOffset = 0; }

                        onePageGlobalOffset = Number(divScrollOffset);

                        $onePageMenuEl.find('li').removeClass('current');
                        $onePageMenuEl.find('a[data-href="' + divScrollToAnchor + '"]').parent('li').addClass('current');

                        if( windowWidth < 768 || $body.hasClass('overlay-menu') ) {
                            if( $('#primary-menu').find('ul.mobile-primary-menu').length > 0 ) {
                                $('#primary-menu > ul.mobile-primary-menu, #primary-menu > div > ul.mobile-primary-menu').toggleClass('d-block', false);
                            } else {
                                $('#primary-menu > ul, #primary-menu > div > ul').toggleClass('d-block', false);
                            }
                            $pagemenu.toggleClass('pagemenu-active', false);
                            $body.toggleClass('primary-menu-open', false);
                        }

                        $('html,body').stop(true).animate({
                            'scrollTop': $( divScrollToAnchor ).offset().top - Number(divScrollOffset)
                        }, Number(divScrollSpeed), divScrollEasing);

                        onePageGlobalOffset = Number(divScrollOffset);
                    }

                    return false;
                });
            }
        },

        onepageScroller: function(){
            $onePageMenuEl.find('li').removeClass('current');
            $onePageMenuEl.find('a[data-href="#' + SEMICOLON.header.onePageCurrentSection() + '"]').parent('li').addClass('current');
        },

        onePageCurrentSection: function(){
            var currentOnePageSection = 'home',
                headerHeight = $headerWrap.outerHeight();

            if( $body.hasClass('side-header') ) { headerHeight = 0; }

            $pageSectionEl.each(function(index) {
                var h = $(this).offset().top;
                var y = $window.scrollTop();

                var offsetScroll = headerHeight + onePageGlobalOffset;

                if( y + offsetScroll >= h && y < h + $(this).height() && $(this).attr('id') != currentOnePageSection ) {
                    currentOnePageSection = $(this).attr('id');
                }
            });

            return currentOnePageSection;
        },

        logo: function(){
            if( ( $header.hasClass('dark') || $body.hasClass('dark') ) && !$headerWrap.hasClass('not-dark') ) {
                if( defaultDarkLogo ){ defaultLogo.find('img').attr('src', defaultDarkLogo); }
                if( retinaDarkLogo ){ retinaLogo.find('img').attr('src', retinaDarkLogo); }
            } else {
                if( defaultLogoImg ){ defaultLogo.find('img').attr('src', defaultLogoImg); }
                if( retinaLogoImg ){ retinaLogo.find('img').attr('src', retinaLogoImg); }
            }
            if( $header.hasClass('sticky-header') ) {
                if( defaultStickyLogo ){ defaultLogo.find('img').attr('src', defaultStickyLogo); }
                if( retinaStickyLogo ){ retinaLogo.find('img').attr('src', retinaStickyLogo); }
            }
            if( $body.hasClass('device-sm') || $body.hasClass('device-xs') ) {
                if( defaultMobileLogo ){ defaultLogo.find('img').attr('src', defaultMobileLogo); }
                if( retinaMobileLogo ){ retinaLogo.find('img').attr('src', retinaMobileLogo); }
            }
        },

        stickyMenuClass: function(){
            if( stickyMenuClasses ) { var newClassesArray = stickyMenuClasses.split(/ +/); } else { var newClassesArray = ''; }
            var noOfNewClasses = newClassesArray.length;

            if( noOfNewClasses > 0 ) {
                var i = 0;
                for( i=0; i<noOfNewClasses; i++ ) {
                    if( newClassesArray[i] == 'not-dark' ) {
                        $header.removeClass('dark');
                        $headerWrap.addClass('not-dark');
                    } else if( newClassesArray[i] == 'dark' ) {
                        $headerWrap.removeClass('not-dark force-not-dark');
                        if( !$header.hasClass( newClassesArray[i] ) ) {
                            $header.addClass( newClassesArray[i] );
                        }
                    } else if( !$header.hasClass( newClassesArray[i] ) ) {
                        $header.addClass( newClassesArray[i] );
                    }
                }
            }
        },

        responsiveMenuClass: function(){
            if( $body.hasClass('device-sm') || $body.hasClass('device-xs') || $body.hasClass('device-md') ){
                if( responsiveMenuClasses ) { var newClassesArray = responsiveMenuClasses.split(/ +/); } else { var newClassesArray = ''; }
                var noOfNewClasses = newClassesArray.length;

                if( noOfNewClasses > 0 ) {
                    var i = 0;
                    for( i=0; i<noOfNewClasses; i++ ) {
                        if( newClassesArray[i] == 'not-dark' ) {
                            $header.removeClass('dark');
                            $headerWrap.addClass('not-dark');
                        } else if( newClassesArray[i] == 'dark' ) {
                            $headerWrap.removeClass('not-dark force-not-dark');
                            if( !$header.hasClass( newClassesArray[i] ) ) {
                                $header.addClass( newClassesArray[i] );
                            }
                        } else if( !$header.hasClass( newClassesArray[i] ) ) {
                            $header.addClass( newClassesArray[i] );
                        }
                    }
                }
                SEMICOLON.header.logo();
            }
        },

        topsocial: function(){
            if( $topSocialEl.length > 0 ){
                if( $body.hasClass('device-lg') || $body.hasClass('device-xl') ) {
                    $topSocialEl.show();
                    $topSocialEl.find('a').css({width: 40});

                    $topSocialEl.find('.ts-text').each( function(){
                        var $clone = $(this).clone().css({'visibility': 'hidden', 'display': 'inline-block', 'font-size': '13px', 'font-weight':'bold'}).appendTo($body),
                            cloneWidth = $clone.innerWidth() + 52;
                        $(this).parent('a').attr('data-hover-width',cloneWidth);
                        $clone.remove();
                    });

                    $topSocialEl.find('a').hover(function() {
                        if( $(this).find('.ts-text').length > 0 ) {
                            $(this).css({width: $(this).attr('data-hover-width')});
                        }
                    }, function() {
                        $(this).css({width: 40});
                    });
                } else {
                    $topSocialEl.show();
                    $topSocialEl.find('a').css({width: 40});

                    $topSocialEl.find('a').each(function() {
                        var topIconTitle = $(this).find('.ts-text').text();
                        $(this).attr('title', topIconTitle);
                    });

                    $topSocialEl.find('a').hover(function() {
                        $(this).css({width: 40});
                    }, function() {
                        $(this).css({width: 40});
                    });

                    if( $body.hasClass('device-xs') ) {
                        $topSocialEl.hide();
                        $topSocialEl.slice(0, 8).show();
                    }
                }
            }
        },

        topsearch: function(){

            $(document).on('click', function(event) {
                if (!$(event.target).closest('#top-search').length) { $body.toggleClass('top-search-open', false); }
                if (!$(event.target).closest('#top-cart').length) { $topCart.toggleClass('top-cart-open', false); }
                if (!$(event.target).closest('#page-menu').length) { $pagemenu.toggleClass('pagemenu-active', false); }
                if (!$(event.target).closest('#side-panel').length) { $body.toggleClass('side-panel-open', false); }
                if (!$(event.target).closest('#primary-menu').length) { $('#primary-menu.on-click > ul').find('.d-block').removeClass('d-block'); }
                if (!$(event.target).closest('#primary-menu.mobile-menu-off-canvas > ul').length) { $('#primary-menu.mobile-menu-off-canvas > ul').toggleClass('d-block', false); }
                if (!$(event.target).closest('#primary-menu.mobile-menu-off-canvas > div > ul').length) { $('#primary-menu.mobile-menu-off-canvas > div > ul').toggleClass('d-block', false); }
            });

            $("#top-search-trigger").off( 'click' ).on( 'click', function(e){
                $body.toggleClass('top-search-open');
                $topCart.toggleClass('top-cart-open', false);
                $( '#primary-menu > ul, #primary-menu > div > ul' ).toggleClass('d-block', false);
                $pagemenu.toggleClass('pagemenu-active', false);
                if ($body.hasClass('top-search-open')){
                    $topSearch.find('input').focus();
                }
                e.stopPropagation();
                e.preventDefault();
            });

        },

        topcart: function(){

            $("#top-cart-trigger").off( 'click' ).on( 'click', function(e){
                $pagemenu.toggleClass('pagemenu-active', false);
                $topCart.toggleClass('top-cart-open');
                e.stopPropagation();
                e.preventDefault();
            });

        }

    };

    SEMICOLON.slider = {

        init: function() {

            SEMICOLON.slider.sliderParallaxDimensions();
            SEMICOLON.slider.sliderRun();
            SEMICOLON.slider.sliderParallax();
            SEMICOLON.slider.sliderElementsFade();
            SEMICOLON.slider.captionPosition();

        },

        sliderParallaxDimensions: function(){
            if( $sliderParallaxEl.find('.slider-parallax-inner').length < 1 ) { return true; }

            if( $body.hasClass('device-xl') || $body.hasClass('device-lg') || $body.hasClass('device-md') ) {
                var parallaxElHeight = $sliderParallaxEl.outerHeight(),
                    parallaxElWidth = $sliderParallaxEl.outerWidth();

                if( $sliderParallaxEl.hasClass('revslider-wrap') || $sliderParallaxEl.find('.carousel-widget').length > 0 ) {
                    parallaxElHeight = $sliderParallaxEl.find('.slider-parallax-inner').children().first().outerHeight();
                    $sliderParallaxEl.height( parallaxElHeight );
                }

                $sliderParallaxEl.find('.slider-parallax-inner').height( parallaxElHeight );

                if( $body.hasClass('side-header') ) {
                    $sliderParallaxEl.find('.slider-parallax-inner').width( parallaxElWidth );
                }

                if( !$body.hasClass('stretched') ) {
                    parallaxElWidth = $wrapper.outerWidth();
                    $sliderParallaxEl.find('.slider-parallax-inner').width( parallaxElWidth );
                }
            } else {
                $sliderParallaxEl.find('.slider-parallax-inner').css({ 'width': '', height: '' });
            }

            if( swiperSlider != '' ) { swiperSlider.update(); }
        },

        sliderRun: function(){

            if( typeof Swiper === 'undefined' ) {
                console.log('sliderRun: Swiper not Defined.');
                return true;
            }

            var $sliderEl = $sliderElement.filter(':not(.customjs)');

            $sliderEl.each( function(){
                if( $(this).hasClass('swiper_wrapper') ) {

                    if( $(this).find('.swiper-slide').length < 1 ) { return true; }

                    var element = $(this).filter('.swiper_wrapper'),
                        elementDirection = element.attr('data-direction'),
                        elementSpeed = element.attr('data-speed'),
                        elementAutoPlay = element.attr('data-autoplay'),
                        elementLoop = element.attr('data-loop'),
                        elementEffect = element.attr('data-effect'),
                        elementGrabCursor = element.attr('data-grab'),
                        slideNumberTotal = element.find('.slide-number-total'),
                        slideNumberCurrent = element.find('.slide-number-current'),
                        sliderVideoAutoPlay = element.attr('data-video-autoplay'),
                        sliderSettings = element.attr('data-settings');

                    if( !elementSpeed ) { elementSpeed = 300; }
                    if( !elementDirection ) { elementDirection = 'horizontal'; }
                    if( elementAutoPlay ) { elementAutoPlay = Number( elementAutoPlay ); } else { elementAutoPlay = 999999999; }
                    if( elementLoop == 'true' ) { elementLoop = true; } else { elementLoop = false; }
                    if( !elementEffect ) { elementEffect = 'slide'; }
                    if( elementGrabCursor == 'false' ) { elementGrabCursor = false; } else { elementGrabCursor = true; }
                    if( sliderVideoAutoPlay == 'false' ) { sliderVideoAutoPlay = false; } else { sliderVideoAutoPlay = true; }

                    if( element.find('.swiper-pagination').length > 0 ) {
                        var elementPagination = element.find('.swiper-pagination'),
                            elementPaginationClickable = true;
                    } else {
                        var elementPagination = '',
                            elementPaginationClickable = false;
                    }

                    var elementNavNext = element.find('.slider-arrow-right'),
                        elementNavPrev = element.find('.slider-arrow-left'),
                        elementScollBar = element.find('.swiper-scrollbar');

                    swiperSlider = new Swiper( element.find('.swiper-parent') ,{
                        direction: elementDirection,
                        speed: Number( elementSpeed ),
                        autoplay: {
                            delay: elementAutoPlay
                        },
                        loop: elementLoop,
                        effect: elementEffect,
                        slidesPerView: 1,
                        grabCursor: elementGrabCursor,
                        pagination: {
                            el: elementPagination,
                            clickable: elementPaginationClickable
                        },
                        navigation: {
                            prevEl: elementNavPrev,
                            nextEl: elementNavNext
                        },
                        scrollbar: {
                            el: elementScollBar
                        },
                        on: {
                            init: function(swiper){
                                SEMICOLON.slider.sliderParallaxDimensions();
                                element.find('.yt-bg-player').attr('data-autoplay', 'false').removeClass('customjs');
                                SEMICOLON.widget.youtubeBgVideo();
                                $('.swiper-slide-active [data-animate]').each(function(){
                                    var $toAnimateElement = $(this),
                                        toAnimateDelay = $toAnimateElement.attr('data-delay'),
                                        toAnimateDelayTime = 0;
                                    if( toAnimateDelay ) { toAnimateDelayTime = Number( toAnimateDelay ) + 750; } else { toAnimateDelayTime = 750; }
                                    if( !$toAnimateElement.hasClass('animated') ) {
                                        $toAnimateElement.addClass('not-animated');
                                        var elementAnimation = $toAnimateElement.attr('data-animate');
                                        setTimeout(function() {
                                            $toAnimateElement.removeClass('not-animated').addClass( elementAnimation + ' animated');
                                        }, toAnimateDelayTime);
                                    }
                                });
                                element.find('[data-animate]').each(function(){
                                    var $toAnimateElement = $(this),
                                        elementAnimation = $toAnimateElement.attr('data-animate');
                                    if( $toAnimateElement.parents('.swiper-slide').hasClass('swiper-slide-active') ) { return true; }
                                    $toAnimateElement.removeClass('animated').removeClass(elementAnimation).addClass('not-animated');
                                });
                                SEMICOLON.slider.swiperSliderMenu();
                            },
                            transitionStart: function(swiper){
                                if( slideNumberCurrent.length > 0 ){
                                    if( elementLoop == true ) {
                                        slideNumberCurrent.html( Number( element.find('.swiper-slide.swiper-slide-active').attr('data-swiper-slide-index') ) + 1 );
                                    } else {
                                        slideNumberCurrent.html( swiperSlider.activeIndex + 1 );
                                    }
                                }
                                element.find('[data-animate]').each(function(){
                                    var $toAnimateElement = $(this),
                                        elementAnimation = $toAnimateElement.attr('data-animate');
                                    if( $toAnimateElement.parents('.swiper-slide').hasClass('swiper-slide-active') ) { return true; }
                                    $toAnimateElement.removeClass('animated').removeClass(elementAnimation).addClass('not-animated');
                                });
                                SEMICOLON.slider.swiperSliderMenu();
                            },
                            transitionEnd: function(swiper){
                                element.find('.swiper-slide').each(function(){
                                    var slideEl = $(this);
                                    if( slideEl.find('video').length > 0 && sliderVideoAutoPlay == true ) {
                                        slideEl.find('video').get(0).pause();
                                    }
                                    if( slideEl.find('.yt-bg-player.mb_YTPlayer:not(.customjs)').length > 0 ) {
                                        slideEl.find('.yt-bg-player.mb_YTPlayer:not(.customjs)').YTPPause();
                                    }
                                });
                                element.find('.swiper-slide:not(".swiper-slide-active")').each(function(){
                                    var slideEl = $(this);
                                    if( slideEl.find('video').length > 0 ) {
                                        if( slideEl.find('video').get(0).currentTime != 0 ) {
                                            slideEl.find('video').get(0).currentTime = 0;
                                        }
                                    }
                                    if( slideEl.find('.yt-bg-player.mb_YTPlayer:not(.customjs)').length > 0 ) {
                                        slideEl.find('.yt-bg-player.mb_YTPlayer:not(.customjs)').YTPSeekTo( slideEl.find('.yt-bg-player.mb_YTPlayer:not(.customjs)').attr('data-start') );
                                    }
                                });
                                if( element.find('.swiper-slide.swiper-slide-active').find('video').length > 0 && sliderVideoAutoPlay == true ) {
                                    element.find('.swiper-slide.swiper-slide-active').find('video').get(0).play();
                                }
                                if( element.find('.swiper-slide.swiper-slide-active').find('.yt-bg-player.mb_YTPlayer:not(.customjs)').length > 0 && sliderVideoAutoPlay == true ) {
                                    element.find('.swiper-slide.swiper-slide-active').find('.yt-bg-player.mb_YTPlayer:not(.customjs)').YTPPlay();
                                }

                                element.find('.swiper-slide.swiper-slide-active [data-animate]').each(function(){
                                    var $toAnimateElement = $(this),
                                        toAnimateDelay = $toAnimateElement.attr('data-delay'),
                                        toAnimateDelayTime = 0;
                                    if( toAnimateDelay ) { toAnimateDelayTime = Number( toAnimateDelay ) + 300; } else { toAnimateDelayTime = 300; }
                                    if( !$toAnimateElement.hasClass('animated') ) {
                                        $toAnimateElement.addClass('not-animated');
                                        var elementAnimation = $toAnimateElement.attr('data-animate');
                                        setTimeout(function() {
                                            $toAnimateElement.removeClass('not-animated').addClass( elementAnimation + ' animated');
                                        }, toAnimateDelayTime);
                                    }
                                });
                            }
                        }
                    });

                    if( slideNumberCurrent.length > 0 ) {
                        if( elementLoop == true ) {
                            slideNumberCurrent.html( swiperSlider.realIndex + 1 );
                        } else {
                            slideNumberCurrent.html( swiperSlider.activeIndex + 1 );
                        }
                    }

                    if( slideNumberTotal.length > 0 ) {
                        slideNumberTotal.html( element.find('.swiper-slide:not(.swiper-slide-duplicate)').length );
                    }

                    if( sliderSettings ){
                        sliderSettings = eval( '(' + sliderSettings + ')' );
                        for (var prop in sliderSettings) {
                            swiperSlider.params[prop] = sliderSettings[prop];
                            swiperSlider.update();
                        }
                    }

                }
            });
        },

        sliderParallaxOffset: function(){
            var sliderParallaxOffsetTop = 0;
            var headerHeight = $header.outerHeight();
            if( $body.hasClass('side-header') || $header.hasClass('transparent-header') ) { headerHeight = 0; }
            if( $pageTitle.length > 0 ) {
                var pageTitleHeight = $pageTitle.outerHeight();
                sliderParallaxOffsetTop = pageTitleHeight + headerHeight;
            } else {
                sliderParallaxOffsetTop = headerHeight;
            }

            if( $slider.next('#header').length > 0 ) { sliderParallaxOffsetTop = 0; }

            return sliderParallaxOffsetTop;
        },

        sliderParallax: function(){

            if( $sliderParallaxEl.length < 1 ) { return true; }

            var parallaxOffsetTop = SEMICOLON.slider.sliderParallaxOffset(),
                parallaxElHeight = $sliderParallaxEl.outerHeight();

            if( ( $body.hasClass('device-xl') || $body.hasClass('device-lg') ) && !SEMICOLON.isMobile.any() ) {
                if( ( parallaxElHeight + parallaxOffsetTop + 50 ) > $window.scrollTop() ){
                    $sliderParallaxEl.addClass('slider-parallax-visible').removeClass('slider-parallax-invisible');
                    if ($window.scrollTop() > parallaxOffsetTop) {
                        if( $sliderParallaxEl.find('.slider-parallax-inner').length > 0 ) {
                            var tranformAmount = (($window.scrollTop()-parallaxOffsetTop) *-.4 ).toFixed(0),
                                tranformAmount2 = (($window.scrollTop()-parallaxOffsetTop) *-.15 ).toFixed(0);
                            $sliderParallaxEl.find('.slider-parallax-inner').css({'transform':'translateY('+ tranformAmount +'px)'});
                            $('.slider-parallax .slider-caption,.ei-title').css({'transform':'translateY('+ tranformAmount2 +'px)'});
                        } else {
                            var tranformAmount = (($window.scrollTop()-parallaxOffsetTop) / 1.5 ).toFixed(0),
                                tranformAmount2 = (($window.scrollTop()-parallaxOffsetTop) / 7 ).toFixed(0);
                            $sliderParallaxEl.css({'transform':'translateY('+ tranformAmount +'px)'});
                            $('.slider-parallax .slider-caption,.ei-title').css({'transform':'translateY('+ -tranformAmount2 +'px)'});
                        }
                    } else {
                        if( $sliderParallaxEl.find('.slider-parallax-inner').length > 0 ) {
                            $('.slider-parallax-inner,.slider-parallax .slider-caption,.ei-title').css({'transform':'translateY(0px)'});
                        } else {
                            $('.slider-parallax,.slider-parallax .slider-caption,.ei-title').css({'transform':'translateY(0px)'});
                        }
                    }
                } else {
                    $sliderParallaxEl.addClass('slider-parallax-invisible').removeClass('slider-parallax-visible');
                }
                if(requesting) {
                    requestAnimationFrame(function(){
                        SEMICOLON.slider.sliderParallax();
                        SEMICOLON.slider.sliderElementsFade();
                    });
                }
            } else {
                if( $sliderParallaxEl.find('.slider-parallax-inner').length > 0 ) {
                    $('.slider-parallax-inner,.slider-parallax .slider-caption,.ei-title').css({'transform':'translateY(0px)'});
                } else {
                    $('.slider-parallax,.slider-parallax .slider-caption,.ei-title').css({'transform':'translateY(0px)'});
                }
            }
        },

        sliderElementsFade: function(){

            if( $sliderParallaxEl.length > 0 ) {
                if( ( $body.hasClass('device-xl') || $body.hasClass('device-lg') ) && !SEMICOLON.isMobile.any() ) {
                    var parallaxOffsetTop = SEMICOLON.slider.sliderParallaxOffset(),
                        parallaxElHeight = $sliderParallaxEl.outerHeight();
                    if( $slider.length > 0 ) {
                        if( $header.hasClass('transparent-header') || $('body').hasClass('side-header') ) { var tHeaderOffset = 100; } else { var tHeaderOffset = 0; }
                        $sliderParallaxEl.find('.slider-arrow-left,.slider-arrow-right,.vertical-middle:not(.no-fade),.slider-caption,.ei-title,.camera_prev,.camera_next').css({'opacity': 1 - ( ( ( $window.scrollTop() - tHeaderOffset ) *1.85 ) / parallaxElHeight ) });
                    }
                } else {
                    $sliderParallaxEl.find('.slider-arrow-left,.slider-arrow-right,.vertical-middle:not(.no-fade),.slider-caption,.ei-title,.camera_prev,.camera_next').css({'opacity': 1});
                }
            }
        },

        captionPosition: function(){
            $sliderElement.find('.slider-caption:not(.custom-caption-pos)').each(function(){
                var scapHeight = $(this).outerHeight();
                var scapSliderHeight = $sliderElement.outerHeight();
                if( $(this).parents('#slider').prev('#header').hasClass('transparent-header') && ( $body.hasClass('device-xl') || $body.hasClass('device-lg') ) ) {
                    if( $(this).parents('#slider').prev('#header').hasClass('floating-header') ) {
                        $(this).css({ top: ( scapSliderHeight + 160 - scapHeight ) / 2 + 'px' });
                    } else {
                        $(this).css({ top: ( scapSliderHeight + 100 - scapHeight ) / 2 + 'px' });
                    }
                } else {
                    $(this).css({ top: ( scapSliderHeight - scapHeight ) / 2 + 'px' });
                }
            });
        },

        swiperSliderMenu: function( onWinLoad ){
            onWinLoad = typeof onWinLoad !== 'undefined' ? onWinLoad : false;
            if( $body.hasClass('device-xl') || $body.hasClass('device-lg') || ( $header.hasClass('transparent-header-responsive') && !$body.hasClass('primary-menu-open') ) ) {
                var activeSlide = $slider.find('.swiper-slide.swiper-slide-active');
                SEMICOLON.slider.headerSchemeChanger(activeSlide, onWinLoad);
            }
        },

        revolutionSliderMenu: function( onWinLoad ){
            onWinLoad = typeof onWinLoad !== 'undefined' ? onWinLoad : false;
            if( $body.hasClass('device-xl') || $body.hasClass('device-lg') || ( $header.hasClass('transparent-header-responsive') && !$body.hasClass('primary-menu-open') ) ) {
                var activeSlide = $slider.find('.active-revslide');
                SEMICOLON.slider.headerSchemeChanger(activeSlide, onWinLoad);
            }
        },

        headerSchemeChanger: function( activeSlide, onWinLoad ){
            if( activeSlide.length > 0 ) {
                var darkExists = false;
                if( activeSlide.hasClass('dark') ){
                    if( oldHeaderClasses ) { var oldClassesArray = oldHeaderClasses.split(/ +/); } else { var oldClassesArray = ''; }
                    var noOfOldClasses = oldClassesArray.length;

                    if( noOfOldClasses > 0 ) {
                        var i = 0;
                        for( i=0; i<noOfOldClasses; i++ ) {
                            if( oldClassesArray[i] == 'dark' && onWinLoad == true ) {
                                darkExists = true;
                                break;
                            }
                        }
                    }
                    $('#header.transparent-header:not(.sticky-header,.semi-transparent,.floating-header)').addClass('dark');
                    if( !darkExists ) {
                        $('#header.transparent-header.sticky-header,#header.transparent-header.semi-transparent.sticky-header,#header.transparent-header.floating-header.sticky-header').removeClass('dark');
                    }
                    $headerWrap.removeClass('not-dark');
                } else {
                    if( $body.hasClass('dark') ) {
                        activeSlide.addClass('not-dark');
                        $('#header.transparent-header:not(.semi-transparent,.floating-header)').removeClass('dark');
                        $('#header.transparent-header:not(.sticky-header,.semi-transparent,.floating-header)').find('#header-wrap').addClass('not-dark');
                    } else {
                        $('#header.transparent-header:not(.semi-transparent,.floating-header)').removeClass('dark');
                        $headerWrap.removeClass('not-dark');
                    }
                }
                if( $header.hasClass('sticky-header') ) {
                    SEMICOLON.header.stickyMenuClass();
                }
                SEMICOLON.header.logo();
            }
        },

        owlCaptionInit: function(){
            if( $owlCarouselEl.length > 0 ){
                $owlCarouselEl.each( function(){
                    var element = $(this);
                    if( element.find('.owl-dot').length > 0 ) {
                        element.addClass('with-carousel-dots');
                    }
                });
            }
        }

    };

    SEMICOLON.portfolio = {

        init: function(){

            SEMICOLON.portfolio.ajaxload();

        },

        gridInit: function( $container ){

            if( !$().isotope ) {
                console.log('gridInit: Isotope not Defined.');
                return true;
            }

            if( $container.length < 1 ) { return true; }
            if( $container.hasClass('customjs') ) { return true; }

            $container.each( function(){
                var element = $(this),
                    elementTransition = element.attr('data-transition'),
                    elementLayoutMode = element.attr('data-layout'),
                    elementStagger = element.attr('data-stagger'),
                    elementOriginLeft = true;

                if( !elementTransition ) { elementTransition = '0.65s'; }
                if( !elementLayoutMode ) { elementLayoutMode = 'masonry'; }
                if( !elementStagger ) { elementStagger = 0; }
                if( $body.hasClass('rtl') ) { elementOriginLeft = false; }

                setTimeout( function(){
                    if( element.hasClass('portfolio') ){
                        element.isotope({
                            layoutMode: elementLayoutMode,
                            isOriginLeft: elementOriginLeft,
                            transitionDuration: elementTransition,
                            stagger: Number( elementStagger ),
                            masonry: {
                                columnWidth: element.find('.portfolio-item:not(.wide)')[0]
                            }
                        });
                    } else {
                        element.isotope({
                            layoutMode: elementLayoutMode,
                            isOriginLeft: elementOriginLeft,
                            transitionDuration: elementTransition
                        });
                    }
                }, 300);
            });
        },

        filterInit: function(){

            if( !$().isotope ) {
                console.log('filterInit: Isotope not Defined.');
                return true;
            }

            if( $portfolioFilter.length < 1 ) { return true; }
            if( $portfolioFilter.hasClass('customjs') ) { return true; }

            $portfolioFilter.each( function(){
                var element = $(this),
                    elementContainer = element.attr('data-container'),
                    elementActiveClass = element.attr('data-active-class'),
                    elementDefaultFilter = element.attr('data-default');

                if( !elementActiveClass ) { elementActiveClass = 'activeFilter'; }

                element.find('a').off( 'click' ).on( 'click', function(){
                    element.find('li').removeClass( elementActiveClass );
                    $(this).parent('li').addClass( elementActiveClass );
                    var selector = $(this).attr('data-filter');
                    $(elementContainer).isotope({ filter: selector });
                    return false;
                });

                if( elementDefaultFilter ) {
                    element.find('li').removeClass( elementActiveClass );
                    element.find('[data-filter="'+ elementDefaultFilter +'"]').parent('li').addClass( elementActiveClass );
                    $(elementContainer).isotope({ filter: elementDefaultFilter });
                }
            });
        },

        shuffleInit: function(){

            if( !$().isotope ) {
                console.log('shuffleInit: Isotope not Defined.');
                return true;
            }

            if( $('.portfolio-shuffle').length < 1 ) { return true; }

            $('.portfolio-shuffle').off( 'click' ).on( 'click', function(){
                var element = $(this),
                    elementContainer = element.attr('data-container');

                $(elementContainer).isotope('shuffle');
            });
        },

        portfolioDescMargin: function(){
            var $portfolioOverlayEl = $('.portfolio-overlay');
            if( $portfolioOverlayEl.length > 0 ){
                $portfolioOverlayEl.each(function() {
                    var element = $(this);
                    if( element.find('.portfolio-desc').length > 0 ) {
                        var portfolioOverlayHeight = element.outerHeight();
                        var portfolioOverlayDescHeight = element.find('.portfolio-desc').outerHeight();
                        if( element.find('a.left-icon').length > 0 || element.find('a.right-icon').length > 0 || element.find('a.center-icon').length > 0 ) {
                            var portfolioOverlayIconHeight = 40 + 20;
                        } else {
                            var portfolioOverlayIconHeight = 0;
                        }
                        var portfolioOverlayMiddleAlign = ( portfolioOverlayHeight - portfolioOverlayDescHeight - portfolioOverlayIconHeight ) / 2
                        element.find('.portfolio-desc').css({ 'margin-top': portfolioOverlayMiddleAlign });
                    }
                });
            }
        },

        arrange: function(){
            if( $portfolio.length > 0 ) {
                $portfolio.each( function(){
                    var element = $(this);
                    SEMICOLON.initialize.setFullColumnWidth( element );
                });
            }
        },

        ajaxload: function(){
            $('.portfolio-ajax .portfolio-item a.center-icon').off( 'click' ).on( 'click', function(e) {
                var portPostId = $(this).parents('.portfolio-item').attr('id');
                if( !$(this).parents('.portfolio-item').hasClass('portfolio-active') ) {
                    SEMICOLON.portfolio.loadItem(portPostId, prevPostPortId);
                }
                e.preventDefault();
            });
        },

        newNextPrev: function( portPostId ){
            var portNext = SEMICOLON.portfolio.getNextItem(portPostId);
            var portPrev = SEMICOLON.portfolio.getPrevItem(portPostId);
            $('#next-portfolio').attr('data-id', portNext);
            $('#prev-portfolio').attr('data-id', portPrev);
        },

        loadItem: function( portPostId, prevPostPortId, getIt ){
            if(!getIt) { getIt = false; }
            var portNext = SEMICOLON.portfolio.getNextItem(portPostId);
            var portPrev = SEMICOLON.portfolio.getPrevItem(portPostId);
            if(getIt == false) {
                SEMICOLON.portfolio.closeItem();
                $portfolioAjaxLoader.fadeIn();
                var portfolioDataLoader = $('#' + portPostId).attr('data-loader');
                $portfolioDetailsContainer.load(portfolioDataLoader, { portid: portPostId, portnext: portNext, portprev: portPrev },
                    function(){
                        SEMICOLON.portfolio.initializeAjax(portPostId);
                        SEMICOLON.portfolio.openItem();
                        $portfolioItems.removeClass('portfolio-active');
                        $('#' + portPostId).addClass('portfolio-active');
                    });
            }
        },

        closeItem: function(){
            if( $portfolioDetails && $portfolioDetails.height() > 32 ) {
                $portfolioAjaxLoader.fadeIn();
                $portfolioDetails.find('#portfolio-ajax-single').fadeOut('600', function(){
                    $(this).remove();
                });
                $portfolioDetails.removeClass('portfolio-ajax-opened');
            }
        },

        openItem: function(){
            var noOfImages = $portfolioDetails.find('img').length;
            var noLoaded = 0;

            if( noOfImages > 0 ) {
                $portfolioDetails.find('img').on('load', function(){
                    noLoaded++;
                    var topOffsetScroll = SEMICOLON.initialize.topScrollOffset();
                    if(noOfImages === noLoaded) {
                        $portfolioDetailsContainer.css({ 'display': 'block' });
                        $portfolioDetails.addClass('portfolio-ajax-opened');
                        $portfolioAjaxLoader.fadeOut();
                        var t=setTimeout(function(){
                            SEMICOLON.widget.loadFlexSlider();
                            SEMICOLON.initialize.lightbox();
                            SEMICOLON.initialize.resizeVideos();
                            SEMICOLON.widget.masonryThumbs();
                            $('html,body').stop(true).animate({
                                'scrollTop': $portfolioDetails.offset().top - topOffsetScroll
                            }, 900, 'easeOutQuad');
                        },500);
                    }
                });
            } else {
                var topOffsetScroll = SEMICOLON.initialize.topScrollOffset();
                $portfolioDetailsContainer.css({ 'display': 'block' });
                $portfolioDetails.addClass('portfolio-ajax-opened');
                $portfolioAjaxLoader.fadeOut();
                var t=setTimeout(function(){
                    SEMICOLON.widget.loadFlexSlider();
                    SEMICOLON.initialize.lightbox();
                    SEMICOLON.initialize.resizeVideos();
                    SEMICOLON.widget.masonryThumbs();
                    $('html,body').stop(true).animate({
                        'scrollTop': $portfolioDetails.offset().top - topOffsetScroll
                    }, 900, 'easeOutQuad');
                },500);
            }
        },

        getNextItem: function( portPostId ){
            var portNext = '';
            var hasNext = $('#' + portPostId).next();
            if(hasNext.length != 0) {
                portNext = hasNext.attr('id');
            }
            return portNext;
        },

        getPrevItem: function( portPostId ){
            var portPrev = '';
            var hasPrev = $('#' + portPostId).prev();
            if(hasPrev.length != 0) {
                portPrev = hasPrev.attr('id');
            }
            return portPrev;
        },

        initializeAjax: function( portPostId ){
            prevPostPortId = $('#' + portPostId);

            $('#next-portfolio, #prev-portfolio').off( 'click' ).on( 'click', function() {
                var portPostId = $(this).attr('data-id');
                $portfolioItems.removeClass('portfolio-active');
                $('#' + portPostId).addClass('portfolio-active');
                SEMICOLON.portfolio.loadItem(portPostId,prevPostPortId);
                return false;
            });

            $('#close-portfolio').off( 'click' ).on( 'click', function() {
                $portfolioDetailsContainer.fadeOut('600', function(){
                    $portfolioDetails.find('#portfolio-ajax-single').remove();
                });
                $portfolioDetails.removeClass('portfolio-ajax-opened');
                $portfolioItems.removeClass('portfolio-active');
                return false;
            });
        }

    };

    SEMICOLON.widget = {

        init: function(){

            SEMICOLON.widget.animations();
            SEMICOLON.widget.youtubeBgVideo();
            SEMICOLON.widget.tabs();
            SEMICOLON.widget.tabsJustify();
            SEMICOLON.widget.tabsResponsive();
            SEMICOLON.widget.tabsResponsiveResize();
            SEMICOLON.widget.toggles();
            SEMICOLON.widget.accordions();
            SEMICOLON.widget.progress();
            SEMICOLON.widget.navTree();
            SEMICOLON.widget.textRotater();
            SEMICOLON.widget.carousel();
            SEMICOLON.widget.linkScroll();
            SEMICOLON.widget.ajaxForm();
            SEMICOLON.widget.subscription();
            SEMICOLON.widget.stickySidebar();
            SEMICOLON.widget.cookieNotify();
            SEMICOLON.widget.cartQuantity();
            SEMICOLON.widget.extras();

        },

        parallax: function(){

            if( typeof skrollr !== 'undefined' && $.isFunction(skrollr) ) {
                console.log('parallax: skrollr not Defined.');
                return true;
            }

            if( $parallaxEl.length > 0 || $parallaxPageTitleEl.length > 0 || $parallaxPortfolioEl.length > 0 ) {
                if( !SEMICOLON.isMobile.any() ){
                    skrollr.init({forceHeight: false});
                } else {
                    $parallaxEl.addClass('mobile-parallax');
                    $parallaxPageTitleEl.addClass('mobile-parallax');
                    $parallaxPortfolioEl.addClass('mobile-parallax');
                }
            }
        },

        animations: function(){

            if( !$().appear ) {
                console.log('animations: Appear not Defined.');
                return true;
            }

            var $dataAnimateEl = $('[data-animate]');
            if( $dataAnimateEl.length > 0 ){
                if( $body.hasClass('device-xl') || $body.hasClass('device-lg') || $body.hasClass('device-md') ){
                    $dataAnimateEl.each(function(){
                        var element = $(this),
                            animationOut = element.attr('data-animate-out'),
                            animationDelay = element.attr('data-delay'),
                            animationDelayOut = element.attr('data-delay-out'),
                            animationDelayTime = 0,
                            animationDelayOutTime = 3000;

                        if( element.parents('.fslider.no-thumbs-animate').length > 0 ) { return true; }
                        if( element.parents('.swiper-slide').length > 0 ) { return true; }

                        if( animationDelay ) { animationDelayTime = Number( animationDelay ) + 500; } else { animationDelayTime = 500; }
                        if( animationOut && animationDelayOut ) { animationDelayOutTime = Number( animationDelayOut ) + animationDelayTime; }

                        if( !element.hasClass('animated') ) {
                            element.addClass('not-animated');
                            var elementAnimation = element.attr('data-animate');
                            element.appear(function () {
                                setTimeout(function() {
                                    element.removeClass('not-animated').addClass( elementAnimation + ' animated');
                                }, animationDelayTime);

                                if( animationOut ) {
                                    setTimeout( function() {
                                        element.removeClass( elementAnimation ).addClass( animationOut );
                                    }, animationDelayOutTime );
                                }
                            },{accX: 0, accY: -120},'easeInCubic');
                        }
                    });
                }
            }
        },

        loadFlexSlider: function(){

            if( !$().flexslider ) {
                console.log('loadFlexSlider: FlexSlider not Defined.');
                return true;
            }

            var $flexSliderEl = $('.fslider:not(.customjs)').find('.flexslider');
            if( $flexSliderEl.length > 0 ){
                $flexSliderEl.each(function() {
                    var $flexsSlider = $(this),
                        flexsAnimation = $flexsSlider.parent('.fslider').attr('data-animation'),
                        flexsEasing = $flexsSlider.parent('.fslider').attr('data-easing'),
                        flexsDirection = $flexsSlider.parent('.fslider').attr('data-direction'),
                        flexsReverse = $flexsSlider.parent('.fslider').attr('data-reverse'),
                        flexsSlideshow = $flexsSlider.parent('.fslider').attr('data-slideshow'),
                        flexsPause = $flexsSlider.parent('.fslider').attr('data-pause'),
                        flexsSpeed = $flexsSlider.parent('.fslider').attr('data-speed'),
                        flexsVideo = $flexsSlider.parent('.fslider').attr('data-video'),
                        flexsPagi = $flexsSlider.parent('.fslider').attr('data-pagi'),
                        flexsArrows = $flexsSlider.parent('.fslider').attr('data-arrows'),
                        flexsThumbs = $flexsSlider.parent('.fslider').attr('data-thumbs'),
                        flexsHover = $flexsSlider.parent('.fslider').attr('data-hover'),
                        flexsSheight = $flexsSlider.parent('.fslider').attr('data-smooth-height'),
                        flexsTouch = $flexsSlider.parent('.fslider').attr('data-touch'),
                        flexsUseCSS = false;

                    if( !flexsAnimation ) { flexsAnimation = 'slide'; }
                    if( !flexsEasing || flexsEasing == 'swing' ) {
                        flexsEasing = 'swing';
                        flexsUseCSS = true;
                    }
                    if( !flexsDirection ) { flexsDirection = 'horizontal'; }
                    if( flexsReverse == 'true' ) { flexsReverse = true; } else { flexsReverse = false; }
                    if( !flexsSlideshow ) { flexsSlideshow = true; } else { flexsSlideshow = false; }
                    if( !flexsPause ) { flexsPause = 5000; }
                    if( !flexsSpeed ) { flexsSpeed = 600; }
                    if( !flexsVideo ) { flexsVideo = false; }
                    if( flexsSheight == 'false' ) { flexsSheight = false; } else { flexsSheight = true; }
                    if( flexsDirection == 'vertical' ) { flexsSheight = false; }
                    if( flexsPagi == 'false' ) { flexsPagi = false; } else { flexsPagi = true; }
                    if( flexsThumbs == 'true' ) { flexsPagi = 'thumbnails'; } else { flexsPagi = flexsPagi; }
                    if( flexsArrows == 'false' ) { flexsArrows = false; } else { flexsArrows = true; }
                    if( flexsHover == 'false' ) { flexsHover = false; } else { flexsHover = true; }
                    if( flexsTouch == 'false' ) { flexsTouch = false; } else { flexsTouch = true; }

                    $flexsSlider.flexslider({
                        selector: ".slider-wrap > .slide",
                        animation: flexsAnimation,
                        easing: flexsEasing,
                        direction: flexsDirection,
                        reverse: flexsReverse,
                        slideshow: flexsSlideshow,
                        slideshowSpeed: Number(flexsPause),
                        animationSpeed: Number(flexsSpeed),
                        pauseOnHover: flexsHover,
                        video: flexsVideo,
                        controlNav: flexsPagi,
                        directionNav: flexsArrows,
                        smoothHeight: flexsSheight,
                        useCSS: flexsUseCSS,
                        touch: flexsTouch,
                        start: function(slider){
                            SEMICOLON.widget.animations();
                            SEMICOLON.initialize.verticalMiddle();
                            slider.parent().removeClass('preloader2');
                            var t = setTimeout( function(){ $('.grid-container').isotope('layout'); }, 1200 );
                            SEMICOLON.initialize.lightbox();
                            $('.flex-prev').html('<i class="icon-angle-left"></i>');
                            $('.flex-next').html('<i class="icon-angle-right"></i>');
                            SEMICOLON.portfolio.portfolioDescMargin();
                        },
                        after: function(){
                            if( $('.grid-container').hasClass('portfolio-full') ) {
                                $('.grid-container.portfolio-full').isotope('layout');
                                SEMICOLON.portfolio.portfolioDescMargin();
                            }
                            if( $('.post-grid').hasClass('post-masonry-full') ) {
                                $('.post-grid.post-masonry-full').isotope('layout');
                            }
                        }
                    });
                });
            }
        },

        html5Video: function(){
            var videoEl = $('.video-wrap:has(video)');
            if( videoEl.length > 0 ) {
                videoEl.each(function(){
                    var element = $(this),
                        elementVideo = element.find('video'),
                        divWidth = element.outerWidth(),
                        divHeight = element.outerHeight(),
                        videoWidth = ( (16*divHeight)/9 ),
                        videoHeight = divHeight;

                    if( videoWidth < divWidth ) {
                        videoWidth = divWidth;
                        videoHeight = ( (9*divWidth)/16 );
                    }

                    elementVideo.css({ width: videoWidth+'px', height: videoHeight+'px' });

                    if( videoHeight > divHeight ) {
                        elementVideo.css({ 'left': '', 'top': -( ( videoHeight - divHeight )/2 )+'px' });
                    }

                    if( videoWidth > divWidth ) {
                        elementVideo.css({ 'top': '', 'left': -( ( videoWidth - divWidth )/2 )+'px' });
                    }

                    if( SEMICOLON.isMobile.any() && !element.hasClass('no-placeholder') ) {
                        var placeholderImg = elementVideo.attr('poster');

                        if( placeholderImg != '' ) {
                            element.append('<div class="video-placeholder" style="background-image: url('+ placeholderImg +');"></div>')
                        }

                        elementVideo.hide();
                    }
                });
            }
        },

        youtubeBgVideo: function(){

            if( !$().YTPlayer ) {
                console.log('youtubeBgVideo: YoutubeBG Plugin not Defined.');
                return true;
            }

            var $youtubeBgPlayerEl = $('.yt-bg-player');
            if( $youtubeBgPlayerEl.hasClass('customjs') ) { return true; }

            if( $youtubeBgPlayerEl.length > 0 ){
                $youtubeBgPlayerEl.each( function(){
                    var element = $(this),
                        ytbgVideo = element.attr('data-video'),
                        ytbgMute = element.attr('data-mute'),
                        ytbgRatio = element.attr('data-ratio'),
                        ytbgQuality = element.attr('data-quality'),
                        ytbgOpacity = element.attr('data-opacity'),
                        ytbgContainer = element.attr('data-container'),
                        ytbgOptimize = element.attr('data-optimize'),
                        ytbgLoop = element.attr('data-loop'),
                        ytbgVolume = element.attr('data-volume'),
                        ytbgStart = element.attr('data-start'),
                        ytbgStop = element.attr('data-stop'),
                        ytbgAutoPlay = element.attr('data-autoplay'),
                        ytbgFullScreen = element.attr('data-fullscreen');

                    if( ytbgMute == 'false' ) { ytbgMute = false; } else { ytbgMute = true; }
                    if( !ytbgRatio ) { ytbgRatio = '16/9'; }
                    if( !ytbgQuality ) { ytbgQuality = 'hd720'; }
                    if( !ytbgOpacity ) { ytbgOpacity = 1; }
                    if( !ytbgContainer ) { ytbgContainer = 'self'; }
                    if( ytbgOptimize == 'false' ) { ytbgOptimize = false; } else { ytbgOptimize = true; }
                    if( ytbgLoop == 'false' ) { ytbgLoop = false; } else { ytbgLoop = true; }
                    if( !ytbgVolume ) { ytbgVolume = 1; }
                    if( !ytbgStart ) { ytbgStart = 0; }
                    if( !ytbgStop ) { ytbgStop = 0; }
                    if( ytbgAutoPlay == 'false' ) { ytbgAutoPlay = false; } else { ytbgAutoPlay = true; }
                    if( ytbgFullScreen == 'true' ) { ytbgFullScreen = true; } else { ytbgFullScreen = false; }

                    element.YTPlayer({
                        videoURL: ytbgVideo,
                        mute: ytbgMute,
                        ratio: ytbgRatio,
                        quality: ytbgQuality,
                        opacity: Number(ytbgOpacity),
                        containment: ytbgContainer,
                        optimizeDisplay: ytbgOptimize,
                        loop: ytbgLoop,
                        vol: Number(ytbgVolume),
                        startAt: Number(ytbgStart),
                        stopAt: Number(ytbgStop),
                        autoPlay: ytbgAutoPlay,
                        realfullscreen: ytbgFullScreen,
                        showYTLogo: false,
                        showControls: false
                    });
                });
            }
        },

        tabs: function(){

            if( !$().tabs ) {
                console.log('tabs: Tabs not Defined.');
                return true;
            }

            var $tabs = $('.tabs:not(.customjs)');
            if( $tabs.length > 0 ) {
                $tabs.each( function(){
                    var element = $(this),
                        elementSpeed = element.attr('data-speed'),
                        tabActive = element.attr('data-active');

                    if( !elementSpeed ) { elementSpeed = 400; }
                    if( !tabActive ) { tabActive = 0; } else { tabActive = tabActive - 1; }

                    var windowHash = window.location.hash;
                    if( jQuery(windowHash).length > 0 ) {
                        var windowHashText = windowHash.split('#'),
                            tabItem = document.getElementById( windowHashText[1] );
                        tabActive = jQuery( ".tab-content" ).index( tabItem );
                    }

                    element.tabs({
                        active: Number(tabActive),
                        show: {
                            effect: "fade",
                            duration: Number(elementSpeed)
                        }
                    });
                });
            }
        },

        tabsJustify: function(){
            if( !$('body').hasClass('device-xs') && !$('body').hasClass('device-sm') ){
                var $tabsJustify = $('.tabs.tabs-justify');
                if( $tabsJustify.length > 0 ) {
                    $tabsJustify.each( function(){
                        var element = $(this),
                            elementTabs = element.find('.tab-nav > li'),
                            elementTabsNo = elementTabs.length,
                            elementContainer = 0,
                            elementWidth = 0;

                        if( element.hasClass('tabs-bordered') || element.hasClass('tabs-bb') ) {
                            elementContainer = element.find('.tab-nav').outerWidth();
                        } else {
                            if( element.find('tab-nav').hasClass('tab-nav2') ) {
                                elementContainer = element.find('.tab-nav').outerWidth() - (elementTabsNo * 10);
                            } else {
                                elementContainer = element.find('.tab-nav').outerWidth() - 30;
                            }
                        }

                        elementWidth = Math.floor(elementContainer/elementTabsNo);
                        elementTabs.css({ 'width': elementWidth + 'px' });

                    });
                }
            } else { $('.tabs.tabs-justify').find('.tab-nav > li').css({ 'width': '' }); }
        },

        tabsResponsive: function(){

            if( !$().tabs ) {
                console.log('tabs: Tabs not Defined.');
                return true;
            }

            var $tabsResponsive = $('.tabs.tabs-responsive');
            if( $tabsResponsive.length < 1 ) { return true; }

            $tabsResponsive.each( function(){
                var element = $(this),
                    elementNav = element.find('.tab-nav'),
                    elementContent = element.find('.tab-container');

                elementNav.children('li').each( function(){
                    var navEl = $(this),
                        navElAnchor = navEl.children('a'),
                        navElTarget = navElAnchor.attr('href'),
                        navElContent = navElAnchor.html();

                    elementContent.find(navElTarget).before('<div class="acctitle d-none"><i class="acc-closed icon-ok-circle"></i><i class="acc-open icon-remove-circle"></i>'+navElContent+'</div>');
                });
            });
        },

        tabsResponsiveResize: function(){

            if( !$().tabs ) {
                console.log('tabs: Tabs not Defined.');
                return true;
            }

            var $tabsResponsive = $('.tabs.tabs-responsive');
            if( $tabsResponsive.length < 1 ) { return true; }

            $tabsResponsive.each( function(){
                var element = $(this),
                    tabActive = element.tabs( 'option', 'active' ) + 1,
                    elementAccStyle = element.attr('data-accordion-style');

                if( $('body').hasClass('device-sm') || $('body').hasClass('device-xs') ) {

                    element.find('.tab-nav').addClass('d-none');
                    element.find('.tab-container').addClass('accordion '+ elementAccStyle +' clearfix').attr('data-active', tabActive);
                    element.find('.tab-content').addClass('acc_content');
                    element.find('.acctitle').removeClass('d-none');
                    SEMICOLON.widget.accordions();

                } else if( $('body').hasClass('device-md') || $('body').hasClass('device-lg') || $('body').hasClass('device-xl') ) {

                    element.find('.tab-nav').removeClass('d-none');
                    element.find('.tab-container').removeClass('accordion '+ elementAccStyle +' clearfix').attr('data-active', '');
                    element.find('.tab-content').removeClass('acc_content');
                    element.find('.acctitle').addClass('d-none');
                    element.tabs('refresh');

                }
            });
        },

        toggles: function(){
            var $toggle = $('.toggle');
            if( $toggle.length > 0 ) {
                $toggle.each( function(){
                    var element = $(this),
                        elementState = element.attr('data-state');

                    if( elementState != 'open' ){
                        element.children('.togglec').hide();
                    } else {
                        element.children('.togglet').addClass("toggleta");
                    }

                    element.children('.togglet').off( 'click' ).on( 'click', function(){
                        $(this).toggleClass('toggleta').next('.togglec').slideToggle(300);
                        return true;
                    });
                });
            }
        },

        accordions: function(){
            var $accordionEl = $('.accordion');
            if( $accordionEl.length > 0 ){
                $accordionEl.each( function(){
                    var element = $(this),
                        elementState = element.attr('data-state'),
                        accordionActive = element.attr('data-active');

                    if( !accordionActive ) { accordionActive = 0; } else { accordionActive = accordionActive - 1; }

                    element.find('.acc_content').hide();

                    if( elementState != 'closed' ) {
                        element.find('.acctitle:eq('+ Number(accordionActive) +')').addClass('acctitlec').next().show();
                    }

                    element.find('.acctitle').off( 'click' ).on( 'click', function(){
                        if( $(this).next().is(':hidden') ) {
                            element.find('.acctitle').removeClass('acctitlec').next().slideUp("normal");
                            var clickTarget = $(this);
                            $(this).toggleClass('acctitlec').next().stop().slideDown("normal", function(){
                                if( ( $body.hasClass('device-sm') || $body.hasClass('device-xs') ) && element.hasClass('scroll-on-open') ) {
                                    $('html,body').stop(true).animate({
                                        'scrollTop': clickTarget.offset().top - ( SEMICOLON.initialize.topScrollOffset() - 40 )
                                    }, 800, 'easeOutQuad' );
                                }
                            });
                        }
                        return false;
                    });
                });
            }
        },

        progress: function(){

            if( !$().appear ) {
                console.log('progress: Appear not Defined.');
                return true;
            }

            var $progressEl = $('.progress');
            if( $progressEl.length > 0 ){
                $progressEl.each(function(){
                    var element = $(this),
                        skillsBar = element.parent('li'),
                        skillValue = skillsBar.attr('data-percent');

                    if( $body.hasClass('device-xl') || $body.hasClass('device-lg') ){
                        element.appear( function(){
                            if (!skillsBar.hasClass('skills-animated')) {
                                element.find('.counter-instant span').countTo();
                                skillsBar.find('.progress').css({width: skillValue + "%"}).addClass('skills-animated');
                            }
                        },{accX: 0, accY: -120},'easeInCubic');
                    } else {
                        element.find('.counter-instant span').countTo();
                        skillsBar.find('.progress').css({width: skillValue + "%"});
                    }
                });
            }
        },

        navTree: function(){
            var $navTreeEl = $('.nav-tree');
            if( $navTreeEl.length > 0 ){
                $navTreeEl.each( function(){
                    var element = $(this),
                        elementSpeed = element.attr('data-speed'),
                        elementEasing = element.attr('data-easing');

                    if( !elementSpeed ) { elementSpeed = 250; }
                    if( !elementEasing ) { elementEasing = 'swing'; }

                    element.find( 'ul li:has(ul)' ).addClass('sub-menu');
                    element.find( 'ul li:has(ul) > a' ).append( ' <i class="icon-angle-down"></i>' );

                    if( element.hasClass('on-hover') ){
                        element.find( 'ul li:has(ul):not(.active)' ).hover( function(e){
                            $(this).children('ul').stop(true, true).slideDown( Number(elementSpeed), elementEasing);
                        }, function(){
                            $(this).children('ul').delay(250).slideUp( Number(elementSpeed), elementEasing);
                        });
                    } else {
                        element.find( 'ul li:has(ul) > a' ).off( 'click' ).on( 'click', function(e){
                            var childElement = $(this);
                            element.find( 'ul li' ).not(childElement.parents()).removeClass('active');
                            childElement.parent().children('ul').slideToggle( Number(elementSpeed), elementEasing, function(){
                                $(this).find('ul').hide();
                                $(this).find('li.active').removeClass('active');
                            });
                            element.find( 'ul li > ul' ).not(childElement.parent().children('ul')).not(childElement.parents('ul')).slideUp( Number(elementSpeed), elementEasing );
                            childElement.parent('li:has(ul)').toggleClass('active');
                            e.preventDefault();
                        });
                    }
                });
            }
        },

        carousel: function(){

            if( !$().owlCarousel ) {
                console.log('carousel: Owl Carousel not Defined.');
                return true;
            }

            var $carousel = $('.carousel-widget:not(.customjs)');
            if( $carousel.length < 1 ){ return true; }

            $carousel.each( function(){
                var element = $(this),
                    elementItems = element.attr('data-items'),
                    elementItemsXl = element.attr('data-items-xl'),
                    elementItemsLg = element.attr('data-items-lg'),
                    elementItemsMd = element.attr('data-items-md'),
                    elementItemsSm = element.attr('data-items-sm'),
                    elementItemsXs = element.attr('data-items-xs'),
                    elementLoop = element.attr('data-loop'),
                    elementAutoPlay = element.attr('data-autoplay'),
                    elementSpeed = element.attr('data-speed'),
                    elementAnimateIn = element.attr('data-animate-in'),
                    elementAnimateOut = element.attr('data-animate-out'),
                    elementNav = element.attr('data-nav'),
                    elementPagi = element.attr('data-pagi'),
                    elementMargin = element.attr('data-margin'),
                    elementStage = element.attr('data-stage-padding'),
                    elementMerge = element.attr('data-merge'),
                    elementStart = element.attr('data-start'),
                    elementRewind = element.attr('data-rewind'),
                    elementSlideBy = element.attr('data-slideby'),
                    elementCenter = element.attr('data-center'),
                    elementLazy = element.attr('data-lazyload'),
                    elementVideo = element.attr('data-video'),
                    elementRTL = element.attr('data-rtl'),
                    elementAutoPlayTime = 5000,
                    elementAutoPlayHoverPause = true;

                if( !elementItems ) { elementItems = 4; }
                if( !elementItemsXl ) { elementItemsXl = Number(elementItems); }
                if( !elementItemsLg ) { elementItemsLg = Number(elementItemsXl); }
                if( !elementItemsMd ) { elementItemsMd = Number(elementItemsLg); }
                if( !elementItemsSm ) { elementItemsSm = Number(elementItemsMd); }
                if( !elementItemsXs ) { elementItemsXs = Number(elementItemsSm); }
                if( !elementSpeed ) { elementSpeed = 250; }
                if( !elementMargin ) { elementMargin = 20; }
                if( !elementStage ) { elementStage = 0; }
                if( !elementStart ) { elementStart = 0; }

                if( !elementSlideBy ) { elementSlideBy = 1; }
                if( elementSlideBy == 'page' ) {
                    elementSlideBy = 'page';
                } else {
                    elementSlideBy = Number(elementSlideBy);
                }

                if( elementLoop == 'true' ){ elementLoop = true; } else { elementLoop = false; }
                if( !elementAutoPlay ){
                    elementAutoPlay = false;
                    elementAutoPlayHoverPause = false;
                } else {
                    elementAutoPlayTime = Number(elementAutoPlay);
                    elementAutoPlay = true;
                }
                if( !elementAnimateIn ) { elementAnimateIn = false; }
                if( !elementAnimateOut ) { elementAnimateOut = false; }
                if( elementNav == 'false' ){ elementNav = false; } else { elementNav = true; }
                if( elementPagi == 'false' ){ elementPagi = false; } else { elementPagi = true; }
                if( elementRewind == 'true' ){ elementRewind = true; } else { elementRewind = false; }
                if( elementMerge == 'true' ){ elementMerge = true; } else { elementMerge = false; }
                if( elementCenter == 'true' ){ elementCenter = true; } else { elementCenter = false; }
                if( elementLazy == 'true' ){ elementLazy = true; } else { elementLazy = false; }
                if( elementVideo == 'true' ){ elementVideo = true; } else { elementVideo = false; }
                if( elementRTL == 'true' || $body.hasClass('rtl') ){ elementRTL = true; } else { elementRTL = false; }

                element.owlCarousel({
                    margin: Number(elementMargin),
                    loop: elementLoop,
                    stagePadding: Number(elementStage),
                    merge: elementMerge,
                    startPosition: Number(elementStart),
                    rewind: elementRewind,
                    slideBy: elementSlideBy,
                    center: elementCenter,
                    lazyLoad: elementLazy,
                    nav: elementNav,
                    navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
                    autoplay: elementAutoPlay,
                    autoplayTimeout: elementAutoPlayTime,
                    autoplayHoverPause: elementAutoPlayHoverPause,
                    dots: elementPagi,
                    smartSpeed: Number(elementSpeed),
                    fluidSpeed: Number(elementSpeed),
                    video: elementVideo,
                    animateIn: elementAnimateIn,
                    animateOut: elementAnimateOut,
                    rtl: elementRTL,
                    responsive:{
                        0:{ items:Number(elementItemsXs) },
                        576:{ items:Number(elementItemsSm) },
                        768:{ items:Number(elementItemsMd) },
                        992:{ items:Number(elementItemsLg) },
                        1200:{ items:Number(elementItemsXl) }
                    },
                    onInitialized: function(){
                        SEMICOLON.slider.owlCaptionInit();
                        SEMICOLON.slider.sliderParallaxDimensions();
                        SEMICOLON.initialize.lightbox();
                    }
                });
            });
        },

        masonryThumbs: function(){
            var $masonryThumbsEl = $('.masonry-thumbs:not(.customjs)');
            if( $masonryThumbsEl.length > 0 ){
                $masonryThumbsEl.each( function(){
                    var masonryItemContainer = $(this);
                    SEMICOLON.widget.masonryThumbsArrange( masonryItemContainer );
                });
            }
        },

        masonryThumbsArrange: function( element ){

            if( !$().isotope ) {
                console.log('masonryThumbsArrange: Isotope not Defined.');
                return true;
            }

            SEMICOLON.initialize.setFullColumnWidth( element );
            element.isotope('layout');
        },

        notifications: function( element ){

            if( typeof toastr === 'undefined' ) {
                console.log('notifications: Toastr not Defined.');
                return true;
            }

            toastr.remove();
            var notifyElement = $(element),
                notifyPosition = notifyElement.attr('data-notify-position'),
                notifyType = notifyElement.attr('data-notify-type'),
                notifyMsg = notifyElement.attr('data-notify-msg'),
                notifyTimeout = notifyElement.attr('data-notify-timeout'),
                notifyCloseButton = notifyElement.attr('data-notify-close');

            if( !notifyPosition ) { notifyPosition = 'toast-top-right'; } else { notifyPosition = 'toast-' + notifyElement.attr('data-notify-position'); }
            if( !notifyMsg ) { notifyMsg = 'Please set a message!'; }
            if( !notifyTimeout ) { notifyTimeout = 5000; }
            if( notifyCloseButton == 'true' ) { notifyCloseButton = true; } else { notifyCloseButton = false; }

            toastr.options.positionClass = notifyPosition;
            toastr.options.timeOut = Number( notifyTimeout );
            toastr.options.closeButton = notifyCloseButton;
            toastr.options.closeHtml = '<button><i class="icon-remove"></i></button>';

            if( notifyType == 'warning' ) {
                toastr.warning(notifyMsg);
            } else if( notifyType == 'error' ) {
                toastr.error(notifyMsg);
            } else if( notifyType == 'success' ) {
                toastr.success(notifyMsg);
            } else {
                toastr.info(notifyMsg);
            }

            return false;
        },

        textRotater: function(){

            if( !$().Morphext ) {
                console.log('textRotater: Morphext not Defined.');
                return true;
            }

            if( $textRotaterEl.length > 0 ){
                $textRotaterEl.each(function(){
                    var element = $(this),
                        trRotate = $(this).attr('data-rotate'),
                        trSpeed = $(this).attr('data-speed'),
                        trSeparator = $(this).attr('data-separator');

                    if( !trRotate ) { trRotate = "fade"; }
                    if( !trSpeed ) { trSpeed = 1200; }
                    if( !trSeparator ) { trSeparator = ","; }

                    var tRotater = $(this).find('.t-rotate');

                    var pluginData = tRotater.Morphext({
                        animation: trRotate,
                        separator: trSeparator,
                        speed: Number(trSpeed)
                    });
                });
            }
        },

        linkScroll: function(){
            $("a[data-scrollto]").off( 'click' ).on( 'click', function(){
                var element = $(this),
                    divScrollToAnchor = element.attr('data-scrollto'),
                    divScrollSpeed = element.attr('data-speed'),
                    divScrollOffset = element.attr('data-offset'),
                    divScrollEasing = element.attr('data-easing'),
                    divScrollHighlight = element.attr('data-highlight');

                if( element.parents('#primary-menu').hasClass('on-click') ) { return true; }

                if( !divScrollSpeed ) { divScrollSpeed = 750; }
                if( !divScrollOffset ) { divScrollOffset = SEMICOLON.initialize.topScrollOffset(); }
                if( !divScrollEasing ) { divScrollEasing = 'easeOutQuad'; }

                $('html,body').stop(true).animate({
                    'scrollTop': $( divScrollToAnchor ).offset().top - Number(divScrollOffset)
                }, Number(divScrollSpeed), divScrollEasing, function(){
                    if( divScrollHighlight ) {
                        if( $(divScrollToAnchor).find('.highlight-me').length > 0 ) {
                            $(divScrollToAnchor).find('.highlight-me').animate({'backgroundColor': divScrollHighlight}, 300);
                            var t = setTimeout( function(){ $(divScrollToAnchor).find('.highlight-me').animate({'backgroundColor': 'transparent'}, 300); }, 500 );
                        } else {
                            $(divScrollToAnchor).animate({'backgroundColor': divScrollHighlight}, 300);
                            var t = setTimeout( function(){ $(divScrollToAnchor).animate({'backgroundColor': 'transparent'}, 300); }, 500 );
                        }
                    }
                });

                return false;
            });
        },

        ajaxForm: function(){

            if( !$().validate ) {
                console.log('ajaxForm: Form Validate not Defined.');
                return true;
            }

            if( !$().ajaxSubmit ) {
                console.log('ajaxForm: jQuery Form not Defined.');
                return true;
            }

            var $ajaxForm = $('.form-widget:not(.customjs)');
            if( $ajaxForm.length < 1 ){ return true; }

            $ajaxForm.each( function(){
                var element = $(this),
                    elementForm = element.find('form'),
                    elementFormId = elementForm.attr('id'),
                    elementAlert = element.attr('data-alert-type'),
                    elementLoader = element.attr('data-loader'),
                    elementResult = element.find('.form-result'),
                    elementRedirect = element.attr('data-redirect');

                if( !elementAlert ) { elementAlert = 'notify'; }

                if( elementFormId ) {
                    $body.addClass( elementFormId + '-ready' );
                }

                element.find('form').validate({
                    errorPlacement: function(error, elementItem) {
                        if( elementItem.parents('.form-group').length > 0 ) {
                            error.appendTo( elementItem.parents('.form-group') );
                        } else {
                            error.insertAfter( elementItem );
                        }
                    },
                    focusCleanup: true,
                    submitHandler: function(form) {

                        elementResult.hide();

                        if( elementLoader == 'button' ) {
                            var defButton = $(form).find('button'),
                                defButtonText = defButton.html();

                            defButton.html('<i class="icon-line-loader icon-spin nomargin"></i>');
                        } else {
                            $(form).find('.form-process').fadeIn();
                        }

                        if( elementFormId ) {
                            $body.removeClass( elementFormId + '-ready ' + elementFormId + '-complete ' + elementFormId + '-success ' + elementFormId + '-error' ).addClass( elementFormId + '-processing' );
                        }

                        $(form).ajaxSubmit({
                            target: elementResult,
                            dataType: 'json',
                            success: function( data ) {
                                if( elementLoader == 'button' ) {
                                    defButton.html( defButtonText );
                                } else {
                                    $(form).find('.form-process').fadeOut();
                                }

                                if( data.alert != 'error' && elementRedirect ){
                                    window.location.replace( elementRedirect );
                                    return true;
                                }

                                if( elementAlert == 'inline' ) {
                                    if( data.alert == 'error' ) {
                                        var alertType = 'alert-danger';
                                    } else {
                                        var alertType = 'alert-success';
                                    }

                                    elementResult.removeClass( 'alert-danger alert-success' ).addClass( 'alert ' + alertType ).html( data.message ).slideDown( 400 );
                                } else if( elementAlert == 'notify' ) {
                                    elementResult.attr( 'data-notify-type', data.alert ).attr( 'data-notify-msg', data.message ).html('');
                                    SEMICOLON.widget.notifications( elementResult );
                                }

                                if( data.alert != 'error' ) {
                                    $(form).resetForm();
                                    $(form).find('.btn-group > .btn').removeClass('active');

                                    if( (typeof tinyMCE != 'undefined') && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden() ){
                                        tinymce.activeEditor.setContent('');
                                    }

                                    var rangeSlider = $(form).find('.input-range-slider');
                                    if( rangeSlider.length > 0 ) {
                                        rangeSlider.each( function(){
                                            var range = $(this).data('ionRangeSlider');
                                            range.reset();
                                        });
                                    }

                                    var ratings = $(form).find('.input-rating');
                                    if( ratings.length > 0 ) {
                                        ratings.each( function(){
                                            $(this).rating('reset');
                                        });
                                    }

                                    var selectPicker = $(form).find('.selectpicker');
                                    if( selectPicker.length > 0 ) {
                                        selectPicker.each( function(){
                                            $(this).selectpicker('val', '');
                                            $(this).selectpicker('deselectAll');
                                        });
                                    }

                                    $(form).find('.input-select2,select[data-selectsplitter-firstselect-selector]').change();

                                    $(form).trigger( 'formSubmitSuccess' );
                                    $body.removeClass( elementFormId + '-error' ).addClass( elementFormId + '-success' );
                                } else {
                                    $(form).trigger( 'formSubmitError' );
                                    $body.removeClass( elementFormId + '-success' ).addClass( elementFormId + '-error' );
                                }

                                if( elementFormId ) {
                                    $body.removeClass( elementFormId + '-processing' ).addClass( elementFormId + '-complete' );
                                }

                                if( $(form).find('.g-recaptcha').children('div').length > 0 ) { grecaptcha.reset(); }
                            }
                        });
                    }
                });

            });
        },

        subscription: function(){

            if( !$().validate ) {
                console.log('subscription: Form Validate not Defined.');
                return true;
            }

            if( !$().ajaxSubmit ) {
                console.log('subscription: jQuery Form not Defined.');
                return true;
            }

            var $subscribeForm = $('.subscribe-widget:not(.customjs)');
            if( $subscribeForm.length < 1 ){ return true; }

            $subscribeForm.each( function(){
                var element = $(this),
                    elementAlert = element.attr('data-alert-type'),
                    elementLoader = element.attr('data-loader'),
                    elementResult = element.find('.widget-subscribe-form-result'),
                    elementRedirect = element.attr('data-redirect');

                element.find('form').validate({
                    submitHandler: function(form) {

                        elementResult.hide();

                        if( elementLoader == 'button' ) {
                            var defButton = $(form).find('button'),
                                defButtonText = defButton.html();

                            defButton.html('<i class="icon-line-loader icon-spin nomargin"></i>');
                        } else {
                            $(form).find('.icon-email2').removeClass('icon-email2').addClass('icon-line-loader icon-spin');
                        }

                        $(form).ajaxSubmit({
                            target: elementResult,
                            dataType: 'json',
                            resetForm: true,
                            success: function( data ) {
                                if( elementLoader == 'button' ) {
                                    defButton.html( defButtonText );
                                } else {
                                    $(form).find('.icon-line-loader').removeClass('icon-line-loader icon-spin').addClass('icon-email2');
                                }
                                if( data.alert != 'error' && elementRedirect ){
                                    window.location.replace( elementRedirect );
                                    return true;
                                }
                                if( elementAlert == 'inline' ) {
                                    if( data.alert == 'error' ) {
                                        var alertType = 'alert-danger';
                                    } else {
                                        var alertType = 'alert-success';
                                    }

                                    elementResult.addClass( 'alert ' + alertType ).html( data.message ).slideDown( 400 );
                                } else {
                                    elementResult.attr( 'data-notify-type', data.alert ).attr( 'data-notify-msg', data.message ).html('');
                                    SEMICOLON.widget.notifications( elementResult );
                                }
                            }
                        });
                    }
                });

            });
        },

        stickySidebar: function(){

            if( !$().scwStickySidebar ) {
                console.log('stickySidebar: Sticky Sidebar is not Defined.');
                return true;
            }

            var $stickySidebar = jQuery('.sticky-sidebar-wrap');
            if( $stickySidebar.length < 1 ){ return true; }

            $stickySidebar.each( function(){
                var element = $(this),
                    elementTop = element.attr('data-offset-top'),
                    elementBottom = element.attr('data-offset-bottom');

                if( !elementTop ) { elementTop = 110; }
                if( !elementBottom ) { elementBottom = 50; }

                element.scwStickySidebar({
                    additionalMarginTop: Number( elementTop ),
                    additionalMarginBottom: Number( elementBottom )
                });
            });
        },

        cookieNotify: function(){

            if( Cookies === 'undefined' ) {
                console.log('cookieNotify: Cookie Function not defined.');
                return true;
            }

            if( $cookieNotification.length > 0 ) {
                var cookieNotificationHeight = $cookieNotification.outerHeight();

                $cookieNotification.css({ bottom: -cookieNotificationHeight });

                if( Cookies.get('websiteUsesCookies') != 'yesConfirmed' ) {
                    $cookieNotification.css({ bottom: 0, opacity: 1 });
                }

                $('.cookie-accept').off( 'click' ).on( 'click', function(){
                    $cookieNotification.css({ bottom: -cookieNotificationHeight, opacity: 0 });
                    Cookies.set('websiteUsesCookies', 'yesConfirmed', { expires: 30 });
                    return false;
                });
            }
        },

        cartQuantity: function(){

            $(".plus").off( 'click' ).on( 'click', function(){
                var productQuantity = $(this).parents('.quantity').find('.qty').val(),
                    quantityStep = $(this).parents('.quantity').find('.qty').attr('step'),
                    intRegex = /^\d+$/;

                if( !quantityStep ) { quantityStep = 1; }

                if( intRegex.test( productQuantity ) ) {
                    var productQuantityPlus = Number(productQuantity) + Number(quantityStep);
                    $(this).parents('.quantity').find('.qty').val( productQuantityPlus ).change();
                } else {
                    $(this).parents('.quantity').find('.qty').val( Number(quantityStep) ).change();
                }

                return false;
            });

            $(".minus").off( 'click' ).on( 'click', function(){
                var productQuantity = $(this).parents('.quantity').find('.qty').val(),
                    quantityStep = $(this).parents('.quantity').find('.qty').attr('step'),
                    intRegex = /^\d+$/;

                if( !quantityStep ) { quantityStep = 1; }

                if( intRegex.test( productQuantity ) ) {
                    if( Number(productQuantity) > 1 ) {
                        var productQuantityMinus = Number(productQuantity) - Number(quantityStep);
                        $(this).parents('.quantity').find('.qty').val( productQuantityMinus );
                    }
                } else {
                    $(this).parents('.quantity').find('.qty').val( Number(quantityStep) );
                }

                return false;
            });

        },

        extras: function(){

            if( $().tooltip ) {
                $('[data-toggle="tooltip"]').tooltip({container: 'body'});
            } else {
                console.log('extras: Bootstrap Tooltip not defined.');
            }

            if( $().popover ) {
                $('[data-toggle=popover]').popover();
            } else {
                console.log('extras: Bootstrap Popover not defined.');
            }

            $('.style-msg').on( 'click', '.close', function(e){
                $( this ).parents( '.style-msg' ).slideUp();
                e.preventDefault();
            });

            $('#primary-menu-trigger,#overlay-menu-close').off( 'click' ).on( 'click', function() {
                if( $('#primary-menu').find('ul.mobile-primary-menu').length > 0 ) {
                    $( '#primary-menu > ul.mobile-primary-menu, #primary-menu > div > ul.mobile-primary-menu' ).toggleClass('d-block');
                } else {
                    $( '#primary-menu > ul, #primary-menu > div > ul' ).toggleClass('d-block');
                }
                $body.toggleClass("primary-menu-open");
                return false;
            });
            $('#page-submenu-trigger').off( 'click' ).on( 'click', function() {
                $body.toggleClass('top-search-open', false);
                $pagemenu.toggleClass("pagemenu-active");
                return false;
            });
            $pagemenu.find('nav').off( 'click' ).on( 'click', function(e){
                $body.toggleClass('top-search-open', false);
                $topCart.toggleClass('top-cart-open', false);
            });
            if( SEMICOLON.isMobile.any() ){
                $body.addClass('device-touch');
            }
            // var el = {
            //     darkLogo : $("<img>", {src: defaultDarkLogo}),
            //     darkRetinaLogo : $("<img>", {src: retinaDarkLogo})
            // };
            // el.darkLogo.prependTo("body");
            // el.darkRetinaLogo.prependTo("body");
            // el.darkLogo.css({'position':'absolute','z-index':'-100'});
            // el.darkRetinaLogo.css({'position':'absolute','z-index':'-100'});
        }

    };

    SEMICOLON.isMobile = {
        Android: function() {
            return navigator.userAgent.match(/Android/i);
        },
        BlackBerry: function() {
            return navigator.userAgent.match(/BlackBerry/i);
        },
        iOS: function() {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        Opera: function() {
            return navigator.userAgent.match(/Opera Mini/i);
        },
        Windows: function() {
            return navigator.userAgent.match(/IEMobile/i);
        },
        any: function() {
            return (SEMICOLON.isMobile.Android() || SEMICOLON.isMobile.BlackBerry() || SEMICOLON.isMobile.iOS() || SEMICOLON.isMobile.Opera() || SEMICOLON.isMobile.Windows());
        }
    };

    SEMICOLON.documentOnResize = {

        init: function(){

            var t = setTimeout( function(){
                SEMICOLON.header.topsocial();
                SEMICOLON.header.fullWidthMenu();
                SEMICOLON.header.overlayMenu();
                SEMICOLON.initialize.fullScreen();
                SEMICOLON.initialize.dataResponsiveHeights();
                SEMICOLON.initialize.verticalMiddle();
                SEMICOLON.initialize.testimonialsGrid();
                SEMICOLON.initialize.stickFooterOnSmall();
                SEMICOLON.initialize.stickyFooter();
                SEMICOLON.slider.sliderParallaxDimensions();
                SEMICOLON.slider.captionPosition();
                SEMICOLON.portfolio.arrange();
                SEMICOLON.portfolio.portfolioDescMargin();
                SEMICOLON.widget.tabsResponsiveResize();
                SEMICOLON.widget.tabsJustify();
                SEMICOLON.widget.html5Video();
                SEMICOLON.widget.masonryThumbs();
                SEMICOLON.initialize.dataResponsiveClasses();
                if( $gridContainer.length > 0 ) {
                    if( !$gridContainer.hasClass('.customjs') ) {
                        if( $().isotope ) {
                            $gridContainer.isotope('layout');
                        } else {
                            console.log('documentOnResize > init: Isotope not defined.');
                        }
                    }
                }
                if( $body.hasClass('device-xl') || $body.hasClass('device-lg') ) {
                    $('#primary-menu').find('ul.mobile-primary-menu').removeClass('d-block');
                }
            }, 500 );

            windowWidth = $window.width();

        }

    };

    SEMICOLON.documentOnReady = {

        init: function(){
            SEMICOLON.initialize.init();
            SEMICOLON.header.init();
            if( $slider.length > 0 || $sliderElement.length > 0 ) { SEMICOLON.slider.init(); }
            if( $portfolio.length > 0 ) { SEMICOLON.portfolio.init(); }
            SEMICOLON.widget.init();
            SEMICOLON.documentOnReady.windowscroll();
        },

        windowscroll: function(){

            var headerOffset = 0,
                headerWrapOffset = 0,
                pageMenuOffset = 0;

            if( $header.length > 0 ) { headerOffset = $header.offset().top; }
            if( $header.length > 0 ) { headerWrapOffset = $headerWrap.offset().top; }
            if( $pagemenu.length > 0 ) {
                if( $header.length > 0 && !$header.hasClass('no-sticky') ) {
                    if( $header.hasClass('sticky-style-2') || $header.hasClass('sticky-style-3') ) {
                        pageMenuOffset = $pagemenu.offset().top - $headerWrap.outerHeight();
                    } else {
                        pageMenuOffset = $pagemenu.offset().top - $header.outerHeight();
                    }
                } else {
                    pageMenuOffset = $pagemenu.offset().top;
                }
            }

            var headerDefinedOffset = $header.attr('data-sticky-offset');
            if( typeof headerDefinedOffset !== 'undefined' ) {
                if( headerDefinedOffset == 'full' ) {
                    headerWrapOffset = $window.height();
                    var headerOffsetNegative = $header.attr('data-sticky-offset-negative');
                    if( typeof headerOffsetNegative !== 'undefined' ) { headerWrapOffset = headerWrapOffset - headerOffsetNegative - 1; }
                } else {
                    headerWrapOffset = Number(headerDefinedOffset);
                }
            } else {
                if( $header.hasClass('sticky-style-2') || $header.hasClass('sticky-style-3') ) {
                    if( headerWrapOffset === 'undefined' ) {
                        headerWrapOffset = headerOffset;
                    }
                } else {
                    headerWrapOffset = headerOffset;
                }
            }

            SEMICOLON.header.stickyMenu( headerWrapOffset );
            SEMICOLON.header.stickyPageMenu( pageMenuOffset );

            $window.on( 'scroll', function(){

                SEMICOLON.initialize.goToTopScroll();
                $('body.open-header.close-header-on-scroll').removeClass("side-header-open");
                SEMICOLON.header.stickyMenu( headerWrapOffset );
                SEMICOLON.header.stickyPageMenu( pageMenuOffset );
                // SEMICOLON.header.logo();

            });

            window.addEventListener('scroll', onScrollSliderParallax, false);

            if( $onePageMenuEl.length > 0 ){
                if( $().scrolled ) {
                    $window.scrolled(function() {
                        SEMICOLON.header.onepageScroller();
                    });
                } else {
                    console.log('windowscroll: Scrolled Function not defined.');
                }
            }
        }

    };

    SEMICOLON.documentOnLoad = {

        init: function(){
            SEMICOLON.slider.captionPosition();
            SEMICOLON.slider.swiperSliderMenu(true);
            SEMICOLON.slider.revolutionSliderMenu(true);
            SEMICOLON.initialize.testimonialsGrid();
            SEMICOLON.initialize.verticalMiddle();
            SEMICOLON.initialize.stickFooterOnSmall();
            SEMICOLON.initialize.stickyFooter();
            SEMICOLON.portfolio.gridInit( $gridContainer );
            SEMICOLON.portfolio.filterInit();
            SEMICOLON.portfolio.shuffleInit();
            SEMICOLON.portfolio.arrange();
            SEMICOLON.portfolio.portfolioDescMargin();
            SEMICOLON.widget.parallax();
            SEMICOLON.widget.loadFlexSlider();
            SEMICOLON.widget.html5Video();
            SEMICOLON.widget.masonryThumbs();
            SEMICOLON.header.topsocial();
            SEMICOLON.header.responsiveMenuClass();
            SEMICOLON.initialize.modal();
        }

    };

    var $window = $(window),
        $body = $('body'),
        $wrapper = $('#wrapper'),
        $header = $('#header'),
        $headerWrap = $('#header-wrap'),
        $content = $('#content'),
        $footer = $('#footer'),
        windowWidth = $window.width(),
        oldHeaderClasses = $header.attr('class'),
        oldHeaderWrapClasses = $headerWrap.attr('class'),
        stickyMenuClasses = $header.attr('data-sticky-class'),
        responsiveMenuClasses = $header.attr('data-responsive-class'),
        defaultLogo = $('#logo').find('.standard-logo'),
        defaultLogoWidth = defaultLogo.find('img').outerWidth(),
        retinaLogo = $('#logo').find('.retina-logo'),
        defaultLogoImg = defaultLogo.find('img').attr('src'),
        retinaLogoImg = retinaLogo.find('img').attr('src'),
        defaultDarkLogo = defaultLogo.attr('data-dark-logo'),
        retinaDarkLogo = retinaLogo.attr('data-dark-logo'),
        defaultStickyLogo = defaultLogo.attr('data-sticky-logo'),
        retinaStickyLogo = retinaLogo.attr('data-sticky-logo'),
        defaultMobileLogo = defaultLogo.attr('data-mobile-logo'),
        retinaMobileLogo = retinaLogo.attr('data-mobile-logo'),
        $pagemenu = $('#page-menu'),
        $onePageMenuEl = $('.one-page-menu'),
        onePageGlobalOffset = 0,
        $portfolio = $('.portfolio'),
        $shop = $('.shop'),
        $gridContainer = $('.grid-container'),
        $slider = $('#slider'),
        $sliderParallaxEl = $('.slider-parallax'),
        $sliderElement = $('.slider-element'),
        swiperSlider = '',
        $pageTitle = $('#page-title'),
        $portfolioItems = $('.portfolio-ajax').find('.portfolio-item'),
        $portfolioDetails = $('#portfolio-ajax-wrap'),
        $portfolioDetailsContainer = $('#portfolio-ajax-container'),
        $portfolioAjaxLoader = $('#portfolio-ajax-loader'),
        $portfolioFilter = $('.portfolio-filter,.custom-filter'),
        prevPostPortId = '',
        $topSearch = $('#top-search'),
        $topCart = $('#top-cart'),
        $verticalMiddleEl = $('.vertical-middle'),
        $topSocialEl = $('#top-social').find('li'),
        $siStickyEl = $('.si-sticky'),
        $dotsMenuEl = $('.dots-menu'),
        $goToTopEl = $('#gotoTop'),
        $fullScreenEl = $('.full-screen'),
        $testimonialsGridEl = $('.testimonials-grid'),
        $pageSectionEl = $('.page-section'),
        $owlCarouselEl = $('.owl-carousel'),
        $parallaxEl = $('.parallax'),
        $parallaxPageTitleEl = $('.page-title-parallax'),
        $parallaxPortfolioEl = $('.portfolio-parallax').find('.portfolio-image'),
        $textRotaterEl = $('.text-rotater'),
        $cookieNotification = $('#cookie-notification');

    $(document).ready( SEMICOLON.documentOnReady.init );
    $window.on( 'load', SEMICOLON.documentOnLoad.init );
    $window.on( 'resize', SEMICOLON.documentOnResize.init );

})(jQuery);

(function ($) {

    var timer;
    var input;

    $(document.body).on('keyup', '.ajax-search-field', function() {
        input = $(this);
        resetTimer();

        timer = setTimeout(ajaxSearch, 1000);
    });

    $(document).ready(function() {
        var searchInput = $('.ajax-search-field');

        if (searchInput.length) {
			searchInput.each(function() {
				var input = $(this);
				var parent = input.parents('form');
				var holder = "<div class='ajax-search-holder'></div>";
				parent.append(holder);
			});
		}

    });

    $(window).on('load', function () {});

    $(window).on('scroll', function() {});

    $(window).on('resize', function(e) {});

    function ajaxSearch() {
        console.log('Form input');
        var q = input.val();
        var parent = input.parents('form');
        var holder = parent.find('.ajax-search-holder');

        var data = {
            q: q,
			action: 'snth_ajax_search'
		};

		ajaxRequest(data, function(decoded) {
            if (decoded.searchResult) {
                holder.html(decoded.searchResult).show();
            } else {
				holder.html('').show();
			}
        });
    }

	function ajaxRequest(data, cb, cbError) {
		$.ajax({
			type: 'post',
			url: snthAjaxObj.ajaxurl,
			data: data,
			success: function (response) {
				var decoded;

				try {
					decoded = $.parseJSON(response);
				} catch(err) {
					console.log(err);
					decoded = false;
				}

				if (decoded) {
					if (decoded.consoleLog) {
						console.log(decoded.consoleLog);
                    }

					if (decoded.message) {
						alert(decoded.message);
					}

					if (decoded.fragments) {
						updateFragments ( decoded.fragments );
					}

					if (decoded.success) {
						if (typeof cb === 'function') {
							cb(decoded);
						}
					} else {
						if (typeof cbError === 'function') {
							cbError(decoded);
						}
					}

					setTimeout(function () {
						if (decoded.url) {
							window.location.replace(decoded.url);
						} else if (decoded.reload) {
							window.location.reload();
						}
					}, 100);
				} else {
					alert('Something went wrong');
				}
			}
		});
    }

	function updateFragments ( fragments ) {
		if ( fragments ) {
			$.each( fragments, function( key ) {
				$( key )
					.addClass( 'updating' )
					.fadeTo( '400', '0.6' )
					.block({
						message: null,
						overlayCSS: {
							opacity: 0.6
						}
					});
			});

			$.each( fragments, function( key, value ) {
				$( key ).replaceWith( value );
				$( key ).stop( true ).css( 'opacity', '1' ).unblock();
			});
		}
	}

    function resetTimer() {
        console.log('Reset timer');
        clearTimeout( timer );
    }
}(jQuery));
(function ($) {
    var productImageOwlCarousel;
    var productThumbOwlCarousel;
    var datepicker = $( "input[date='date']" );

    $(document.body).on('click', '.product-modal-desc-open', function() {
        var parentQuickView = $(this).parents('.product-quick-view');
        var parentQuickViewContent = parentQuickView.find('.modal-content');
        var parentQuickViewContentHtml = parentQuickViewContent.html();
        var productModalQuickView = $('#product-modal-desc');
        var productModalQuickViewContent = productModalQuickView.find('.modal-content');
        productModalQuickViewContent.html(parentQuickViewContentHtml);
        $('#product-modal-desc').modal();
    });

    $('#product-modal-desc').on('shown.bs.modal', function (e) {
        console.log('Shown modal');
        var descCarousel = $('#product-modal-desc').find('.product-modal-desc-images');

        descCarousel.owlCarousel({
            loop:true,
            margin:10,
            nav:true,
            items:1
        });
    })

    $(document.body).on('click', "#top-cart-trigger", function(e) {
        $('#page-menu').toggleClass('pagemenu-active', false);
        $('#top-cart').toggleClass('top-cart-open');
        e.stopPropagation();
        e.preventDefault();

    }); 

    $(document.body).on('click', '#woocommerce-product-gallery__thumbnails a', function(e) {
        var sliderIndex = $(this).data('index');
        productImageOwlCarousel.trigger('to.owl.carousel', [sliderIndex]);
        e.stopPropagation();
        e.preventDefault();

    });

    $(document.body).on('click', '#ajax-search-field', function(e) {
        //TODO:Add ajax search
    });

    $(document.body).on('wooaioservice:success', function(e) {
        if ($( "input[date='date']" ).length) {
            $( "input[date='date']" ).each(function() {
                $(this).datepicker({
                    format: 'dd-mm-yyyy'
                });
            });
        }
    });

    $(document).ready(function() {
        productImageOwlCarousel = $('#woocommerce-product-gallery__images');

        productImageOwlCarousel.owlCarousel({
            margin: 0,
            items: 1,
            nav: 0,
            navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
            dots: 0
        });

        productThumbOwlCarousel = $('#woocommerce-product-gallery__thumbnails');

        productThumbOwlCarousel.owlCarousel({
            margin: 5,
            items: 1,
            nav: 1,
            navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
            dots: 0,
            responsive:{
                0:{ items:2 },
                576:{ items:2 },
                768:{ items:3 },
                992:{ items:3 },
                1200:{ items:3 }
            }
        });

        if (datepicker.length) {
            datepicker.each(function() {
                $(this).datepicker({
                    format: 'dd-mm-yyyy'
                });
            });
        }
        

        $('.shop.product-3 .product .product-desc .product-title').matchHeight({
            byRow: true,
            property: 'height',
            target: null,
            remove: false
        });
    });

    $(window).on('load', function () {});

    $(window).on('scroll', function() {});

    $(window).on('resize', function(e) {});
}(jQuery));
