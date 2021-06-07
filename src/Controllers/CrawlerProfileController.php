<?php
namespace Phobrv\BrvCrawler\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Phobrv\BrvCore\Services\UnitServices;
use Phobrv\BrvCrawler\Repositories\CrawlerProfileRepository;
use Phobrv\BrvCrawler\Services\CrawlServices;

class CrawlerProfileController extends Controller {

	protected $optionRepository;
	protected $unitService;
	protected $crawlService;

	public function __construct(
		CrawlerProfileRepository $crawlerProfileRepository,
		CrawlServices $crawlService,
		UnitServices $unitService
	) {
		$this->crawlerProfileRepository = $crawlerProfileRepository;
		$this->unitService = $unitService;
		$this->crawlService = $crawlService;
	}

	public function index() {
		try {
			$data['breadcrumbs'] = $this->unitService->generateBreadcrumbs(
				[
					['text' => 'Config Crawler', 'href' => ''],
				]
			);
			$data['crawler_profiles'] = $this->crawlerProfileRepository->all();
			$data['submit_label'] = "Create";
			return view('phobrv::crawler.profile')
				->with('data', $data);
		} catch (Exception $e) {
			return back()->with('alert_danger', $e->getMessage());
		}
	}

	public function store(Request $request) {
		$request->validate([
			'url' => 'required|unique:crawler_profile',
		]);
		try {
			$data = $request->all();
			$data['url'] = trim($data['url']);
			$data['domain'] = $this->crawlService->getDomainFromUrl($data['url']);
			$profile = $this->crawlerProfileRepository->create($data);
			$msg = "Create success";
			return redirect()->route('crawlerProfile.index')->with('alert_success', $msg);
		} catch (Exception $e) {

		}
	}

	public function edit($id) {
		try {
			$data['breadcrumbs'] = $this->unitService->generateBreadcrumbs(
				[
					['text' => 'Config Crawler', 'href' => ''],
				]
			);
			$data['crawler_profiles'] = $this->crawlerProfileRepository->all();
			$data['crawler_profile'] = $this->crawlerProfileRepository->findByField('id', $id)->first();
			$data['submit_label'] = "Update";
			return view('phobrv::crawler.profile')
				->with('data', $data);
		} catch (Exception $e) {
			return back()->with('alert_danger', $e->getMessage());
		}
	}

	public function update(Request $request, $id) {
		$request->validate([
			'url' => 'required|unique:crawler_profile,url,' . $id,
		]);
		try {
			$data = $request->all();
			$data['url'] = trim($data['url']);
			$data['domain'] = $this->crawlService->getDomainFromUrl($data['url']);
			$profile = $this->crawlerProfileRepository->update($data, $id);
			$msg = "Create success";
			return redirect()->route('crawlerProfile.index')->with('alert_success', $msg);
		} catch (Exception $e) {

		}
	}

	public function destroy($id) {
		$this->crawlerProfileRepository->delete($id);
		$msg = __("Delete  success!");
		return redirect()->route('crawlerProfile.index')->with('alert_success', $msg);
	}
}
