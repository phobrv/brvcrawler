@extends('phobrv::layout.app')
@section('header')
<ul>
	<li>
		<a href="#"  class="btn btn-default float-left">
			Crawler test
		</a>
	</li>
</ul>
@endsection
@section('content')
<div class="row">
	<div class="col-md-6">

		<form  class="form-horizontal" id="formSubmit" method="post" action="{{isset($data['crawler_profile']) ? route('crawlerProfile.update',['crawlerProfile'=>$data['crawler_profile']->id]) : route('crawlerProfile.store')}}">
			@isset($data['crawler_profile']) @method('put') @endif
			<div class="box box-primary">
				<div class="box-header font16">
					Create/Edit quick
				</div>
				<div class="box-body">
					@csrf
					@include('phobrv::input.inputText',['label'=>'Source','key'=>'url', 'required'=> true , 'value'=> $data['crawler_profile']->url ?? '' ])
					@include('phobrv::input.inputSelect',['label'=>'Type','key'=>'type', 'array'=> $arrayCrawlerType , 'value'=> $data['crawler_profile']->type ?? '0' ])
					@include('phobrv::input.inputSelect',['label'=>'Check before Add','key'=>'is_check', 'array'=> ['1'=>'Yes','0'=>'No'] , 'value'=> $data['crawler_profile']->is_check ?? '1' ])
					@include('phobrv::input.inputSelect',['label'=>'Spread','key'=>'is_spread', 'array'=> ['1'=>'Yes','0'=>'No'] , 'value'=> $data['crawler_profile']->is_spread ?? '0' ])
					<hr>
					@include('phobrv::input.inputText',['label'=>'Title Tag','key'=>'title_tag',  'value'=> $data['crawler_profile']->title_tag ?? '' ])
					@include('phobrv::input.inputText',['label'=>'Content Tag','key'=>'content_tag',  'value'=> $data['crawler_profile']->content_tag ?? '' ])

					@include('phobrv::input.inputText',['label'=>'Thumb Tag','key'=>'thumb_tag',  'value'=> $data['crawler_profile']->thumb_tag ?? '' ])
					@include('phobrv::input.inputText',['label'=>'Meta title Tag','key'=>'meta_title_tag',  'value'=> $data['crawler_profile']->meta_title_tag ?? '' ])
					@include('phobrv::input.inputText',['label'=>'Meta desc Tag','key'=>'meta_description_tag',  'value'=> $data['crawler_profile']->meta_description_tag ?? '' ])
				</div>
				<div class="box-footer">
					<button class="btn btn-primary pull-right">{{$data['submit_label']}}</button>
				</div>
			</div>
		</form>

	</div>
	<div class="col-md-6">
		<div class="box box-primary">
			<div class="box-body">

				<table id="" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>{{__('Domain')}}</th>
							<th>{{__('Type')}}</th>
							<th class="text-center">{{__('Action')}}</th>
						</tr>
					</thead>
					<tbody>
						@isset($data['crawler_profiles'])
						@foreach($data['crawler_profiles'] as  $r)
						<tr>
							<td>{{$loop->index + 1}}</td>
							<td>{{$r->url}}</td>
							<td align="center">
								@isset($arrayCrawlerType[$r->type]) {{ $arrayCrawlerType[$r->type] }} @endif
							</td>
							<td align="center">
								<a href="{{route('crawlerProfile.edit',array('crawlerProfile'=>$r->id))}}"><i class="fa fa-edit" title="Sửa"></i></a>
								&nbsp;&nbsp;&nbsp;
								<a style="color: red" href="#" onclick="destroy('destroy{{$r->id}}')"><i class="fa fa-times" title="Sửa"></i></a>
								<form id="destroy{{$r->id}}" action="{{ route('crawlerProfile.destroy',array('crawlerProfile'=>$r->id)) }}" method="post" style="display: none;">
									@method('delete')
									@csrf
								</form>

							</td>
						</tr>
						@endforeach
						@endif
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>


@endsection

@section('styles')

@endsection

@section('scripts')
<script type="text/javascript">
	function destroy(form){
		var anwser =  confirm("Bạn muốn menu item này?");
		if(anwser){
			event.preventDefault();
			document.getElementById(form).submit();
		}
	}
</script>
@endsection