/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/src/front-end/js/components/Attachment.vue":
/*!***********************************************************!*\
  !*** ./assets/src/front-end/js/components/Attachment.vue ***!
  \***********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _Attachment_vue_vue_type_template_id_9db8c732___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Attachment.vue?vue&type=template&id=9db8c732& */ \"./assets/src/front-end/js/components/Attachment.vue?vue&type=template&id=9db8c732&\");\n/* harmony import */ var _Attachment_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Attachment.vue?vue&type=script&lang=js& */ \"./assets/src/front-end/js/components/Attachment.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _Attachment_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _Attachment_vue_vue_type_template_id_9db8c732___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _Attachment_vue_vue_type_template_id_9db8c732___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"assets/src/front-end/js/components/Attachment.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/Attachment.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/Attachment.vue?vue&type=script&lang=js&":
/*!************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/Attachment.vue?vue&type=script&lang=js& ***!
  \************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachment_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/vue-loader/lib??vue-loader-options!./Attachment.vue?vue&type=script&lang=js& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/Attachment.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachment_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/Attachment.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/Attachment.vue?vue&type=template&id=9db8c732&":
/*!******************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/Attachment.vue?vue&type=template&id=9db8c732& ***!
  \******************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachment_vue_vue_type_template_id_9db8c732___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../../node_modules/vue-loader/lib??vue-loader-options!./Attachment.vue?vue&type=template&id=9db8c732& */ \"./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/Attachment.vue?vue&type=template&id=9db8c732&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachment_vue_vue_type_template_id_9db8c732___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Attachment_vue_vue_type_template_id_9db8c732___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/Attachment.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/AttachmentLoading.vue":
/*!******************************************************************!*\
  !*** ./assets/src/front-end/js/components/AttachmentLoading.vue ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _AttachmentLoading_vue_vue_type_template_id_e03705f6___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AttachmentLoading.vue?vue&type=template&id=e03705f6& */ \"./assets/src/front-end/js/components/AttachmentLoading.vue?vue&type=template&id=e03705f6&\");\n/* harmony import */ var _AttachmentLoading_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AttachmentLoading.vue?vue&type=script&lang=js& */ \"./assets/src/front-end/js/components/AttachmentLoading.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _AttachmentLoading_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _AttachmentLoading_vue_vue_type_template_id_e03705f6___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _AttachmentLoading_vue_vue_type_template_id_e03705f6___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"assets/src/front-end/js/components/AttachmentLoading.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/AttachmentLoading.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/AttachmentLoading.vue?vue&type=script&lang=js&":
/*!*******************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/AttachmentLoading.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentLoading_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/vue-loader/lib??vue-loader-options!./AttachmentLoading.vue?vue&type=script&lang=js& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/AttachmentLoading.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentLoading_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/AttachmentLoading.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/AttachmentLoading.vue?vue&type=template&id=e03705f6&":
/*!*************************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/AttachmentLoading.vue?vue&type=template&id=e03705f6& ***!
  \*************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentLoading_vue_vue_type_template_id_e03705f6___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../../node_modules/vue-loader/lib??vue-loader-options!./AttachmentLoading.vue?vue&type=template&id=e03705f6& */ \"./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/AttachmentLoading.vue?vue&type=template&id=e03705f6&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentLoading_vue_vue_type_template_id_e03705f6___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_AttachmentLoading_vue_vue_type_template_id_e03705f6___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/AttachmentLoading.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/CommentAttachment.vue":
/*!******************************************************************!*\
  !*** ./assets/src/front-end/js/components/CommentAttachment.vue ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _CommentAttachment_vue_vue_type_template_id_da15f264___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CommentAttachment.vue?vue&type=template&id=da15f264& */ \"./assets/src/front-end/js/components/CommentAttachment.vue?vue&type=template&id=da15f264&\");\n/* harmony import */ var _CommentAttachment_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CommentAttachment.vue?vue&type=script&lang=js& */ \"./assets/src/front-end/js/components/CommentAttachment.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _CommentAttachment_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _CommentAttachment_vue_vue_type_template_id_da15f264___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _CommentAttachment_vue_vue_type_template_id_da15f264___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"assets/src/front-end/js/components/CommentAttachment.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/CommentAttachment.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/CommentAttachment.vue?vue&type=script&lang=js&":
/*!*******************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/CommentAttachment.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_CommentAttachment_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/vue-loader/lib??vue-loader-options!./CommentAttachment.vue?vue&type=script&lang=js& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/CommentAttachment.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_CommentAttachment_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/CommentAttachment.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/CommentAttachment.vue?vue&type=template&id=da15f264&":
/*!*************************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/CommentAttachment.vue?vue&type=template&id=da15f264& ***!
  \*************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CommentAttachment_vue_vue_type_template_id_da15f264___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../../node_modules/vue-loader/lib??vue-loader-options!./CommentAttachment.vue?vue&type=template&id=da15f264& */ \"./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/CommentAttachment.vue?vue&type=template&id=da15f264&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CommentAttachment_vue_vue_type_template_id_da15f264___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CommentAttachment_vue_vue_type_template_id_da15f264___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/CommentAttachment.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/CommentAttachments.vue":
/*!*******************************************************************!*\
  !*** ./assets/src/front-end/js/components/CommentAttachments.vue ***!
  \*******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _CommentAttachments_vue_vue_type_template_id_615117d6___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CommentAttachments.vue?vue&type=template&id=615117d6& */ \"./assets/src/front-end/js/components/CommentAttachments.vue?vue&type=template&id=615117d6&\");\n/* harmony import */ var _CommentAttachments_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CommentAttachments.vue?vue&type=script&lang=js& */ \"./assets/src/front-end/js/components/CommentAttachments.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _CommentAttachments_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _CommentAttachments_vue_vue_type_template_id_615117d6___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _CommentAttachments_vue_vue_type_template_id_615117d6___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"assets/src/front-end/js/components/CommentAttachments.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/CommentAttachments.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/CommentAttachments.vue?vue&type=script&lang=js&":
/*!********************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/CommentAttachments.vue?vue&type=script&lang=js& ***!
  \********************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_CommentAttachments_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/vue-loader/lib??vue-loader-options!./CommentAttachments.vue?vue&type=script&lang=js& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/CommentAttachments.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_CommentAttachments_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/CommentAttachments.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/CommentAttachments.vue?vue&type=template&id=615117d6&":
/*!**************************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/CommentAttachments.vue?vue&type=template&id=615117d6& ***!
  \**************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CommentAttachments_vue_vue_type_template_id_615117d6___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../../node_modules/vue-loader/lib??vue-loader-options!./CommentAttachments.vue?vue&type=template&id=615117d6& */ \"./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/CommentAttachments.vue?vue&type=template&id=615117d6&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CommentAttachments_vue_vue_type_template_id_615117d6___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CommentAttachments_vue_vue_type_template_id_615117d6___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/CommentAttachments.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/ThreadAttachment.vue":
/*!*****************************************************************!*\
  !*** ./assets/src/front-end/js/components/ThreadAttachment.vue ***!
  \*****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _ThreadAttachment_vue_vue_type_template_id_021867de___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ThreadAttachment.vue?vue&type=template&id=021867de& */ \"./assets/src/front-end/js/components/ThreadAttachment.vue?vue&type=template&id=021867de&\");\n/* harmony import */ var _ThreadAttachment_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ThreadAttachment.vue?vue&type=script&lang=js& */ \"./assets/src/front-end/js/components/ThreadAttachment.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _ThreadAttachment_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _ThreadAttachment_vue_vue_type_template_id_021867de___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _ThreadAttachment_vue_vue_type_template_id_021867de___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"assets/src/front-end/js/components/ThreadAttachment.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/ThreadAttachment.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/ThreadAttachment.vue?vue&type=script&lang=js&":
/*!******************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/ThreadAttachment.vue?vue&type=script&lang=js& ***!
  \******************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_ThreadAttachment_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/vue-loader/lib??vue-loader-options!./ThreadAttachment.vue?vue&type=script&lang=js& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/ThreadAttachment.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_ThreadAttachment_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/ThreadAttachment.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/ThreadAttachment.vue?vue&type=template&id=021867de&":
/*!************************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/ThreadAttachment.vue?vue&type=template&id=021867de& ***!
  \************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ThreadAttachment_vue_vue_type_template_id_021867de___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../../node_modules/vue-loader/lib??vue-loader-options!./ThreadAttachment.vue?vue&type=template&id=021867de& */ \"./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/ThreadAttachment.vue?vue&type=template&id=021867de&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ThreadAttachment_vue_vue_type_template_id_021867de___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ThreadAttachment_vue_vue_type_template_id_021867de___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/ThreadAttachment.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/ThreadAttachments.vue":
/*!******************************************************************!*\
  !*** ./assets/src/front-end/js/components/ThreadAttachments.vue ***!
  \******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _ThreadAttachments_vue_vue_type_template_id_399d519c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ThreadAttachments.vue?vue&type=template&id=399d519c& */ \"./assets/src/front-end/js/components/ThreadAttachments.vue?vue&type=template&id=399d519c&\");\n/* harmony import */ var _ThreadAttachments_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ThreadAttachments.vue?vue&type=script&lang=js& */ \"./assets/src/front-end/js/components/ThreadAttachments.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _ThreadAttachments_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _ThreadAttachments_vue_vue_type_template_id_399d519c___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _ThreadAttachments_vue_vue_type_template_id_399d519c___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"assets/src/front-end/js/components/ThreadAttachments.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/ThreadAttachments.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/ThreadAttachments.vue?vue&type=script&lang=js&":
/*!*******************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/ThreadAttachments.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_ThreadAttachments_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/vue-loader/lib??vue-loader-options!./ThreadAttachments.vue?vue&type=script&lang=js& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/ThreadAttachments.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_ThreadAttachments_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/ThreadAttachments.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/ThreadAttachments.vue?vue&type=template&id=399d519c&":
/*!*************************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/ThreadAttachments.vue?vue&type=template&id=399d519c& ***!
  \*************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ThreadAttachments_vue_vue_type_template_id_399d519c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../../node_modules/vue-loader/lib??vue-loader-options!./ThreadAttachments.vue?vue&type=template&id=399d519c& */ \"./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/ThreadAttachments.vue?vue&type=template&id=399d519c&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ThreadAttachments_vue_vue_type_template_id_399d519c___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ThreadAttachments_vue_vue_type_template_id_399d519c___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/ThreadAttachments.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/UploadIcon.vue":
/*!***********************************************************!*\
  !*** ./assets/src/front-end/js/components/UploadIcon.vue ***!
  \***********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _UploadIcon_vue_vue_type_template_id_45a4c5de___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./UploadIcon.vue?vue&type=template&id=45a4c5de& */ \"./assets/src/front-end/js/components/UploadIcon.vue?vue&type=template&id=45a4c5de&\");\n/* harmony import */ var _UploadIcon_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./UploadIcon.vue?vue&type=script&lang=js& */ \"./assets/src/front-end/js/components/UploadIcon.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ \"./node_modules/vue-loader/lib/runtime/componentNormalizer.js\");\n\n\n\n\n\n/* normalize component */\n\nvar component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(\n  _UploadIcon_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n  _UploadIcon_vue_vue_type_template_id_45a4c5de___WEBPACK_IMPORTED_MODULE_0__[\"render\"],\n  _UploadIcon_vue_vue_type_template_id_45a4c5de___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"],\n  false,\n  null,\n  null,\n  null\n  \n)\n\n/* hot reload */\nif (false) { var api; }\ncomponent.options.__file = \"assets/src/front-end/js/components/UploadIcon.vue\"\n/* harmony default export */ __webpack_exports__[\"default\"] = (component.exports);\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/UploadIcon.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/UploadIcon.vue?vue&type=script&lang=js&":
/*!************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/UploadIcon.vue?vue&type=script&lang=js& ***!
  \************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_UploadIcon_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/babel-loader/lib!../../../../../node_modules/vue-loader/lib??vue-loader-options!./UploadIcon.vue?vue&type=script&lang=js& */ \"./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/UploadIcon.vue?vue&type=script&lang=js&\");\n/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__[\"default\"] = (_node_modules_babel_loader_lib_index_js_node_modules_vue_loader_lib_index_js_vue_loader_options_UploadIcon_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__[\"default\"]); \n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/UploadIcon.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/components/UploadIcon.vue?vue&type=template&id=45a4c5de&":
/*!******************************************************************************************!*\
  !*** ./assets/src/front-end/js/components/UploadIcon.vue?vue&type=template&id=45a4c5de& ***!
  \******************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_UploadIcon_vue_vue_type_template_id_45a4c5de___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../../node_modules/vue-loader/lib??vue-loader-options!./UploadIcon.vue?vue&type=template&id=45a4c5de& */ \"./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/UploadIcon.vue?vue&type=template&id=45a4c5de&\");\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_UploadIcon_vue_vue_type_template_id_45a4c5de___WEBPACK_IMPORTED_MODULE_0__[\"render\"]; });\n\n/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_UploadIcon_vue_vue_type_template_id_45a4c5de___WEBPACK_IMPORTED_MODULE_0__[\"staticRenderFns\"]; });\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/UploadIcon.vue?");

/***/ }),

/***/ "./assets/src/front-end/js/extensions/comment.js":
/*!*******************************************************!*\
  !*** ./assets/src/front-end/js/extensions/comment.js ***!
  \*******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _components_CommentAttachments_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../components/CommentAttachments.vue */ \"./assets/src/front-end/js/components/CommentAttachments.vue\");\n/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! lodash.get */ \"./node_modules/lodash.get/index.js\");\n/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(lodash_get__WEBPACK_IMPORTED_MODULE_1__);\nconst {\n  extendComponent\n} = ph.components;\n\n\nextendComponent(\"shared.comment\", {\n  mounted() {\n    // insert upload preview component\n    this.insertComponent({\n      ref: \"content\",\n      component: _components_CommentAttachments_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\n      data: {\n        propsData: {\n          comment: this.comment\n        }\n      },\n      key: `attachments-${this.comment.id}`\n    }); // fetch attachments!\n\n    if (this.comment.attachment_ids.length) {\n      this.$http.get(\"/media/\", {\n        params: {\n          include: this.comment.attachment_ids\n        }\n      }).then(([data]) => {\n        _.each(data, attachment => {\n          attachment.post = 0;\n        });\n\n        this.$store.dispatch(\"entities/insertOrUpdate\", {\n          entity: \"media\",\n          data\n        });\n      });\n    }\n  }\n\n});\n\n//# sourceURL=webpack:///./assets/src/front-end/js/extensions/comment.js?");

/***/ }),

/***/ "./assets/src/front-end/js/extensions/editor.js":
/*!******************************************************!*\
  !*** ./assets/src/front-end/js/extensions/editor.js ***!
  \******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _components_UploadIcon_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../components/UploadIcon.vue */ \"./assets/src/front-end/js/components/UploadIcon.vue\");\nconst {\n  extendComponent\n} = ph.components;\n // add icon to editor\n\nextendComponent(\"shared.editor\", {\n  mounted() {\n    this.insertIntoSlot(\"footer-right-after\", _components_UploadIcon_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"], {\n      props: {\n        thread: this.thread\n      },\n      on: {\n        upload: files => {\n          this.$emit(\"upload\", files);\n        }\n      }\n    });\n  }\n\n});\n\n//# sourceURL=webpack:///./assets/src/front-end/js/extensions/editor.js?");

/***/ }),

/***/ "./assets/src/front-end/js/extensions/thread.js":
/*!******************************************************!*\
  !*** ./assets/src/front-end/js/extensions/thread.js ***!
  \******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _components_ThreadAttachments_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../components/ThreadAttachments.vue */ \"./assets/src/front-end/js/components/ThreadAttachments.vue\");\n/**\r\n * Extend the ThreadBody.vue component\r\n */\nconst {\n  extendComponent\n} = ph.components;\n\nextendComponent(\"thread.body\", {\n  data() {\n    return {\n      attachments: []\n    };\n  },\n\n  mounted() {\n    // add attachments on upload\n    this.$refs.editor.$on(\"upload\", this.uploadFile); // insert upload preview component\n\n    this.insertComponent({\n      ref: \"form\",\n      component: _components_ThreadAttachments_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\n      data: {\n        // remember, this is not dynamic\n        propsData: {\n          id: this.thread.id\n        }\n      },\n      key: `test`\n    }); // add attachment ids to new comment submit\n\n    ph.hooks.addFilter(\"ph_new_comment_data\", \"ph.file-uploads\", (data, instance) => {\n      data.attachment_ids = this.thread.attachment_ids;\n      this.thread.$update({\n        attachment_ids: []\n      });\n      return data;\n    });\n  },\n\n  methods: {\n    async uploadFile(files) {\n      if (PHF_Settings.max_upload_size < files[0].size) {\n        alert(\"Attached file exceeds the maximum upload size for this site.\");\n        return;\n      }\n\n      let {\n        media\n      } = await this.$store.dispatch(\"entities/insert\", {\n        entity: \"media\",\n        data: {\n          post: this.thread.id || 0,\n          file: files[0]\n        }\n      });\n      this.thread.$update({\n        attachment_ids: [...this.thread.attachment_ids, ..._.pluck(media, \"id\")]\n      });\n    }\n\n  }\n});\n\n//# sourceURL=webpack:///./assets/src/front-end/js/extensions/thread.js?");

/***/ }),

/***/ "./assets/src/front-end/js/main.js":
/*!*****************************************!*\
  !*** ./assets/src/front-end/js/main.js ***!
  \*****************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _extensions_editor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./extensions/editor */ \"./assets/src/front-end/js/extensions/editor.js\");\n/* harmony import */ var _extensions_thread__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./extensions/thread */ \"./assets/src/front-end/js/extensions/thread.js\");\n/* harmony import */ var _extensions_comment__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./extensions/comment */ \"./assets/src/front-end/js/extensions/comment.js\");\n\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/main.js?");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/Attachment.vue?vue&type=script&lang=js&":
/*!**********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/Attachment.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! lodash.get */ \"./node_modules/lodash.get/index.js\");\n/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(lodash_get__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var file_extension__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! file-extension */ \"./node_modules/file-extension/file-extension.js\");\n/* harmony import */ var file_extension__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(file_extension__WEBPACK_IMPORTED_MODULE_1__);\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  props: {\n    id: Number,\n    media: Object,\n    progress: Number,\n    canDelete: Boolean\n  },\n  computed: {\n    sourceUrl() {\n      return lodash_get__WEBPACK_IMPORTED_MODULE_0___default()(this, \"media.source_url\");\n    },\n\n    url() {\n      return lodash_get__WEBPACK_IMPORTED_MODULE_0___default()(this, \"media.media_details.sizes.thumbnail.source_url\");\n    },\n\n    extension() {\n      return this.sourceUrl && file_extension__WEBPACK_IMPORTED_MODULE_1___default()(this.sourceUrl);\n    },\n\n    title() {\n      return lodash_get__WEBPACK_IMPORTED_MODULE_0___default()(this, \"media.title.rendered\");\n    }\n\n  }\n});\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/Attachment.vue?./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/AttachmentLoading.vue?vue&type=script&lang=js&":
/*!*****************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/AttachmentLoading.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  props: {\n    progress: Number\n  }\n});\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/AttachmentLoading.vue?./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/CommentAttachment.vue?vue&type=script&lang=js&":
/*!*****************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/CommentAttachment.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! lodash.get */ \"./node_modules/lodash.get/index.js\");\n/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(lodash_get__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _Attachment_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Attachment.vue */ \"./assets/src/front-end/js/components/Attachment.vue\");\n/* harmony import */ var _AttachmentLoading_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AttachmentLoading.vue */ \"./assets/src/front-end/js/components/AttachmentLoading.vue\");\n//\n//\n//\n//\n//\n//\n//\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  props: {\n    id: Number,\n    comment: Object\n  },\n  components: {\n    Attachment: _Attachment_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n    AttachmentLoading: _AttachmentLoading_vue__WEBPACK_IMPORTED_MODULE_2__[\"default\"]\n  },\n  computed: {\n    media() {\n      return this.$store.getters[\"entities/media/find\"](this.id);\n    },\n\n    isAuthor() {\n      return lodash_get__WEBPACK_IMPORTED_MODULE_0___default()(this, \"$store.state.entities.users.me.id\") == lodash_get__WEBPACK_IMPORTED_MODULE_0___default()(this, \"media.author\");\n    },\n\n    canModerate() {\n      return lodash_get__WEBPACK_IMPORTED_MODULE_0___default()(this, \"$store.state.entities.users.me.capabilities.moderate_comments\");\n    },\n\n    canDelete() {\n      return this.isAuthor || this.canModerate;\n    }\n\n  },\n  methods: {\n    confirmTrash() {\n      this.$store.commit(\"ui/SET_DIALOG\", {\n        name: \"dialog\",\n        title: this.__(\"Permanently delete this upload?\", \"project-huddle\"),\n        message: `<p>${this.__(\"Are you sure you want to delete this file? This is permanent.\", \"project-huddle\")}</p>`,\n        success: this.trash\n      });\n    },\n\n    trash() {\n      // remove from comment item\n      this.comment.$dispatch(\"update\", {\n        where: this.comment.id,\n        data: comment => {\n          // TODO: Bug - this is not updating unless we do this method\n          this.$delete(comment.attachment_ids, _.indexOf(comment.attachment_ids, this.media.id));\n        }\n      }); // perhaps tied to bug above?\n\n      this.$parent.$forceUpdate(); // save\n\n      this.comment.$dispatch(\"patch\", {\n        comment: this.comment,\n        data: {\n          attachment_ids: this.comment.attachment_ids.length === 0 ? \"\" : this.comment.attachment_ids\n        }\n      }); // cache\n\n      let attachment = this.media;\n\n      let link = lodash_get__WEBPACK_IMPORTED_MODULE_0___default()(this.media, \"_links.self[0].href\"); // delete\n\n\n      link && this.$http.delete(link, {\n        params: {\n          force: true\n        }\n      }).then(([data]) => {\n        this.$notification({\n          text: this.__(\"Attachment deleted.\", \"project-huddle\"),\n          duration: 5000\n        });\n      }).catch(err => {\n        this.$store.dispatch(\"entities/insert\", {\n          entity: \"media\",\n          data: attachment\n        }); //revert\n\n        this.$error(err);\n      });\n    }\n\n  }\n});\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/CommentAttachment.vue?./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/CommentAttachments.vue?vue&type=script&lang=js&":
/*!******************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/CommentAttachments.vue?vue&type=script&lang=js& ***!
  \******************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _CommentAttachment_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CommentAttachment.vue */ \"./assets/src/front-end/js/components/CommentAttachment.vue\");\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  components: {\n    CommentAttachment: _CommentAttachment_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]\n  },\n  props: {\n    comment: Object\n  },\n  computed: {\n    attachment_ids() {\n      return this.comment.attachment_ids;\n    }\n\n  }\n});\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/CommentAttachments.vue?./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/ThreadAttachment.vue?vue&type=script&lang=js&":
/*!****************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/ThreadAttachment.vue?vue&type=script&lang=js& ***!
  \****************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! lodash.get */ \"./node_modules/lodash.get/index.js\");\n/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(lodash_get__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _Attachment_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Attachment.vue */ \"./assets/src/front-end/js/components/Attachment.vue\");\n/* harmony import */ var _AttachmentLoading_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AttachmentLoading.vue */ \"./assets/src/front-end/js/components/AttachmentLoading.vue\");\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  props: {\n    id: Number | String,\n    thread: Object\n  },\n  components: {\n    Attachment: _Attachment_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n    AttachmentLoading: _AttachmentLoading_vue__WEBPACK_IMPORTED_MODULE_2__[\"default\"]\n  },\n\n  data() {\n    return {\n      progress: 100\n    };\n  },\n\n  mounted() {\n    console.log(this.thread);\n\n    if (!this.loaded) {\n      this.upload();\n    }\n  },\n\n  computed: {\n    media() {\n      // return this.$store.getters[\"entities/media/all\"]();\n      return this.$store.getters[\"entities/media/find\"](this.id);\n    },\n\n    loaded() {\n      return this.media && _.isNumber(this.media.id);\n    },\n\n    canDelete() {\n      if (!this.loaded) {\n        return false;\n      } // let author delete\n\n\n      if (lodash_get__WEBPACK_IMPORTED_MODULE_0___default()(this, \"$store.state.entities.users.me.id\") == this.media.author) {\n        return true;\n      } // let comment moderator delete\n\n\n      if (lodash_get__WEBPACK_IMPORTED_MODULE_0___default()(this, \"$store.state.entities.users.me.capabilities.moderate_comments\")) {\n        return true;\n      }\n\n      return false;\n    }\n\n  },\n  methods: {\n    confirmTrash() {\n      this.$store.commit(\"ui/SET_DIALOG\", {\n        name: \"dialog\",\n        title: this.__(\"Permanently delete this upload?\", \"project-huddle\"),\n        message: `<p>${this.__(\"Are you sure you want to delete this file? This is permanent.\", \"project-huddle\")}</p>`,\n        success: this.trash\n      });\n    },\n\n    trash() {\n      let link = lodash_get__WEBPACK_IMPORTED_MODULE_0___default()(this.media, \"_links.self[0].href\");\n\n      this.remove(); // delete\n\n      link && this.$http.delete(link, {\n        params: {\n          force: true\n        }\n      }).then(([data]) => {\n        this.$notification({\n          text: this.__(\"Attachment deleted.\", \"project-huddle\"),\n          duration: 5000\n        });\n      }).catch(err => {\n        this.$store.dispatch(\"entities/insert\", {\n          entity: \"media\",\n          data: attachment\n        }); //revert\n\n        this.$error(err);\n      });\n    },\n\n    async upload() {\n      this.thread.$update({\n        disabled: true\n      });\n      this.progress = 0;\n\n      if (!this.media || !this.media.file) {\n        return;\n      }\n\n      var formData = new FormData();\n      let data = {\n        file: this.media.file\n      };\n\n      if (this.media.post) {\n        data.post = this.media.post;\n      }\n\n      _.each(data, function (value, key) {\n        formData.append(key, value);\n      });\n\n      this.$http.post(\"/media\", {\n        data: formData,\n        options: {\n          async: true,\n          cache: false,\n          contentType: false,\n          processData: false,\n          xhr: () => {\n            // get the native XmlHttpRequest object\n            var xhr = jQuery.ajaxSettings.xhr(); // set the onprogress event handler\n\n            xhr.upload.onprogress = event => {\n              if (event.lengthComputable) {\n                // Trigger progress event on model for view updates\n                this.progress = event.loaded / event.total * 100;\n              }\n            }; // set the onload event handler\n\n\n            xhr.upload.onload = () => {\n              this.progress = 100;\n            }; // return the customized object\n\n\n            return xhr;\n          }\n        }\n      }).then(([data]) => {\n        this.progress = 100;\n        this.$store.dispatch(\"entities/insertOrUpdate\", {\n          entity: \"media\",\n          data\n        }).then(() => {\n          this.replace(data.id);\n        });\n      }).catch(err => {\n        this.$error(err);\n        this.media.$delete();\n      }).finally(() => {\n        this.thread.$update({\n          disabled: false\n        });\n      });\n    },\n\n    replace(id) {\n      let attachment_ids = _.without(this.thread.attachment_ids, this.media.id);\n\n      attachment_ids.push(id);\n      this.thread.$update({\n        attachment_ids\n      }).then(() => {\n        this.media.$delete();\n      });\n    },\n\n    remove() {\n      let attachment_ids = _.without(this.thread.attachment_ids, this.media.id);\n\n      this.thread.$update({\n        attachment_ids\n      }).then(() => {\n        this.media.$delete();\n      });\n    }\n\n  }\n});\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/ThreadAttachment.vue?./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/ThreadAttachments.vue?vue&type=script&lang=js&":
/*!*****************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/ThreadAttachments.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _ThreadAttachment_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ThreadAttachment.vue */ \"./assets/src/front-end/js/components/ThreadAttachment.vue\");\n/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! lodash.get */ \"./node_modules/lodash.get/index.js\");\n/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(lodash_get__WEBPACK_IMPORTED_MODULE_1__);\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  name: \"threadAttachment\",\n  components: {\n    ThreadAttachment: _ThreadAttachment_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]\n  },\n  props: {\n    id: Number | String\n  },\n  computed: {\n    thread() {\n      var _this$$store, _this$$store$getters, _this$$store2, _this$$store2$getters;\n\n      let getter = ((_this$$store = this.$store) === null || _this$$store === void 0 ? void 0 : (_this$$store$getters = _this$$store.getters) === null || _this$$store$getters === void 0 ? void 0 : _this$$store$getters[\"entities/mockup-threads/find\"]) || ((_this$$store2 = this.$store) === null || _this$$store2 === void 0 ? void 0 : (_this$$store2$getters = _this$$store2.getters) === null || _this$$store2$getters === void 0 ? void 0 : _this$$store2$getters[\"entities/website-threads/find\"]);\n      return getter ? getter(this.id) : {};\n    }\n\n  }\n});\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/ThreadAttachments.vue?./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/UploadIcon.vue?vue&type=script&lang=js&":
/*!**********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/UploadIcon.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! lodash.get */ \"./node_modules/lodash.get/index.js\");\n/* harmony import */ var lodash_get__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(lodash_get__WEBPACK_IMPORTED_MODULE_0__);\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  computed: {\n    accepts() {\n      return PHF_Settings.types;\n    }\n\n  },\n  methods: {\n    addFile() {\n      if (lodash_get__WEBPACK_IMPORTED_MODULE_0___default()(this, \"me.id\")) {\n        this.$refs.file.click();\n      } else {\n        ph.store.commit(\"ui/SET_DIALOG\", {\n          name: \"register\",\n          success: this.addFile\n        });\n      }\n    },\n\n    uploadImage(e) {\n      if (!this.$refs.file.files) {\n        return;\n      }\n\n      this.$emit(\"upload\", this.$refs.file.files);\n      this.$refs.file.value = \"\";\n    }\n\n  }\n});\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/UploadIcon.vue?./node_modules/babel-loader/lib!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/file-extension/file-extension.js":
/*!*******************************************************!*\
  !*** ./node_modules/file-extension/file-extension.js ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("/*! file-extension v4.0.5 | (c) silverwind | BSD license */\n\n\n(function(m) {\n  if (true) {\n    module.exports = m();\n  } else {}\n})(function() {\n  return function fileExtension(filename, opts) {\n    if (!opts) opts = {};\n    if (!filename) return \"\";\n    var ext = (/[^./\\\\]*$/.exec(filename) || [\"\"])[0];\n    return opts.preserveCase ? ext : ext.toLowerCase();\n  };\n});\n\n\n//# sourceURL=webpack:///./node_modules/file-extension/file-extension.js?");

/***/ }),

/***/ "./node_modules/lodash.get/index.js":
/*!******************************************!*\
  !*** ./node_modules/lodash.get/index.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("/* WEBPACK VAR INJECTION */(function(global) {/**\n * lodash (Custom Build) <https://lodash.com/>\n * Build: `lodash modularize exports=\"npm\" -o ./`\n * Copyright jQuery Foundation and other contributors <https://jquery.org/>\n * Released under MIT license <https://lodash.com/license>\n * Based on Underscore.js 1.8.3 <http://underscorejs.org/LICENSE>\n * Copyright Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors\n */\n\n/** Used as the `TypeError` message for \"Functions\" methods. */\nvar FUNC_ERROR_TEXT = 'Expected a function';\n\n/** Used to stand-in for `undefined` hash values. */\nvar HASH_UNDEFINED = '__lodash_hash_undefined__';\n\n/** Used as references for various `Number` constants. */\nvar INFINITY = 1 / 0;\n\n/** `Object#toString` result references. */\nvar funcTag = '[object Function]',\n    genTag = '[object GeneratorFunction]',\n    symbolTag = '[object Symbol]';\n\n/** Used to match property names within property paths. */\nvar reIsDeepProp = /\\.|\\[(?:[^[\\]]*|([\"'])(?:(?!\\1)[^\\\\]|\\\\.)*?\\1)\\]/,\n    reIsPlainProp = /^\\w*$/,\n    reLeadingDot = /^\\./,\n    rePropName = /[^.[\\]]+|\\[(?:(-?\\d+(?:\\.\\d+)?)|([\"'])((?:(?!\\2)[^\\\\]|\\\\.)*?)\\2)\\]|(?=(?:\\.|\\[\\])(?:\\.|\\[\\]|$))/g;\n\n/**\n * Used to match `RegExp`\n * [syntax characters](http://ecma-international.org/ecma-262/7.0/#sec-patterns).\n */\nvar reRegExpChar = /[\\\\^$.*+?()[\\]{}|]/g;\n\n/** Used to match backslashes in property paths. */\nvar reEscapeChar = /\\\\(\\\\)?/g;\n\n/** Used to detect host constructors (Safari). */\nvar reIsHostCtor = /^\\[object .+?Constructor\\]$/;\n\n/** Detect free variable `global` from Node.js. */\nvar freeGlobal = typeof global == 'object' && global && global.Object === Object && global;\n\n/** Detect free variable `self`. */\nvar freeSelf = typeof self == 'object' && self && self.Object === Object && self;\n\n/** Used as a reference to the global object. */\nvar root = freeGlobal || freeSelf || Function('return this')();\n\n/**\n * Gets the value at `key` of `object`.\n *\n * @private\n * @param {Object} [object] The object to query.\n * @param {string} key The key of the property to get.\n * @returns {*} Returns the property value.\n */\nfunction getValue(object, key) {\n  return object == null ? undefined : object[key];\n}\n\n/**\n * Checks if `value` is a host object in IE < 9.\n *\n * @private\n * @param {*} value The value to check.\n * @returns {boolean} Returns `true` if `value` is a host object, else `false`.\n */\nfunction isHostObject(value) {\n  // Many host objects are `Object` objects that can coerce to strings\n  // despite having improperly defined `toString` methods.\n  var result = false;\n  if (value != null && typeof value.toString != 'function') {\n    try {\n      result = !!(value + '');\n    } catch (e) {}\n  }\n  return result;\n}\n\n/** Used for built-in method references. */\nvar arrayProto = Array.prototype,\n    funcProto = Function.prototype,\n    objectProto = Object.prototype;\n\n/** Used to detect overreaching core-js shims. */\nvar coreJsData = root['__core-js_shared__'];\n\n/** Used to detect methods masquerading as native. */\nvar maskSrcKey = (function() {\n  var uid = /[^.]+$/.exec(coreJsData && coreJsData.keys && coreJsData.keys.IE_PROTO || '');\n  return uid ? ('Symbol(src)_1.' + uid) : '';\n}());\n\n/** Used to resolve the decompiled source of functions. */\nvar funcToString = funcProto.toString;\n\n/** Used to check objects for own properties. */\nvar hasOwnProperty = objectProto.hasOwnProperty;\n\n/**\n * Used to resolve the\n * [`toStringTag`](http://ecma-international.org/ecma-262/7.0/#sec-object.prototype.tostring)\n * of values.\n */\nvar objectToString = objectProto.toString;\n\n/** Used to detect if a method is native. */\nvar reIsNative = RegExp('^' +\n  funcToString.call(hasOwnProperty).replace(reRegExpChar, '\\\\$&')\n  .replace(/hasOwnProperty|(function).*?(?=\\\\\\()| for .+?(?=\\\\\\])/g, '$1.*?') + '$'\n);\n\n/** Built-in value references. */\nvar Symbol = root.Symbol,\n    splice = arrayProto.splice;\n\n/* Built-in method references that are verified to be native. */\nvar Map = getNative(root, 'Map'),\n    nativeCreate = getNative(Object, 'create');\n\n/** Used to convert symbols to primitives and strings. */\nvar symbolProto = Symbol ? Symbol.prototype : undefined,\n    symbolToString = symbolProto ? symbolProto.toString : undefined;\n\n/**\n * Creates a hash object.\n *\n * @private\n * @constructor\n * @param {Array} [entries] The key-value pairs to cache.\n */\nfunction Hash(entries) {\n  var index = -1,\n      length = entries ? entries.length : 0;\n\n  this.clear();\n  while (++index < length) {\n    var entry = entries[index];\n    this.set(entry[0], entry[1]);\n  }\n}\n\n/**\n * Removes all key-value entries from the hash.\n *\n * @private\n * @name clear\n * @memberOf Hash\n */\nfunction hashClear() {\n  this.__data__ = nativeCreate ? nativeCreate(null) : {};\n}\n\n/**\n * Removes `key` and its value from the hash.\n *\n * @private\n * @name delete\n * @memberOf Hash\n * @param {Object} hash The hash to modify.\n * @param {string} key The key of the value to remove.\n * @returns {boolean} Returns `true` if the entry was removed, else `false`.\n */\nfunction hashDelete(key) {\n  return this.has(key) && delete this.__data__[key];\n}\n\n/**\n * Gets the hash value for `key`.\n *\n * @private\n * @name get\n * @memberOf Hash\n * @param {string} key The key of the value to get.\n * @returns {*} Returns the entry value.\n */\nfunction hashGet(key) {\n  var data = this.__data__;\n  if (nativeCreate) {\n    var result = data[key];\n    return result === HASH_UNDEFINED ? undefined : result;\n  }\n  return hasOwnProperty.call(data, key) ? data[key] : undefined;\n}\n\n/**\n * Checks if a hash value for `key` exists.\n *\n * @private\n * @name has\n * @memberOf Hash\n * @param {string} key The key of the entry to check.\n * @returns {boolean} Returns `true` if an entry for `key` exists, else `false`.\n */\nfunction hashHas(key) {\n  var data = this.__data__;\n  return nativeCreate ? data[key] !== undefined : hasOwnProperty.call(data, key);\n}\n\n/**\n * Sets the hash `key` to `value`.\n *\n * @private\n * @name set\n * @memberOf Hash\n * @param {string} key The key of the value to set.\n * @param {*} value The value to set.\n * @returns {Object} Returns the hash instance.\n */\nfunction hashSet(key, value) {\n  var data = this.__data__;\n  data[key] = (nativeCreate && value === undefined) ? HASH_UNDEFINED : value;\n  return this;\n}\n\n// Add methods to `Hash`.\nHash.prototype.clear = hashClear;\nHash.prototype['delete'] = hashDelete;\nHash.prototype.get = hashGet;\nHash.prototype.has = hashHas;\nHash.prototype.set = hashSet;\n\n/**\n * Creates an list cache object.\n *\n * @private\n * @constructor\n * @param {Array} [entries] The key-value pairs to cache.\n */\nfunction ListCache(entries) {\n  var index = -1,\n      length = entries ? entries.length : 0;\n\n  this.clear();\n  while (++index < length) {\n    var entry = entries[index];\n    this.set(entry[0], entry[1]);\n  }\n}\n\n/**\n * Removes all key-value entries from the list cache.\n *\n * @private\n * @name clear\n * @memberOf ListCache\n */\nfunction listCacheClear() {\n  this.__data__ = [];\n}\n\n/**\n * Removes `key` and its value from the list cache.\n *\n * @private\n * @name delete\n * @memberOf ListCache\n * @param {string} key The key of the value to remove.\n * @returns {boolean} Returns `true` if the entry was removed, else `false`.\n */\nfunction listCacheDelete(key) {\n  var data = this.__data__,\n      index = assocIndexOf(data, key);\n\n  if (index < 0) {\n    return false;\n  }\n  var lastIndex = data.length - 1;\n  if (index == lastIndex) {\n    data.pop();\n  } else {\n    splice.call(data, index, 1);\n  }\n  return true;\n}\n\n/**\n * Gets the list cache value for `key`.\n *\n * @private\n * @name get\n * @memberOf ListCache\n * @param {string} key The key of the value to get.\n * @returns {*} Returns the entry value.\n */\nfunction listCacheGet(key) {\n  var data = this.__data__,\n      index = assocIndexOf(data, key);\n\n  return index < 0 ? undefined : data[index][1];\n}\n\n/**\n * Checks if a list cache value for `key` exists.\n *\n * @private\n * @name has\n * @memberOf ListCache\n * @param {string} key The key of the entry to check.\n * @returns {boolean} Returns `true` if an entry for `key` exists, else `false`.\n */\nfunction listCacheHas(key) {\n  return assocIndexOf(this.__data__, key) > -1;\n}\n\n/**\n * Sets the list cache `key` to `value`.\n *\n * @private\n * @name set\n * @memberOf ListCache\n * @param {string} key The key of the value to set.\n * @param {*} value The value to set.\n * @returns {Object} Returns the list cache instance.\n */\nfunction listCacheSet(key, value) {\n  var data = this.__data__,\n      index = assocIndexOf(data, key);\n\n  if (index < 0) {\n    data.push([key, value]);\n  } else {\n    data[index][1] = value;\n  }\n  return this;\n}\n\n// Add methods to `ListCache`.\nListCache.prototype.clear = listCacheClear;\nListCache.prototype['delete'] = listCacheDelete;\nListCache.prototype.get = listCacheGet;\nListCache.prototype.has = listCacheHas;\nListCache.prototype.set = listCacheSet;\n\n/**\n * Creates a map cache object to store key-value pairs.\n *\n * @private\n * @constructor\n * @param {Array} [entries] The key-value pairs to cache.\n */\nfunction MapCache(entries) {\n  var index = -1,\n      length = entries ? entries.length : 0;\n\n  this.clear();\n  while (++index < length) {\n    var entry = entries[index];\n    this.set(entry[0], entry[1]);\n  }\n}\n\n/**\n * Removes all key-value entries from the map.\n *\n * @private\n * @name clear\n * @memberOf MapCache\n */\nfunction mapCacheClear() {\n  this.__data__ = {\n    'hash': new Hash,\n    'map': new (Map || ListCache),\n    'string': new Hash\n  };\n}\n\n/**\n * Removes `key` and its value from the map.\n *\n * @private\n * @name delete\n * @memberOf MapCache\n * @param {string} key The key of the value to remove.\n * @returns {boolean} Returns `true` if the entry was removed, else `false`.\n */\nfunction mapCacheDelete(key) {\n  return getMapData(this, key)['delete'](key);\n}\n\n/**\n * Gets the map value for `key`.\n *\n * @private\n * @name get\n * @memberOf MapCache\n * @param {string} key The key of the value to get.\n * @returns {*} Returns the entry value.\n */\nfunction mapCacheGet(key) {\n  return getMapData(this, key).get(key);\n}\n\n/**\n * Checks if a map value for `key` exists.\n *\n * @private\n * @name has\n * @memberOf MapCache\n * @param {string} key The key of the entry to check.\n * @returns {boolean} Returns `true` if an entry for `key` exists, else `false`.\n */\nfunction mapCacheHas(key) {\n  return getMapData(this, key).has(key);\n}\n\n/**\n * Sets the map `key` to `value`.\n *\n * @private\n * @name set\n * @memberOf MapCache\n * @param {string} key The key of the value to set.\n * @param {*} value The value to set.\n * @returns {Object} Returns the map cache instance.\n */\nfunction mapCacheSet(key, value) {\n  getMapData(this, key).set(key, value);\n  return this;\n}\n\n// Add methods to `MapCache`.\nMapCache.prototype.clear = mapCacheClear;\nMapCache.prototype['delete'] = mapCacheDelete;\nMapCache.prototype.get = mapCacheGet;\nMapCache.prototype.has = mapCacheHas;\nMapCache.prototype.set = mapCacheSet;\n\n/**\n * Gets the index at which the `key` is found in `array` of key-value pairs.\n *\n * @private\n * @param {Array} array The array to inspect.\n * @param {*} key The key to search for.\n * @returns {number} Returns the index of the matched value, else `-1`.\n */\nfunction assocIndexOf(array, key) {\n  var length = array.length;\n  while (length--) {\n    if (eq(array[length][0], key)) {\n      return length;\n    }\n  }\n  return -1;\n}\n\n/**\n * The base implementation of `_.get` without support for default values.\n *\n * @private\n * @param {Object} object The object to query.\n * @param {Array|string} path The path of the property to get.\n * @returns {*} Returns the resolved value.\n */\nfunction baseGet(object, path) {\n  path = isKey(path, object) ? [path] : castPath(path);\n\n  var index = 0,\n      length = path.length;\n\n  while (object != null && index < length) {\n    object = object[toKey(path[index++])];\n  }\n  return (index && index == length) ? object : undefined;\n}\n\n/**\n * The base implementation of `_.isNative` without bad shim checks.\n *\n * @private\n * @param {*} value The value to check.\n * @returns {boolean} Returns `true` if `value` is a native function,\n *  else `false`.\n */\nfunction baseIsNative(value) {\n  if (!isObject(value) || isMasked(value)) {\n    return false;\n  }\n  var pattern = (isFunction(value) || isHostObject(value)) ? reIsNative : reIsHostCtor;\n  return pattern.test(toSource(value));\n}\n\n/**\n * The base implementation of `_.toString` which doesn't convert nullish\n * values to empty strings.\n *\n * @private\n * @param {*} value The value to process.\n * @returns {string} Returns the string.\n */\nfunction baseToString(value) {\n  // Exit early for strings to avoid a performance hit in some environments.\n  if (typeof value == 'string') {\n    return value;\n  }\n  if (isSymbol(value)) {\n    return symbolToString ? symbolToString.call(value) : '';\n  }\n  var result = (value + '');\n  return (result == '0' && (1 / value) == -INFINITY) ? '-0' : result;\n}\n\n/**\n * Casts `value` to a path array if it's not one.\n *\n * @private\n * @param {*} value The value to inspect.\n * @returns {Array} Returns the cast property path array.\n */\nfunction castPath(value) {\n  return isArray(value) ? value : stringToPath(value);\n}\n\n/**\n * Gets the data for `map`.\n *\n * @private\n * @param {Object} map The map to query.\n * @param {string} key The reference key.\n * @returns {*} Returns the map data.\n */\nfunction getMapData(map, key) {\n  var data = map.__data__;\n  return isKeyable(key)\n    ? data[typeof key == 'string' ? 'string' : 'hash']\n    : data.map;\n}\n\n/**\n * Gets the native function at `key` of `object`.\n *\n * @private\n * @param {Object} object The object to query.\n * @param {string} key The key of the method to get.\n * @returns {*} Returns the function if it's native, else `undefined`.\n */\nfunction getNative(object, key) {\n  var value = getValue(object, key);\n  return baseIsNative(value) ? value : undefined;\n}\n\n/**\n * Checks if `value` is a property name and not a property path.\n *\n * @private\n * @param {*} value The value to check.\n * @param {Object} [object] The object to query keys on.\n * @returns {boolean} Returns `true` if `value` is a property name, else `false`.\n */\nfunction isKey(value, object) {\n  if (isArray(value)) {\n    return false;\n  }\n  var type = typeof value;\n  if (type == 'number' || type == 'symbol' || type == 'boolean' ||\n      value == null || isSymbol(value)) {\n    return true;\n  }\n  return reIsPlainProp.test(value) || !reIsDeepProp.test(value) ||\n    (object != null && value in Object(object));\n}\n\n/**\n * Checks if `value` is suitable for use as unique object key.\n *\n * @private\n * @param {*} value The value to check.\n * @returns {boolean} Returns `true` if `value` is suitable, else `false`.\n */\nfunction isKeyable(value) {\n  var type = typeof value;\n  return (type == 'string' || type == 'number' || type == 'symbol' || type == 'boolean')\n    ? (value !== '__proto__')\n    : (value === null);\n}\n\n/**\n * Checks if `func` has its source masked.\n *\n * @private\n * @param {Function} func The function to check.\n * @returns {boolean} Returns `true` if `func` is masked, else `false`.\n */\nfunction isMasked(func) {\n  return !!maskSrcKey && (maskSrcKey in func);\n}\n\n/**\n * Converts `string` to a property path array.\n *\n * @private\n * @param {string} string The string to convert.\n * @returns {Array} Returns the property path array.\n */\nvar stringToPath = memoize(function(string) {\n  string = toString(string);\n\n  var result = [];\n  if (reLeadingDot.test(string)) {\n    result.push('');\n  }\n  string.replace(rePropName, function(match, number, quote, string) {\n    result.push(quote ? string.replace(reEscapeChar, '$1') : (number || match));\n  });\n  return result;\n});\n\n/**\n * Converts `value` to a string key if it's not a string or symbol.\n *\n * @private\n * @param {*} value The value to inspect.\n * @returns {string|symbol} Returns the key.\n */\nfunction toKey(value) {\n  if (typeof value == 'string' || isSymbol(value)) {\n    return value;\n  }\n  var result = (value + '');\n  return (result == '0' && (1 / value) == -INFINITY) ? '-0' : result;\n}\n\n/**\n * Converts `func` to its source code.\n *\n * @private\n * @param {Function} func The function to process.\n * @returns {string} Returns the source code.\n */\nfunction toSource(func) {\n  if (func != null) {\n    try {\n      return funcToString.call(func);\n    } catch (e) {}\n    try {\n      return (func + '');\n    } catch (e) {}\n  }\n  return '';\n}\n\n/**\n * Creates a function that memoizes the result of `func`. If `resolver` is\n * provided, it determines the cache key for storing the result based on the\n * arguments provided to the memoized function. By default, the first argument\n * provided to the memoized function is used as the map cache key. The `func`\n * is invoked with the `this` binding of the memoized function.\n *\n * **Note:** The cache is exposed as the `cache` property on the memoized\n * function. Its creation may be customized by replacing the `_.memoize.Cache`\n * constructor with one whose instances implement the\n * [`Map`](http://ecma-international.org/ecma-262/7.0/#sec-properties-of-the-map-prototype-object)\n * method interface of `delete`, `get`, `has`, and `set`.\n *\n * @static\n * @memberOf _\n * @since 0.1.0\n * @category Function\n * @param {Function} func The function to have its output memoized.\n * @param {Function} [resolver] The function to resolve the cache key.\n * @returns {Function} Returns the new memoized function.\n * @example\n *\n * var object = { 'a': 1, 'b': 2 };\n * var other = { 'c': 3, 'd': 4 };\n *\n * var values = _.memoize(_.values);\n * values(object);\n * // => [1, 2]\n *\n * values(other);\n * // => [3, 4]\n *\n * object.a = 2;\n * values(object);\n * // => [1, 2]\n *\n * // Modify the result cache.\n * values.cache.set(object, ['a', 'b']);\n * values(object);\n * // => ['a', 'b']\n *\n * // Replace `_.memoize.Cache`.\n * _.memoize.Cache = WeakMap;\n */\nfunction memoize(func, resolver) {\n  if (typeof func != 'function' || (resolver && typeof resolver != 'function')) {\n    throw new TypeError(FUNC_ERROR_TEXT);\n  }\n  var memoized = function() {\n    var args = arguments,\n        key = resolver ? resolver.apply(this, args) : args[0],\n        cache = memoized.cache;\n\n    if (cache.has(key)) {\n      return cache.get(key);\n    }\n    var result = func.apply(this, args);\n    memoized.cache = cache.set(key, result);\n    return result;\n  };\n  memoized.cache = new (memoize.Cache || MapCache);\n  return memoized;\n}\n\n// Assign cache to `_.memoize`.\nmemoize.Cache = MapCache;\n\n/**\n * Performs a\n * [`SameValueZero`](http://ecma-international.org/ecma-262/7.0/#sec-samevaluezero)\n * comparison between two values to determine if they are equivalent.\n *\n * @static\n * @memberOf _\n * @since 4.0.0\n * @category Lang\n * @param {*} value The value to compare.\n * @param {*} other The other value to compare.\n * @returns {boolean} Returns `true` if the values are equivalent, else `false`.\n * @example\n *\n * var object = { 'a': 1 };\n * var other = { 'a': 1 };\n *\n * _.eq(object, object);\n * // => true\n *\n * _.eq(object, other);\n * // => false\n *\n * _.eq('a', 'a');\n * // => true\n *\n * _.eq('a', Object('a'));\n * // => false\n *\n * _.eq(NaN, NaN);\n * // => true\n */\nfunction eq(value, other) {\n  return value === other || (value !== value && other !== other);\n}\n\n/**\n * Checks if `value` is classified as an `Array` object.\n *\n * @static\n * @memberOf _\n * @since 0.1.0\n * @category Lang\n * @param {*} value The value to check.\n * @returns {boolean} Returns `true` if `value` is an array, else `false`.\n * @example\n *\n * _.isArray([1, 2, 3]);\n * // => true\n *\n * _.isArray(document.body.children);\n * // => false\n *\n * _.isArray('abc');\n * // => false\n *\n * _.isArray(_.noop);\n * // => false\n */\nvar isArray = Array.isArray;\n\n/**\n * Checks if `value` is classified as a `Function` object.\n *\n * @static\n * @memberOf _\n * @since 0.1.0\n * @category Lang\n * @param {*} value The value to check.\n * @returns {boolean} Returns `true` if `value` is a function, else `false`.\n * @example\n *\n * _.isFunction(_);\n * // => true\n *\n * _.isFunction(/abc/);\n * // => false\n */\nfunction isFunction(value) {\n  // The use of `Object#toString` avoids issues with the `typeof` operator\n  // in Safari 8-9 which returns 'object' for typed array and other constructors.\n  var tag = isObject(value) ? objectToString.call(value) : '';\n  return tag == funcTag || tag == genTag;\n}\n\n/**\n * Checks if `value` is the\n * [language type](http://www.ecma-international.org/ecma-262/7.0/#sec-ecmascript-language-types)\n * of `Object`. (e.g. arrays, functions, objects, regexes, `new Number(0)`, and `new String('')`)\n *\n * @static\n * @memberOf _\n * @since 0.1.0\n * @category Lang\n * @param {*} value The value to check.\n * @returns {boolean} Returns `true` if `value` is an object, else `false`.\n * @example\n *\n * _.isObject({});\n * // => true\n *\n * _.isObject([1, 2, 3]);\n * // => true\n *\n * _.isObject(_.noop);\n * // => true\n *\n * _.isObject(null);\n * // => false\n */\nfunction isObject(value) {\n  var type = typeof value;\n  return !!value && (type == 'object' || type == 'function');\n}\n\n/**\n * Checks if `value` is object-like. A value is object-like if it's not `null`\n * and has a `typeof` result of \"object\".\n *\n * @static\n * @memberOf _\n * @since 4.0.0\n * @category Lang\n * @param {*} value The value to check.\n * @returns {boolean} Returns `true` if `value` is object-like, else `false`.\n * @example\n *\n * _.isObjectLike({});\n * // => true\n *\n * _.isObjectLike([1, 2, 3]);\n * // => true\n *\n * _.isObjectLike(_.noop);\n * // => false\n *\n * _.isObjectLike(null);\n * // => false\n */\nfunction isObjectLike(value) {\n  return !!value && typeof value == 'object';\n}\n\n/**\n * Checks if `value` is classified as a `Symbol` primitive or object.\n *\n * @static\n * @memberOf _\n * @since 4.0.0\n * @category Lang\n * @param {*} value The value to check.\n * @returns {boolean} Returns `true` if `value` is a symbol, else `false`.\n * @example\n *\n * _.isSymbol(Symbol.iterator);\n * // => true\n *\n * _.isSymbol('abc');\n * // => false\n */\nfunction isSymbol(value) {\n  return typeof value == 'symbol' ||\n    (isObjectLike(value) && objectToString.call(value) == symbolTag);\n}\n\n/**\n * Converts `value` to a string. An empty string is returned for `null`\n * and `undefined` values. The sign of `-0` is preserved.\n *\n * @static\n * @memberOf _\n * @since 4.0.0\n * @category Lang\n * @param {*} value The value to process.\n * @returns {string} Returns the string.\n * @example\n *\n * _.toString(null);\n * // => ''\n *\n * _.toString(-0);\n * // => '-0'\n *\n * _.toString([1, 2, 3]);\n * // => '1,2,3'\n */\nfunction toString(value) {\n  return value == null ? '' : baseToString(value);\n}\n\n/**\n * Gets the value at `path` of `object`. If the resolved value is\n * `undefined`, the `defaultValue` is returned in its place.\n *\n * @static\n * @memberOf _\n * @since 3.7.0\n * @category Object\n * @param {Object} object The object to query.\n * @param {Array|string} path The path of the property to get.\n * @param {*} [defaultValue] The value returned for `undefined` resolved values.\n * @returns {*} Returns the resolved value.\n * @example\n *\n * var object = { 'a': [{ 'b': { 'c': 3 } }] };\n *\n * _.get(object, 'a[0].b.c');\n * // => 3\n *\n * _.get(object, ['a', '0', 'b', 'c']);\n * // => 3\n *\n * _.get(object, 'a.b.c', 'default');\n * // => 'default'\n */\nfunction get(object, path, defaultValue) {\n  var result = object == null ? undefined : baseGet(object, path);\n  return result === undefined ? defaultValue : result;\n}\n\nmodule.exports = get;\n\n/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../webpack/buildin/global.js */ \"./node_modules/webpack/buildin/global.js\")))\n\n//# sourceURL=webpack:///./node_modules/lodash.get/index.js?");

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/Attachment.vue?vue&type=template&id=9db8c732&":
/*!************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/Attachment.vue?vue&type=template&id=9db8c732& ***!
  \************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\"div\", { staticClass: \"ph-file-attachment-thumbnail\" }, [\n    _c(\n      \"div\",\n      {\n        staticClass: \"thumb-icon\",\n        style: [_vm.url ? { \"background-image\": \"url(\" + _vm.url + \")\" } : {}],\n      },\n      [\n        _vm.progress < 100\n          ? [\n              _vm._m(0),\n              _vm._v(\" \"),\n              _c(\"div\", { staticClass: \"ph-upload-progress\" }, [\n                _c(\"div\", {\n                  staticClass: \"ph-upload-progress-indicator\",\n                  style: { width: _vm.progress + \"%\" },\n                }),\n              ]),\n            ]\n          : [\n              _vm.canDelete\n                ? _c(\n                    \"div\",\n                    {\n                      staticClass: \"ph-close-icon ph-tooltip-wrap\",\n                      on: {\n                        click: function ($event) {\n                          $event.preventDefault()\n                          return _vm.$emit(\"delete\")\n                        },\n                      },\n                    },\n                    [\n                      _c(\n                        \"svg\",\n                        {\n                          attrs: {\n                            viewBox: \"0 0 512 512\",\n                            id: \"ion-android-close\",\n                            width: \"9\",\n                            height: \"9\",\n                          },\n                        },\n                        [\n                          _c(\"path\", {\n                            attrs: {\n                              d: \"M405 136.798L375.202 107 256 226.202 136.798 107 107 136.798 226.202 256 107 375.202 136.798 405 256 285.798 375.202 405 405 375.202 285.798 256z\",\n                            },\n                          }),\n                        ]\n                      ),\n                      _vm._v(\" \"),\n                      _c(\"div\", { staticClass: \"ph-tooltip\" }, [\n                        _vm._v(_vm._s(_vm.__(\"Delete\", \"project-huddle\"))),\n                      ]),\n                    ]\n                  )\n                : _vm._e(),\n              _vm._v(\" \"),\n              !_vm.url\n                ? _c(\"div\", { staticClass: \"ph-generic-attachment-icon\" }, [\n                    _vm._v(_vm._s(_vm.extension)),\n                  ])\n                : _vm._e(),\n              _vm._v(\" \"),\n              _c(\n                \"a\",\n                {\n                  staticClass: \"ph-download\",\n                  attrs: {\n                    href: _vm.sourceUrl,\n                    download: _vm.title,\n                    target: \"_blank\",\n                  },\n                },\n                [\n                  _c(\n                    \"svg\",\n                    {\n                      attrs: {\n                        viewBox: \"0 0 512 512\",\n                        id: \"ion-android-download\",\n                        width: \"18\",\n                        height: \"18\",\n                        fill: \"#fff\",\n                      },\n                    },\n                    [\n                      _c(\"path\", {\n                        attrs: {\n                          d: \"M403.002 217.001C388.998 148.002 328.998 96 256 96c-57.998 0-107.998 32.998-132.998 81.001C63.002 183.002 16 233.998 16 296c0 65.996 53.999 120 120 120h260c55 0 100-45 100-100 0-52.998-40.996-96.001-92.998-98.999zM224 268v-76h64v76h68L256 368 156 268h68z\",\n                        },\n                      }),\n                    ]\n                  ),\n                ]\n              ),\n            ],\n      ],\n      2\n    ),\n    _vm._v(\" \"),\n    _c(\n      \"div\",\n      {\n        staticClass: \"ph-attachment-title\",\n        attrs: { \"data-text\": _vm.title || false },\n      },\n      [_vm._v(_vm._s(_vm.title))]\n    ),\n  ])\n}\nvar staticRenderFns = [\n  function () {\n    var _vm = this\n    var _h = _vm.$createElement\n    var _c = _vm._self._c || _h\n    return _c(\"div\", { staticClass: \"ph-loading-image light\" }, [\n      _c(\"div\", { staticClass: \"ph-loading-image-dots\" }),\n    ])\n  },\n]\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/Attachment.vue?./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/AttachmentLoading.vue?vue&type=template&id=e03705f6&":
/*!*******************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/AttachmentLoading.vue?vue&type=template&id=e03705f6& ***!
  \*******************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\"div\", { staticClass: \"ph-file-attachment-thumbnail\" }, [\n    _c(\"div\", { staticClass: \"thumb-icon\" }, [\n      _vm._m(0),\n      _vm._v(\" \"),\n      _c(\"div\", { staticClass: \"ph-upload-progress\" }, [\n        _c(\"div\", {\n          staticClass: \"ph-upload-progress-indicator\",\n          style: { width: _vm.progress + \"%\" },\n        }),\n      ]),\n    ]),\n    _vm._v(\" \"),\n    _c(\"div\", { staticClass: \"ph-attachment-title\" }),\n  ])\n}\nvar staticRenderFns = [\n  function () {\n    var _vm = this\n    var _h = _vm.$createElement\n    var _c = _vm._self._c || _h\n    return _c(\"div\", { staticClass: \"ph-loading-image light\" }, [\n      _c(\"div\", { staticClass: \"ph-loading-image-dots\" }),\n    ])\n  },\n]\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/AttachmentLoading.vue?./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/CommentAttachment.vue?vue&type=template&id=da15f264&":
/*!*******************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/CommentAttachment.vue?vue&type=template&id=da15f264& ***!
  \*******************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\n    \"div\",\n    { staticClass: \"ph-comment__attachment\" },\n    [\n      _vm.media\n        ? _c(\"Attachment\", {\n            attrs: { media: _vm.media, canDelete: _vm.canDelete },\n            on: { delete: _vm.confirmTrash },\n          })\n        : _c(\"AttachmentLoading\"),\n    ],\n    1\n  )\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/CommentAttachment.vue?./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/CommentAttachments.vue?vue&type=template&id=615117d6&":
/*!********************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/CommentAttachments.vue?vue&type=template&id=615117d6& ***!
  \********************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\n    \"div\",\n    { staticClass: \"ph-comment-attachment-container\" },\n    _vm._l(_vm.attachment_ids, function (id) {\n      return _c(\"CommentAttachment\", {\n        key: id,\n        staticClass: \"ph-comment__attachment\",\n        attrs: { id: id, comment: _vm.comment },\n      })\n    }),\n    1\n  )\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/CommentAttachments.vue?./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/ThreadAttachment.vue?vue&type=template&id=021867de&":
/*!******************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/ThreadAttachment.vue?vue&type=template&id=021867de& ***!
  \******************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\n    \"div\",\n    { staticClass: \"ph-comment__attachment\" },\n    [\n      _vm.loaded\n        ? [\n            _c(\"Attachment\", {\n              attrs: { media: _vm.media, canDelete: _vm.canDelete },\n              on: { delete: _vm.confirmTrash },\n            }),\n          ]\n        : [_c(\"AttachmentLoading\", { attrs: { progress: _vm.progress } })],\n    ],\n    2\n  )\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/ThreadAttachment.vue?./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/ThreadAttachments.vue?vue&type=template&id=399d519c&":
/*!*******************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/ThreadAttachments.vue?vue&type=template&id=399d519c& ***!
  \*******************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _vm.thread && _vm.thread.attachment_ids.length\n    ? _c(\n        \"div\",\n        {\n          staticClass: \"ph-attachment-container\",\n          staticStyle: { \"margin-bottom\": \"0\" },\n        },\n        _vm._l(_vm.thread.attachment_ids, function (id) {\n          return _c(\"ThreadAttachment\", {\n            key: id,\n            attrs: { id: id, thread: _vm.thread },\n          })\n        }),\n        1\n      )\n    : _vm._e()\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/ThreadAttachments.vue?./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./assets/src/front-end/js/components/UploadIcon.vue?vue&type=template&id=45a4c5de&":
/*!************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./assets/src/front-end/js/components/UploadIcon.vue?vue&type=template&id=45a4c5de& ***!
  \************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\n    \"div\",\n    { staticClass: \"ph-file-attachment-icon ph-cursor-pointer\" },\n    [\n      _c(\n        \"div\",\n        {\n          staticClass: \"ph-tooltip-wrap ph-add-file\",\n          on: {\n            click: function ($event) {\n              $event.preventDefault()\n              return _vm.addFile.apply(null, arguments)\n            },\n          },\n        },\n        [\n          _c(\n            \"svg\",\n            {\n              staticClass: \"feather feather-paperclip\",\n              staticStyle: { fill: \"none\" },\n              attrs: {\n                xmlns: \"http://www.w3.org/2000/svg\",\n                width: \"16\",\n                height: \"16\",\n                viewBox: \"0 0 24 24\",\n                fill: \"none\",\n                stroke: \"currentColor\",\n                \"stroke-width\": \"2\",\n                \"stroke-linecap\": \"round\",\n                \"stroke-linejoin\": \"round\",\n              },\n            },\n            [\n              _c(\"path\", {\n                attrs: {\n                  d: \"M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48\",\n                },\n              }),\n            ]\n          ),\n          _vm._v(\" \"),\n          _c(\"div\", { staticClass: \"ph-tooltip\" }, [\n            _vm._v(_vm._s(_vm.__(\"Attach A File\", \"project-huddle\"))),\n          ]),\n        ]\n      ),\n      _vm._v(\" \"),\n      _c(\"input\", {\n        ref: \"file\",\n        staticClass: \"ph-file-input\",\n        staticStyle: { display: \"none\" },\n        attrs: { type: \"file\", accept: _vm.accepts },\n        on: { change: _vm.uploadImage },\n      }),\n    ]\n  )\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack:///./assets/src/front-end/js/components/UploadIcon.vue?./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options");

/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"default\", function() { return normalizeComponent; });\n/* globals __VUE_SSR_CONTEXT__ */\n\n// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).\n// This module is a runtime utility for cleaner component module output and will\n// be included in the final webpack user bundle.\n\nfunction normalizeComponent (\n  scriptExports,\n  render,\n  staticRenderFns,\n  functionalTemplate,\n  injectStyles,\n  scopeId,\n  moduleIdentifier, /* server only */\n  shadowMode /* vue-cli only */\n) {\n  // Vue.extend constructor export interop\n  var options = typeof scriptExports === 'function'\n    ? scriptExports.options\n    : scriptExports\n\n  // render functions\n  if (render) {\n    options.render = render\n    options.staticRenderFns = staticRenderFns\n    options._compiled = true\n  }\n\n  // functional template\n  if (functionalTemplate) {\n    options.functional = true\n  }\n\n  // scopedId\n  if (scopeId) {\n    options._scopeId = 'data-v-' + scopeId\n  }\n\n  var hook\n  if (moduleIdentifier) { // server build\n    hook = function (context) {\n      // 2.3 injection\n      context =\n        context || // cached call\n        (this.$vnode && this.$vnode.ssrContext) || // stateful\n        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional\n      // 2.2 with runInNewContext: true\n      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {\n        context = __VUE_SSR_CONTEXT__\n      }\n      // inject component styles\n      if (injectStyles) {\n        injectStyles.call(this, context)\n      }\n      // register component module identifier for async chunk inferrence\n      if (context && context._registeredComponents) {\n        context._registeredComponents.add(moduleIdentifier)\n      }\n    }\n    // used by ssr in case component is cached and beforeCreate\n    // never gets called\n    options._ssrRegister = hook\n  } else if (injectStyles) {\n    hook = shadowMode\n      ? function () {\n        injectStyles.call(\n          this,\n          (options.functional ? this.parent : this).$root.$options.shadowRoot\n        )\n      }\n      : injectStyles\n  }\n\n  if (hook) {\n    if (options.functional) {\n      // for template-only hot-reload because in that case the render fn doesn't\n      // go through the normalizer\n      options._injectStyles = hook\n      // register for functional component in vue file\n      var originalRender = options.render\n      options.render = function renderWithStyleInjection (h, context) {\n        hook.call(context)\n        return originalRender(h, context)\n      }\n    } else {\n      // inject component registration as beforeCreate hook\n      var existing = options.beforeCreate\n      options.beforeCreate = existing\n        ? [].concat(existing, hook)\n        : [hook]\n    }\n  }\n\n  return {\n    exports: scriptExports,\n    options: options\n  }\n}\n\n\n//# sourceURL=webpack:///./node_modules/vue-loader/lib/runtime/componentNormalizer.js?");

/***/ }),

/***/ "./node_modules/webpack/buildin/global.js":
/*!***********************************!*\
  !*** (webpack)/buildin/global.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("var g;\n\n// This works in non-strict mode\ng = (function() {\n\treturn this;\n})();\n\ntry {\n\t// This works if eval is allowed (see CSP)\n\tg = g || new Function(\"return this\")();\n} catch (e) {\n\t// This works if the window reference is available\n\tif (typeof window === \"object\") g = window;\n}\n\n// g can still be undefined, but nothing to do about it...\n// We return undefined, instead of nothing here, so it's\n// easier to handle this case. if(!global) { ...}\n\nmodule.exports = g;\n\n\n//# sourceURL=webpack:///(webpack)/buildin/global.js?");

/***/ }),

/***/ 0:
/*!***********************************************!*\
  !*** multi ./assets/src/front-end/js/main.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

eval("module.exports = __webpack_require__(/*! ./assets/src/front-end/js/main.js */\"./assets/src/front-end/js/main.js\");\n\n\n//# sourceURL=webpack:///multi_./assets/src/front-end/js/main.js?");

/***/ })

/******/ });