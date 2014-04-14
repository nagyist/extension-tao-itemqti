define([
    'taoQtiItem/qtiCreator/widgets/static/Widget',
    'taoQtiItem/qtiCreator/widgets/static/modalFeedback/states/states',
    'tpl!taoQtiItem/qtiCreator/tpl/toolbars/htmlEditorTrigger',
    'taoQtiItem/qtiCreator/helper/htmlEditor'
], function(Widget, states, toolbarTpl, htmlEditor){

    var ModalFeedbackWidget = Widget.clone();

    ModalFeedbackWidget.initCreator = function(){
        
        Widget.initCreator.call(this);

        this.registerStates(states);

        this.buildEditor();
    };

    ModalFeedbackWidget.buildContainer = function(){

        this.$container = this.$original.addClass('widget-box');

        this.$container.find('.modal-title').attr('contenteditable', true);
    };

    ModalFeedbackWidget.createToolbar = function(){

        this.$container.find('.modal-body').append(toolbarTpl({
            serial : this.serial,
            state : 'active'
        }));

        return this;
    };

    ModalFeedbackWidget.buildEditor = function(){

        var _this = this,
            $editableContainer = _this.$container.find('.modal-body'),
            element = _this.element;

        $editableContainer.attr('data-html-editable-container', true);

        if(!htmlEditor.hasEditor($editableContainer)){
            htmlEditor.buildEditor($editableContainer, {
                change : function(data){
                    element.body(data);
                },
                focus : function(){
                    _this.changeState('active');
                }
            });
        }

        this.$original.find('.modal-title').on('keyup', function(){
            element.attr('title', $(this).text());//save text only, since tittle has a baseType "string"
        });
    };

    return ModalFeedbackWidget;
});