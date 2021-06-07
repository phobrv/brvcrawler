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
	<div class="box-header font16">
		Chọn nguồn link crawl
	</div>
	<div class="box-body">
		<div  class="form-horizontal" >
			<div class="form-group">
				<label for="inputEmail3" class="col-sm-2 control-label">{{__('Source')}}</label>
				<div class="col-sm-8">
					<select  class="form-control" name="source" id="source">
						<option value="0">-</option>
						@foreach($data['profiles'] as $p)
						<option value="{{ $p->id }}">{{ $p->url }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-sm-2">
					<a type="submit" id="btnCrawl" class=" btn btn-primary" onclick="crawlHandWork()"> <i class=""></i> Crawl</a>
				</div>
			</div>
		</div>
		<table id="" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>{{__('Date Crawl')}}</th>
					<th>{{__('Link')}}</th>
					<th>{{__('Title')}}</th>
					<th>{{__('Status')}}</th>
					<th class="text-center">{{__('Action')}}</th>
				</tr>
			</thead>
			<tbody id="crawlResult">
			</tbody>
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
	function crawlHandWork(){
		var source = $('#source').val();
		if(source == 0)
		{
			$('#source').addClass('red');
		}
		else
		{
			$('#source').removeClass('red');
			$('#btnCrawl i').addClass('fa fa-spinner fa-spin');
			$('#source').prop('disabled', 'disabled');
			$.ajax({
				headers : { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
				url: '{{URL::route("crawler.apiCrawlHandwork")}}',
				type: 'POST',
				data: {source: source},
				success: function(output){
					$('#crawlResult').html(output);
					$('#btnCrawl i').removeClass('fa fa-spinner fa-spin');
				}
			});
		}


	}
</script>
@endsection