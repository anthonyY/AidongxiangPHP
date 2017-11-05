
//好友动态-打赏
function getRewardList(page) {
	  if (rewardList_url) {
	    $.post(rewardList_url, {page:page},function (data) {
//	    	console.log(data);return;
	    	rewardTemplate(data, 'reward_list');
	    	rewardTemplate2(data,'reward_page');
	    },"json");
	  }
	}
////跳转详情用
//function ordserDetails(id) {
//  location.href =  order_details_url + '?order_id=' + id;
//}
function rewardTemplate(data, container) {
  var content = '';
  list = data.list
  for(i in list) {
    item = list[i];
    template = '<li>';
	  if(item.image){
		  template += '<img class="head_img" src="' + url_base + '/uploadfiles/' +item.image+'">';
	  }else{
		  template += '<img class="head_img" src="' + url_base + '/images/portrait.png">';
	  }
	  template += '<div class="right_txt"><p><span class="name" style="float: left;">'+item.nickname+'</span>';
	  template += '<span style="float: left; margin-left: 10px">'+item.rank+'</span><span style="float: left; margin-left: 10px">'+item.typeName+'</span>';
	  template += '</p><p>打赏了<span style="color: #EC1B1B;margin: 0 5px">'+item.price+'</span>元</p>';
	  template += '<p>'+item.timestamp+'</p></div></li>';
    content += template;
  }
  if (content) {
    $("#" + container).append(content);
  }
}
function rewardTemplate2(data,container) {
	  var content2 = '';
	  item = data.page_info
	  if(item.pagesInRange){
		    if(item.previous){
		        template2 = '<ul><li class="disabled"><a>首页</a></li><li class="disabled"><a>上一页</a></li>';
		    }else{
//		    	item.arr.insert(2, '1');
		    	template2 = '<ul><li><a href="javascript:rewardList(1);">首页</a></li><li><a href="javascript:rewardList('+item.page_1+');">上一页</a>';
		    }
			    $.each(item.pagesInRange, function(i,val){
			    	if(val==item.page){
			    		 template2 += '<li class="active"><a>'+val+'</a></li>';
			    	}else{
			    		 template2 += '<li><a href="javascript:rewardList('+val+');">'+val+'</a></li>';
			    	}
			    });
		    if(item.net){
		        template2 += '<ul><li class="disabled"><a>下一页</a></li><li class="disabled"><a>尾页</a></li>';
		    }else{
		    	template2 += '<ul><li><a href="javascript:rewardList('+item.page_2+');">下一页</a></li><li><a href="javascript:rewardList('+item.total+');">尾页</a></li>';
		    }
		    content2 += template2;
	  }
	  if (content2) {
	    $("#" + container).append(content2);
	  }
}
// 1打赏；
function rewardList(page){
	 $('#reward_list').html('');//清空
	 $('#reward_page').html('');//清空
	  getRewardList(page);
}
// 1评论；
function reviewList(page) {
	 $('#review_list').html('');//清空
	 $('#review_page').html('');//清空
	getReviewList(page);
}

  $(document).ready(function(){
	  // 1打赏；
	  if (loadmore_type == 1) {
		  getRewardList(1);
	  }
	  // 1评论；
	  if (loadmore_type2 == 2) {
		  getReviewList(1);
	  }
	  if(loadmore_type == 3){
		  getQAList(1);
	  }
	});
//旁听用户列表
  function getQAList(page) {
  	  if (qaList_url) {
  	    $.post(qaList_url, {page:page},function (data) {
//  	    	console.log(data);return;
  	    	qaTemplate(data, 'qa_list');
  	    	qaTemplate2(data,'qa_page');
  	    },"json");
  	  }
  	}
  //旁听用户列表
  function qaTemplate(data, container) {
    var content = '';
    list = data.list
    for(i in list) {
      item = list[i];
      template = '<li>';
  	  if(item.image){
  		  template += '<img class="head_img" src="' + url_base + '/uploadfiles/' +item.image+'">';
  	  }else{
  		  template += '<img class="head_img" src="' + url_base + '/images/portrait.png">';
  	  }
  	  template += '<div class="right_txt"><p><span class="name" style="float: left;">'+item.nickname+'</span>';
  	  template += '<span style="float: left; margin-left: 10px">'+item.rank+'</span><span style="float: left; margin-left: 10px">'+item.typeName+'</span>';
  	  template += '</p>';//<p>打赏了<span style="color: #EC1B1B;margin: 0 5px">'+item.price+'</span>元</p>
  	  template += '<p>'+item.timestamp+'</p></div></li>';
      content += template;
    }
    if (content) {
      $("#" + container).append(content);
    }
  }
  function qaTemplate2(data,container) {
  	  var content3 = '';
  	  item = data.page_info
  	  if(item.pagesInRange){
  		    if(item.previous){
  		        template3 = '<ul><li class="disabled"><a>首页</a></li><li class="disabled"><a>上一页</a></li>';
  		    }else{
//  		    	item.arr.insert(2, '1');
  		    	template3 = '<ul><li><a href="javascript:qaList(1);">首页</a></li><li><a href="javascript:qaList('+item.page_1+');">上一页</a>';
  		    }
  			    $.each(item.pagesInRange, function(i,val){
  			    	if(val==item.page){
  			    		 template3 += '<li class="active"><a>'+val+'</a></li>';
  			    	}else{
  			    		 template3 += '<li><a href="javascript:qaList('+val+');">'+val+'</a></li>';
  			    	}
  			    });
  		    if(item.net){
  		        template3 += '<ul><li class="disabled"><a>下一页</a></li><li class="disabled"><a>尾页</a></li>';
  		    }else{
  		    	template3 += '<ul><li><a href="javascript:qaList('+item.page_2+');">下一页</a></li><li><a href="javascript:qaList('+item.total+');">尾页</a></li>';
  		    }
  		    content3 += template3;
  	  }
//  	console.log(item,container,content2);
  	  if (content3) {
  	    $("#" + container).append(content3);
  	  }
  }
  // 1打赏；
  function qaList(page){
  	 $('#qa_list').html('');//清空
  	 $('#qa_page').html('');//清空
  	  getQAList(page);
  }
  
//好友动态-评论
  function getReviewList(page) {
  	  if (reviewList_url) {
  	    $.post(reviewList_url, {page:page},function (data) {
//  	    	console.log(data);return;
  	     	reviewTemplate2(data,'review_page');
  	    	reviewTemplate(data, 'review_list');
  	    
  	    },"json");
  	  }
  	}
  
  function reviewTemplate2(data,container) {
	  var content2 = '';
	  item = data.page_info
	  if(item.pagesInRange){
		    if(item.previous){
		        template2 = '<ul><li class="disabled"><a>首页</a></li><li class="disabled"><a>上一页</a></li>';
		    }else{
//		    	item.arr.insert(2, '1');
		    	template2 = '<ul><li><a href="javascript:reviewList(1);">首页</a></li><li><a href="javascript:reviewList('+item.page_1+');">上一页</a>';
		    }
			    $.each(item.pagesInRange, function(i,val){
			    	if(val==item.page){
			    		 template2 += '<li class="active"><a>'+val+'</a></li>';
			    	}else{
			    		 template2 += '<li><a href="javascript:reviewList('+val+');">'+val+'</a></li>';
			    	}
			    });
		
		    if(item.net){
		        template2 += '<ul><li class="disabled"><a>下一页</a></li><li class="disabled"><a>尾页</a></li>';
		    }else{
		    	template2 += '<ul><li><a href="javascript:reviewList('+item.page_2+');">下一页</a></li><li><a href="javascript:reviewList('+item.total+');">尾页</a></li>';
		    }
		    content2 += template2;
	  }
//  template2 += '<ul><li class="disabled"><a>下一页</a></li><li class="disabled"><a>尾页</a></li>';
//  console.log(content2);
	  if (content2) {
	    $("#" + container).append(content2);
	  }
  }
  
  
function reviewTemplate(data, container) {
	var content = '';
	list = data.list
	for(i in list) {
	  item = list[i];
	  template = '<li class="wrap">';
	  if(item.image){
	      template += '<img class="head_img" src="' + url_base + '/uploadfiles/' +item.image+'">';
	  }else{
		  template += '<img class="head_img" src="' + url_base + '/images/portrait.png">';
	  }
	  template += '<div class="right_txt flex1"><p style="overflow: hidden;"><span class="name" style=" margin-right: 15px">' +item.nickname+'</span>';
	  template += '<span style=" margin-right: 15px">'+item.rank+'</span><span>'+item.typeName+'</span>';
	  template += '<span class="time">'+item.timestamp+'</span></p><p style="margin-top: 10px;">'+item.content+'</p></div></li>';
	  content += template;
	}
	if (content) {
	  $("#" + container).append(content);
	}
}
////加载更多   
//var loadmore_type = 0; // 1活动；2商城；3活动+商城；4收藏活动；8收藏商品；12收藏活动+收藏商品；16积分商城；32消息； 64我的订单详情; 128 店铺 ; 
//function loadmore(obj){
//  var scrollTop = $(obj).scrollTop();
//  var scrollHeight = $(document).height();
//  var windowHeight = window.innerHeight;
//  if (scrollHeight - scrollTop - windowHeight<=50 ) {
//    if (loadmore_type == 1) {
//      getActive(active_page+1);
//    }
//    if (loadmore_type == 2) {
//      getShop(shop_page+1)
//    }
//    if (loadmore_type == 4) {
//      getCollectionActive(active_page+1);
//    }
//    if (loadmore_type == 8) {
//      getCollectionShop(shop_page+1);
//    }
//    if (loadmore_type == 16) {
//      getShopIntegral(shop_page+1);
//    }
//    if (loadmore_type == 32) {
//        getNews(news_page+1);
//    }
//    if (loadmore_type == 64) {
//        getMyOrder(order_page+1);
//    }
//    if (loadmore_type == 128) {
//    	getShopGoods(shops_goods_page+1);
//    }
//  }
//}
//
////页面滚动执行事件
//$(window).scroll(function (){
//  if (loadmore) {
//    loadmore($(this));
//  }
//});