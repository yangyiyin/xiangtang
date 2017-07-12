/**
 * Created by yyy on 17/7/11.
 */
var gain_labels = function (option) {

    this.div = (option && option.class) ? $('.' + option.class) : $('.labels');
    this.input_html = '<input class="label_input" type="text" style="width: 160px;height: 30px;line-height: 30px;margin: 10px 10px 10px 0px;float:left;"/>' +
        '<div class="del" style="width: 30px;height:30px;float:left;border-radius: 15px;margin: 10px 10px;' +
        'cursor:pointer;background-color: red;color: #fff;font-size: 30px;line-height: 30px;text-align: center">-</div>' +
        '<div style="clear: both"></div>';
    this.add_html = '<div class="add" style="padding: 10px;border-radius:5px;width:30px;' +
        'cursor:pointer;text-align:center;background-color: #00a2d4;color: #fff">添加</div>';
    this.lables = [];

    if (option && option.hidden_input) {
        this.div.append('<input class="hidden_input" name="labels" type="hidden" value=""/>');
    }

    this.add_input = function () {
        this.div.append( this.input_html);
        $('.add').remove();
        this.div.append(this.add_html);
    }

    this.del_input = function (ele) {
        ele.prev('input').remove();
        ele.remove();
    }
    var _that = this;
    $(document).on('click','.add',function () {
        _that.add_input();
        _that.get_labels();
    });
    $(document).on('click','.del', function (a,b) {
        _that.del_input($(this));
        _that.get_labels();
    });

    $(document).on('change','.label_input', function (a,b) {
        _that.get_labels();
    });

    this.get_labels = function () {
        this.lables = [];
        var _that = this;
        $('.label_input').each(function (a,b) {
            _that.lables.push($(b).val());
        });

        if (option.hidden_input) {
            $('.hidden_input').val( this.lables.join(','));
        }
    }

    if (option && option.current_labels) {
        var labels = option.current_labels.split(',');
        for(var i in labels) {
            this.add_input();
        }

        for(var i in labels) {
            $('.label_input').eq(i).val(labels[i]);
        }
        this.get_labels();
    } else {
        this.add_input();
    }
}

