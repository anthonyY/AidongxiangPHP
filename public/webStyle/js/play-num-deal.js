var played_audio_ids = new Array();//已播放数组

function play_num_deal(audio_id){//播放量处理方法
	if($.inArray(audio_id,played_audio_ids) == -1){//不在已播放数组内
		played_audio_ids.push(audio_id);
		$.post(study_num_url,{audio_id:audio_id},
			function(data){
			console.log(data);
		},"json");
	}
}
