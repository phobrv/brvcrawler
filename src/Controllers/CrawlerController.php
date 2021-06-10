<?php

namespace Phobrv\BrvCrawler\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Phobrv\BrvCore\Services\UnitServices;
use Phobrv\BrvCrawler\Repositories\CrawlerDataRepository;
use Phobrv\BrvCrawler\Repositories\CrawlerProfileRepository;
use Phobrv\BrvCrawler\Services\CrawlServices;
use Yajra\Datatables\Datatables;

class CrawlerController extends Controller {
	protected $crawlerProfileRepository;
	protected $crawlerDataRepository;
	protected $unitService;
	protected $crawlService;
	protected $crawlDataStatus;
	public function __construct(
		CrawlerProfileRepository $crawlerProfileRepository,
		CrawlerDataRepository $crawlerDataRepository,
		CrawlServices $crawlService,
		UnitServices $unitService
	) {
		$this->crawlerDataRepository = $crawlerDataRepository;
		$this->crawlerProfileRepository = $crawlerProfileRepository;
		$this->unitService = $unitService;
		$this->crawlService = $crawlService;
		$this->crawlDataStatus = config('brvcrawler.crawlerStatusLabel');
	}

	public function index() {
		try {
			$data['breadcrumbs'] = $this->unitService->generateBreadcrumbs(
				[
					['text' => 'Crawler Hardword', 'href' => ''],
				]
			);

			$data['profiles'] = $this->crawlerProfileRepository->all();
			return view('phobrv::crawler.index')->with('data', $data);

		} catch (Exception $e) {

		}
	}

	public function getData() {
		$data['crawlerData'] = $this->crawlerDataRepository->all();
		return Datatables::of($data['crawlerData'])
			->addColumn('status', function ($post) {
				return view('phobrv::crawler.components.status', ['status' => $post->status]);
			})
			->addColumn('create', function ($post) {
				return date('d/m/Y', strtotime($post->created_at));
			})
			->addColumn('action', function ($post) {
				return view('phobrv::crawler.components.action', ['post' => $post]);
			})->make(true);
	}

	public function create() {
		//
	}

	public function store(Request $request) {
		//
	}

	public function show($id) {

	}

	public function edit($id) {
		$data['breadcrumbs'] = $this->unitService->generateBreadcrumbs(
			[
				['text' => 'Crawler', 'href' => ''],
				['text' => 'Edit Post', 'href' => ''],
			]
		);
		try {
			$data['post'] = $this->crawlerDataRepository->find($id);
			return view('phobrv::crawler.edit')->with('data', $data);
		} catch (Exception $e) {
			return back()->with('alert_danger', $e->getMessage());
		}
	}

	public function update(Request $request, $id) {
		$data = $request->all();
		$post = $this->crawlerDataRepository->update($data, $id);
		$msg = __('Update success!');
		if ($request->typeSubmit == 'save') {
			return redirect()->route('crawler.index')->with('alert_success', $msg);
		} else {
			return redirect()->route('crawler.edit', ['crawler' => $id])->with('alert_success', $msg);
		}
	}

	public function destroy($id) {
		$this->crawlerDataRepository->delete($id);
		return true;
	}

	//API
	public function crawl(Request $request) {
		$data = $request->all();
		if (empty($data['source'])) {
			return response()->json(['code' => '1', 'msg' => 'Invalid request']);
		}
		$profile = $this->crawlerProfileRepository->find($data['source']);
		$out = [];
		switch ($profile->type) {
		case 'post':
			return $this->crawlService->crawlPost(trim($profile->url), $profile);
			break;

		case 'category':
			if ($profile->is_spread) {

				$multi = $this->crawlMultiPostSpread($profile);

			} else {
				$multi = $this->crawlMultiPost($profile);
			}
			break;
		}
		return response()->json(['code' => '0', 'msg' => 'Success']);
	}

}
