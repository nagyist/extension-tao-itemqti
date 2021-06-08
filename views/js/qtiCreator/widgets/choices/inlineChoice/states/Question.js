define([
    'jquery',
    'lodash',
    'ckeditor',
    'taoQtiItem/qtiCreator/widgets/states/factory',
    'taoQtiItem/qtiCreator/widgets/choices/states/Question',
    'taoQtiItem/qtiCreator/widgets/choices/helpers/formElement',
    'taoQtiItem/qtiCreator/editor/ckEditor/htmlEditor',
    'taoQtiItem/qtiCreator/editor/gridEditor/content'
], function ($, _, CKEditor, stateFactory, QuestionState, formElement, htmlEditor, contentHelper) {
    'use strict';

    const ChoiceStateQuestion = stateFactory.extend(QuestionState, function initStateQuestion() {
        this.buildEditor();
    }, function exitStateQuestion() {
        this.destroyEditor();
    });

    ChoiceStateQuestion.prototype.createToolbar = function () {

        const _widget = this.widget,
            $toolbar = _widget.$container.find('td:last');

        //set toolbar button behaviour:
        formElement.initShufflePinToggle(_widget);
        formElement.initDelete(_widget);

        return $toolbar;
    };

    ChoiceStateQuestion.prototype.buildEditor = function () {

        const _widget = this.widget,
            container = _widget.element.getBody(),
            $editable = _widget.$container.find('.editable-content'),
            $editableContainer = _widget.$container.find('.editable-container');

        $editableContainer.attr('data-html-editable-container', true);
        $editable.attr('data-html-editable', true);

        if (!htmlEditor.hasEditor($editableContainer)) {
            htmlEditor.buildEditor($editableContainer, {
                change : contentHelper.getChangeCallback(container),
                data: {
                    container,
                    widget: _widget
                },
                toolbar: [ {
                    name: 'basicstyles',
                    items: [ 'Bold', 'Italic', 'Subscript', 'Superscript' ]
                }, {
                    name: 'insert',
                    items: [ 'SpecialChar' ]
                } ],
                qtiMedia: false,
                qtiImage: false,
                qtiInclude: false,
                enterMode: CKEditor.ENTER_BR
            });
        }

        $editable
            .on('focus.qti-widget', function () {
                _widget.changeState('choice');
            })
            .on('keypress.qti-widget', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $(this).blur();
                    _widget.changeState('question');
                }
        });
    };

    ChoiceStateQuestion.prototype.destroyEditor = function () {

        this.widget.$container.find('.editable-content')
            .removeAttr('contentEditable')
            .removeAttr('data-html-editable')
            .off('keyup.qti-widget');

        this.widget.$container.find('.editable-container')
            .removeAttr('data-html-editable-container');
    };

    return ChoiceStateQuestion;
});
