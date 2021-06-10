@extends('phobrv::layout.app')

@section('header')
<a href="{{route('crawler.index')}}"  class="btn btn-default float-left">
	<i class="fa fa-backward"></i> @lang('Back')
</a>
<a href="#" onclick="save()"  class="btn btn-primary float-left">
	<i class="fa fa-floppy-o"></i> @lang('Save & Close')
</a>
<a href="#" onclick="update()"  class="btn btn-warning float-left">
	<i class="fa fa-wrench"></i> @lang('Update')
</a>

@endsection

@section('content')
<div class="box box-primary">
	<div class="box-body">
		<div class="row">
			<form class="form-horizontal" id="formSubmit" method="post" action="{{isset($data['post']) ? route('crawler.update',array('crawler'=>$data['post']->id)) : route('crawler.store')}}"  enctype="multipart/form-data">
				@csrf
				@isset($data['post']) @method('put') @endisset
				<input type="hidden" id="typeSubmit" name="typeSubmit" value="">
				<button id="btnSubmit" style="display: none" type="submit" ></button>
				<div class="col-md-8">
					@include('phobrv::input.inputText',['label'=>'Slug','key'=>'slug','check_auto_gen'=>'true'])
					@include('phobrv::input.inputText',['label'=>'Title','key'=>'title','required'=>true])

					@include('phobrv::input.inputTextarea',['label'=>'Nội dung','key'=>'content','style'=>'short'])

				</div>
				<div class="col-md-4">
					@include('phobrv::input.inputImage',['key'=>'thumb','basic'=>true])
					@include('phobrv::input.inputText',['label'=>'Url','key'=>'url'])
					@include('phobrv::input.inputText',['label'=>'Description','key'=>'excerpt'])
					@include('phobrv::input.inputText',['label'=>'Create date','key'=>'created_at','datetime'=>true,'value'=>date('Y-m-d H:i:s',strtotime($data['post']->created_at))])
					@include('phobrv::input.label',['label'=>'Seo Meta'])
					@include('phobrv::input.inputText',['label'=>'Meta Title','key'=>'meta_title','type'=>'meta'])
					@include('phobrv::input.inputText',['label'=>'Meta Description','key'=>'meta_description','type'=>'meta'])
					@include('phobrv::input.inputText',['label'=>'Meta Keywords','key'=>'meta_keywords','type'=>'meta'])

				</div>
			</form>
		</div>
	</div>
</div>
@endsection

@section('styles')

@endsection

@section('scripts')
<script type="text/javascript">
	window.onload = function() {
		if($('textarea[name="content"]').length > 0)
			CKEDITOR.replace('content', options);
	};
</script>
@endsection