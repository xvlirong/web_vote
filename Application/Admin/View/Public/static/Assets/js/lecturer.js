$(function(){
	var num = $('#good_1').children().length;
	for(var i = 0;i < num;i++){
		var content = `<div data-index="`+i+`" class='nck'></div>`;
		$('#classify').append(content);
	}
})
// 上传头像
$('#upLoadfile').click(function(){
	$('#addImg').click();
})
$('#addImg').change(function(e){
	// console.log($(this)[0].files[0])
	var filemaxsize = 1024 * 0.5;//0.5M
	var target = $(e.target);
	var Size = target[0].files[0].size / 1024;
	if(Size > filemaxsize) {
		alert('图片过大，请重新选择!');
		$(".avatar-wrapper").childre().remove;
		return false;
	}
	if(!this.files[0].type.match(/image.*/)) {
		alert('请选择正确的图片!')
		return false;
	}
	$('.userImg').attr('src',URL.createObjectURL($(this)[0].files[0]))
})
// 上传excel,word时获取文件名
$(document).on('click','.upLoad',function(){
	$(this).parent().find('.file').click();
	var file = $(this).parent().find('.file');
    file.on('change', function( e ){
        //e.currentTarget.files 是一个数组，如果支持多个文件，则需要遍历
        var name = e.currentTarget.files[0].name;
        $(this).parent().find('.file_name').text(name).show();
        $(this).parent().find('.lecturer_btn').css({'marginLeft':'15px','margin-top':'5px'});
    });
})

// 添加课程
$(document).on('click','.addClass',function(){
	var content = `<input class="input mainclass" type="text" name="course_name[]" value="" placeholder="请输入课程名称(必填)">`;
	$(this).parent().find('.menulist').find('dd').append(content);
})

// 隐藏弹窗
$('#mask').click(function(){
	$('#mask,.model').hide();
})
// 显示弹窗
$('.menu').click(function(){
	var id = $(this).attr('data-id');
	$(this).parent().find('.model').show();
	$('#mask').show();
	if($(this).attr('data-id') == 'good'){
		$('.good_3').hide();
		$('.good .good_3:eq(0)').show();
	}
	
})
$(document).on('click','.menu[data-id="customer"]',function(){
	$('.menu[data-id="customer"]').find('.customer').hide();
	$(this).parent().find('.customer').show();
	$('#mask').show();
})
// 选择人数
$(document).on('click','.customer li',function(){
	$(this).parent().parent().prev().find('span').text($(this).text());
	$(this).parent().next().val($(this).text());
})
// 显示擅长领域的二级分类
$('#good_1 li span').hover(function(){
	var index = $(this).parent().index();
	$('.good_3').hide();
	$('.good_3:eq('+index+')').show();
})
// 点击添加选中状态
$(document).on('click','.model>ul>li',function(){
	var _self = $(this);
	if($(this).hasClass('nike')){
		$(this).removeClass('nike');
		$(this).parent().parent().next().find('dd').each(function(index,item){
			if(_self.find('span').text() == $(item).find('span').text()){
				$(item).remove();
			}
		})
	}else{
		$(this).addClass('nike');
		var content = `<dd><span>`+$(this).find('span').text()+`</span><b>×</b></dd>`;
		$(this).parent().parent().next().append(content);
	}
	if($(this).hasClass('num')){
		$(this).parent().find('li').removeClass('nike');
		$(this).addClass('nike');
	}
	if($(this).find('input[type="checkbox"]').attr('checked') == undefined){
		$(this).find('input[type="checkbox"]').attr('checked','checked');
	}else{
		$(this).find('input[type="checkbox"]').removeAttr('checked');
	}
})
$('.good_3 li').click(function(){
	var _self = $(this);
	var second_div = $('#classify').find('div[data-index="'+($(this).parent().index()-1)+'"]');
	if($(this).hasClass('nike')){
		$(this).removeClass('nike');
		if(second_div.children().length > 0){
			second_div.find('div:eq(1)').find('span').each(function(index,item){
				if($(item).text() == _self.find('span').text()){
					$(item).parent().remove();
					// console.log(second_div.find('div:eq(1)').children().length)
					if(second_div.find('div:eq(1)').children().length == 0){
						second_div.empty();
					}
				}
			})
		}else{
			second_div.append(first+second);
		}
	}else{
		$(this).addClass('nike');
		var first = `<div class='chuk'>`+$('#good_1 li:eq('+($(this).parent().index()-1)+') span').text()+`</div>`;
		var second = `<div><div class='lick'><span>`+$(this).find('span').text()+`</span><b>×</b></div></div>`;
		var third = `<div class='lick'><span>`+$(this).find('span').text()+`</span><b>×</b></div>`;
		if(second_div.children().length > 0){
			second_div.find('div:eq(1)').append(third);
		}else{
			second_div.append(first+second);	
		}
	}
	// 给二级分类下的input选中或取消，根据选中个数给一级下的input添加或取消
	if($(this).find('input[type="checkbox"]').attr('checked') == undefined){
		$(this).find('input[type="checkbox"]').attr('checked','checked');
	}else{
		$(this).find('input[type="checkbox"]').removeAttr('checked');
	}
	if($(this).parent().find('.nike').length > 0){
		$('#good_1 li:eq('+($(this).parent().index()-1)+') input').attr('checked','checked');
	}else{
		$('#good_1 li:eq('+($(this).parent().index()-1)+') input').removeAttr('checked');
	}
})
// 删除已选标签
$(document).on('click','.select_list dd b',function(){
	var _self = $(this);
	$(this).parent().parent().prev().find('ul').find('li').each(function(index,item){
		if($(item).find('span').text() == _self.prev().text()){
			$(item).removeClass('nike');
		}
	})
	$(this).parent().remove();
})
$(document).on('click','#classify b',function(){
	var _self = $(this);
	var text = _self.prev().text();
	var index = _self.parent().parent().parent().attr('data-index');
	$('.good_3:eq('+index+') li span').each(function(indexs,item){
		if($(item).text() == text){
			$(item).parent().removeClass('nike');
			$(item).parent().find('input').removeAttr('checked');
		}
		if($(item).parent().parent().find('.nike').length == 0){
			$('#good_1 li:eq('+index+') input').removeAttr('checked');
		}
	})
	if(_self.parent().parent().children().length == 1){
		_self.parent().parent().parent().empty();
	}
	_self.parent().remove();
})