<?php
namespace Phobrv\BrvCrawler\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Phobrv\BrvCore\Services\UnitServices;
use Phobrv\BrvCrawler\Repositories\CrawlerProfileRepository;
use Phobrv\BrvCrawler\Services\CommonServices;
use Phobrv\BrvCrawler\Services\CrawlServices;
use Exception;

class CrawlerProfileController extends Controller {

	protected $optionRepository;
	protected $unitService;
	protected $crawlService;
	protected $commonServices;
	protected $data;
	public function __construct(
		CrawlerProfileRepository $crawlerProfileRepository,
		CrawlServices $crawlService,
		CommonServices $commonServices,
		UnitServices $unitService
	) {
		$this->crawlerProfileRepository = $crawlerProfileRepository;
		$this->unitService = $unitService;
		$this->commonServices = $commonServices;
		$this->crawlService = $crawlService;
		$this->data = [
			'crawler_profiles' => $crawlerProfileRepository->all(),
			'arrayProfile' => $crawlerProfileRepository->getProfileArray(),
		];
	}

	public function index() {
		try {
			$data = $this->data;
			$data['submit_label'] = "Create";
			$data['breadcrumbs'] = $this->unitService->generateBreadcrumbs(
				[
					['text' => 'Config Crawler', 'href' => ''],
				]
			);
			return view('phobrv::crawler.profile')
				->with('data', $data);
		} catch (Exception $e) {
			return back()->with('alert_danger', $e->getMessage());
		}
	}

	public function store(Request $request) {
		$request->validate([
			'url' => 'required|unique:brv_crawler_profile',
		]);
		try {
			$data = $request->all();
			$data = $this->handleProfileData($data);
			$profile = $this->crawlerProfileRepository->create($data);
			$msg = "Create success";
			return redirect()->route('crawlerProfile.index')->with('alert_success', $msg);
		} catch (Exception $e) {
			return back()->with('alert_danger', $e->getMessage());
		}
	}

	public function edit($id) {
		try {
			$data = $this->data;
			$data['breadcrumbs'] = $this->unitService->generateBreadcrumbs(
				[
					['text' => 'Config Crawler', 'href' => ''],
				]
			);
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
			'url' => 'required|unique:brv_crawler_profile,url,' . $id,
		]);
		try {
			$data = $request->all();
			$data = $this->handleProfileData($data);
			$profile = $this->crawlerProfileRepository->update($data, $id);
			$msg = "Create success";
			return redirect()->route('crawlerProfile.index')->with('alert_success', $msg);
		} catch (Exception $e) {
			return back()->with('alert_danger', $e->getMessage());
		}
	}

	public function destroy($id) {
		$this->crawlerProfileRepository->delete($id);
		$msg = __("Delete  success!");
		return redirect()->route('crawlerProfile.index')->with('alert_success', $msg);
	}

	public function handleProfileData($data) {
		$data['url'] = trim($data['url']);
		$data['domain'] = $this->commonServices->getDomainFromUrl($data['url']);
		if (!empty($data['profile_id'])) {
			$profile = $this->crawlerProfileRepository->find($data['profile_id']);
			$data['title_tag'] = $profile->title_tag;
			$data['content_tag'] = $profile->content_tag;
			$data['thumb_tag'] = $profile->thumb_tag;
			$data['meta_title_tag'] = $profile->meta_title_tag;
			$data['meta_description_tag'] = $profile->meta_description_tag;
		}
		return $data;
	}
}
