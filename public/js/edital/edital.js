EditalView = function() {
    this.init = function () {
        console.log('EditalView.init');
        this.initView();
        this.addEvent();
    };

    this.initView = function () {
        console.log('EditalView.initView');
        $('input').attr('disabled','true');
        if (0 < $('#tipoEdital option:selected').val()) {
            $('input').removeAttr('disabled');
        };
    };

    this.addEvent = function () {
        console.log('EditalView.addEvent');
        $('#tipoEdital').change( function(){
            if($(this).val() == 0){
                $('input').attr('disabled','true');
            }else{
                $('input').removeAttr('disabled');
            }
        });
        $('.marcatodos').click(function (){
            var filhos = $(this).attr('data-filho');
            if($(this).is(':checked')){
                $('.filho_'+filhos).attr('checked','checked');
            }else{
                $('.filho_'+filhos).removeAttr('checked');
            }
        });
    };

    return this;
}().init();