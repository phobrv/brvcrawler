@extends('phobrv::layout.app')

@section('header')
<ul>
	<li>
		<a href="{{ route('crawlerProfile.index') }}"  class="btn btn-default float-left">
			<i class="fa fa-backward"></i> @lang('Back')
		</a>
	</li>
</ul>
@endsection

@section('content')
<div class="box box-primary">
	<div class="box-body" >
		<form  id="crawlerForm" class="form-horizontal"  method="post" action="{{ route("crawler.crawl") }}">
			@csrf
			<div class="form-group">
				<label for="inputEmail3" class="col-sm-2 control-label">{{__('Source')}}</label>
				<div class="col-sm-8">
					<select  class="form-control" name="source" id="source" required>
						<option value="0">-</option>
						@foreach($data['profiles'] as $p)
						<option value="{{ $p->id }}">{{ $p->url }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-sm-2">
					<button type="submit" class=" btn btn-primary" id="btnCrawl"> <i class=""></i> Crawl</button>
				</div>
			</div>
		</form>
		<table id="crawlerData" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>{{__('Date Crawl')}}</th>
					<th>{{__('Link')}}</th>
					<th>{{__('Title')}}</th>
					<th>{{__('Status')}}</th>
					<th class="text-center">{{__('Action')}}</th>
				</tr>
			</thead>

		</table>
	</div>

</div>

@endsection

@section('styles')
<style type="text/css">
.red{
	border-color: red;
}
</style>
@endsection

@section('scripts')
<script type="text/javascript">
	var crawlerData =  $('#crawlerData').DataTable({
		lengthMenu: [[15,35,50, -1], [15,35,50, "All"]],
		"order": [[ 0, "desc" ]],
		processing: true,
		serverSide: true,
		ajax: "{{ route('crawler.getData') }}",
		columns:
		[
		{ data: 'create', name: 'create',className:'text-center' },
		{ data: 'url', name: 'url' },
		{ data: 'title', name: 'title' },
		{ data: 'status', name: 'status', orderable: false, searchable: false,className:'text-center'},
		{ data: 'action', name: 'action',orderable: false, searchable: false,className:'text-center'},
		]
	})

	var crawlerForm = {
		data: function(){
			return {
				source: $('#source').val(),
			}
		},
		alertFeedback: function(msg){
			alertOutput('danger',msg)
		},
		submit: function(){
			let Root = this
			$.ajax({
				headers : { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
				type: "POST",
				cache: false,
				url: '{{URL::route("crawler.crawl")}}',
				data: Root.data(),
				dataType: 'json',
				success: function (res) {
					console.log(res)
					$('#btnCrawl i').removeClass('fa fa-spinner fa-spin')
					$('#source').removeAttr('disabled');
					if (res.code == 0) {
						alertOutput('success',res.msg)
						crawlerData.draw()
					}
					else{
						Root.alertFeedback(res.msg)
					}

				}
			});
		},
		init: function(){
			const Root = this
			$('#crawlerForm').on('submit', function(e){
				e.preventDefault()
				$('#btnCrawl i').addClass('fa fa-spinner fa-spin');
				$('#source').prop('disabled', 'disabled');
				Root.submit()
			})
		}
	}

	crawlerForm.init()

	function destroy(url){
		var anwser = confirm("Bạn muốn xoá bài viết này?")
		if(anwser){
			$.ajax({
				headers : { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
				url:url,
				method:"delete",
				success:function(output){
					crawlerData.draw()
				}
			});
		}
	}

</script>
@endsection