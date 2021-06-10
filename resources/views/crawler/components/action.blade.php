<a href="{{ route('crawler.edit',['crawler'=>$post->id]) }}"><i class="fa fa-edit" title="Sửa"></i></a>
&nbsp;&nbsp;&nbsp;&nbsp;
<a style="color: red" href="#" onclick="destroy('{{route('crawler.destroy',['crawler'=>$post->id])}}')">
	<i class="fa fa-trash" title="Delete"></i>
</a>
