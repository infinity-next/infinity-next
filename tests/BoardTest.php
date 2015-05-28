<?php namespace Tests;

use App\Board;
use Controller;
use TestCase;

class BoardTest extends TestCase {
	
	/**
	 * The board used by tests
	 */
	private $board;
	
	/**
	 * Picks a random board upon construction.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	public function testModel()
	{
		// Get a board.
		$boards   = Board::take(1)->get();
		// Get a board that cannot exist.
		// The maximum `board_uri` length should be, at most, 31.
		$noBoards = Board::where('board_uri', "12345678901234567890123456789012")->take(1)->get();
		
		$this->assertInstanceOf("Illuminate\Database\Eloquent\Collection", $boards);
		$this->assertInstanceOf("Illuminate\Database\Eloquent\Collection", $noBoards);
		
		$this->assertEquals(0, count($noBoards));
		
		if (count($boards))
		{
			$this->board = $board = $boards[0];
			$this->assertInstanceOf("App\Board", $board);
			
			// Test relationships
			$this->assertInstanceOf("Illuminate\Database\Eloquent\Relations\HasMany", $board->posts());
			$this->assertInstanceOf("Illuminate\Database\Eloquent\Relations\HasMany", $board->logs());
			$this->assertInstanceOf("Illuminate\Database\Eloquent\Relations\HasMany", $board->threads());
			$this->assertInstanceOf("Illuminate\Database\Eloquent\Relations\HasMany", $board->roles());
			
			// Test modern routes
			$response = $this->call('GET', "{$board->board_uri}");
			$this->assertEquals(200, $response->getStatusCode());
			$this->doBoardIndexAssertions();
			
			$response = $this->call('GET', "{$board->board_uri}/1");
			$this->assertEquals(200, $response->getStatusCode());
			$this->doBoardIndexAssertions();
			
			$response = $this->call('GET', "{$board->board_uri}/catalog");
			$this->assertEquals(404, $response->getStatusCode());
			
			$response = $this->call('GET', "{$board->board_uri}/logs");
			$this->assertEquals(200, $response->getStatusCode());
			$this->assertViewHas('board');
			$this->assertViewHas('logs');
			
			
			// Test legacy routes
			$legacyCode = env('LEGACY_ROUTES', false) ? 302 : 404;
			
			$response = $this->call('GET', "{$board->board_uri}/index.html");
			$this->assertEquals($legacyCode, $response->getStatusCode());
			
			$response = $this->call('GET', "{$board->board_uri}/1.html");
			$this->assertEquals($legacyCode, $response->getStatusCode());
			
			$response = $this->call('GET', "{$board->board_uri}/catalog.html");
			$this->assertEquals($legacyCode, $response->getStatusCode());
		}
		else
		{
			$this->assertEquals(0, Board::count());
		}
	}
	
	/**
	 * Test board 404.
	 *
	 * @return void
	 */
	public function testMissingBoard()
	{
		$response = $this->action('GET', 'Board\BoardController@getIndex');
		
		$this->assertEquals(404, $response->getStatusCode());
	}
	
	
	/**
	 * Asserts a series of variables the board index should have.
	 *
	 * @return void
	 */
	private function doBoardIndexAssertions()
	{
		$this->assertViewHas('board');
		$this->assertViewHas('posts');
		$this->assertViewHas('reply_to');
		$this->assertViewHas('pages');
		$this->assertViewHas('page');
		$this->assertViewHas('pagePrev');
		$this->assertViewHas('pageNext');
	}
}
