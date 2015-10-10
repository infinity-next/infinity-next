<?php

use Illuminate\Database\Seeder;

use App\Option;

class OptionSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Seeding system options.');
		
		$option_count = Option::count();
		
		foreach ($this->slugs() as $slugType => $slugs)
		{
			foreach ($slugs as $slug)
			{
				$slug['option_type'] = $slugType;
				$option = Option::updateOrCreate([
					'option_name' => $slug['option_name'],
				], $slug);
			}
		}
		
		$option_count = Option::count() - $option_count;
		
		$this->command->info("Done. Seeded {$option_count} new permission(s).");
	}
	
	private function slugs()
	{
		return [
			'site' => [
				[
					'option_name'           => "adventureEnabled",
					'default_value'         => 1,
					'format'                => "onoff",
					'data_type'             => "boolean",
					'validation_parameters' => 'boolean'
				],
				[
					'option_name'           => "adventureIcons",
					'default_value'         => 1,
					'format'                => "onoff",
					'data_type'             => "boolean",
					'validation_parameters' => 'boolean'
				],
				
				[
					'option_name'           => "attachmentFilesize",
					'default_value'         => "1024",
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min'
				],
				[
					'option_name'           => "attachmentThumbnailSize",
					'default_value'         => "250",
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 50 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min'
				],
				[
					'option_name'           => "attachmentThumbnailQuality",
					'default_value'         => "75",
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 100 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min'
				],
				[
					'option_name'           => "attachmentThumbnailJpeg",
					'default_value'         => 0,
					'format'                => "onoff",
					'data_type'             => "boolean",
					'validation_parameters' => 'boolean'
				],
				
				[
					'option_name'           => "banMaxLength",
					'default_value'         => "30",
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => -1 ] ),
					'data_type'             => "integer",
					'validation_parameters' => 'required|min:$min'
				],
				[
					'option_name'           => "banSubnets",
					'default_value'         => 1,
					'format'                => "onoff",
					'data_type'             => "boolean",
					'validation_parameters' => 'boolean'
				],
				
				[
					'option_name'           => "boardCreateMax",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min'
				],
				[
					'option_name'           => "boardCreateTimer",
					'default_value'         => 15,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min'
				],
				[
					'option_name'           => "boardListShow",
					'default_value'         => 1,
					'format'                => "onoff",
					'data_type'             => "boolean",
					'validation_parameters' => "boolean",
				],
				[
					'option_name'           => "boardUriBanned",
					'default_value'         => "",
					'format'                => "textbox",
					'data_type'             => "string",
				],
				[
					'option_name'           => "postFloodTime",
					'default_value'         => 30,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min'
				],
				[
					'option_name'           => "globalReportText",
					'default_value'         => "",
					'format'                => "textbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
					'data_type'             => "string",
					'validation_parameters' => 'min:$min|max:$max'
				],
			],
			
			'board' => [
				[
					'option_name'           => "boardCustomCSS",
					'default_value'         => "",
					'format'                => "textbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
					'data_type'             => "string",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "boardReportText",
					'default_value'         => "",
					'format'                => "textbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
					'data_type'             => "string",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "boardSidebarText",
					'default_value'         => "",
					'format'                => "textbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
					'data_type'             => "string",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "postAttachmentsMax",
					'default_value'         => "5",
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 10 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'required|min:$min|max:$max'
				],
				[
					'option_name'           => "postMaxLength",
					'default_value'         => null,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65534 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'integer|min:$min|max:$max|greater_than:postMinLength',
				],
				[
					'option_name'           => "postMinLength",
					'default_value'         => null,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65534 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'integer|min:$min|max:$max',
				],
				[
					'option_name'           => "postsPerPage",
					'default_value'         => "10",
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 5, 'max' => 20 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "postsThreadId",
					'default_value'         => false,
					'format'                => "onoff",
					'data_type'             => "boolean",
					'validation_parameters' => "boolean",
				],
				[
					'option_name'           => "postsAuthorCountry",
					'default_value'         => false,
					'format'                => "onoff",
					'data_type'             => "boolean",
					'validation_parameters' => "boolean",
				],
				
				
				[
					'option_name'           => "epheSageThreadReply",
					'default_value'         => 350,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheSageThreadDays",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 365 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheSageThreadPage",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheLockThreadReply",
					'default_value'         => 700,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheLockThreadDays",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 365 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheLockThreadPage",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheDeleteThreadReply",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheDeleteThreadDays",
					'default_value'         => 0,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 365 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
				[
					'option_name'           => "epheDeleteThreadPage",
					'default_value'         => 16,
					'format'                => "spinbox",
					'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
					'data_type'             => "unsigned_integer",
					'validation_parameters' => 'min:$min|max:$max'
				],
			],
		];
	}
}


use App\OptionGroup;
use App\OptionGroupAssignment;

class OptionGroupSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Seeding option groups and relationships.');
		
		OptionGroupAssignment::truncate();
		
		foreach ($this->slugs() as $slug)
		{
			$optionGroupOptions = $slug['options'];
			unset($slug['options']);
			
			$optionGroup = OptionGroup::firstOrNew([
				'group_name' => $slug['group_name'],
			]);
			
			$optionGroup->debug_only = $slug['debug_only'];
			$optionGroup->display_order = $slug['display_order'];
			
			$optionGroup->save();
			
			foreach ($optionGroupOptions as $optionGroupIndex => $optionGroupOption)
			{
				$optionGroupOptionModel = OptionGroupAssignment::firstOrNew([
					'option_name'     => $optionGroupOption,
					'option_group_id' => $optionGroup->option_group_id,
				]);
				
				if ($optionGroupOptionModel->exists)
				{
					OptionGroupAssignment::where([
						'option_name'     => $optionGroupOption,
						'option_group_id' => $optionGroup->option_group_id,
					])->update([
						'display_order' => $optionGroupIndex * 10,
					]);
				}
				else
				{
					$optionGroupOptionModel->display_order = $optionGroupIndex * 10;
					$optionGroupOptionModel->save();
				}
				
				$optionGroupOptionModels[] = $optionGroupOptionModel;
			}
		}
	}
	
	private function slugs()
	{
		return [
			[
				'group_name'    => "attachments",
				'debug_only'    => false,
				'display_order' => 100,
				
				'options'       => [
					"attachmentFilesize",
					"attachmentThumbnailSize",
					"attachmentThumbnailQuality",
					"attachmentThumbnailJpeg",
				],
			],
			[
				'group_name'    => "bans",
				'debug_only'    => false,
				'display_order' => 200,
				
				'options'       => [
					"banMaxLength",
					"banSubnets",
				],
			],
			[
				'group_name'    => "boards",
				'debug_only'    => false,
				'display_order' => 300,
				
				'options'       => [
					"boardCreateMax",
					"boardCreateTimer",
					"boardUriBanned",
				],
			],
			[
				'group_name'    => "adventures",
				'debug_only'    => false,
				'display_order' => 350,
				
				'options'       => [
					"adventureEnabled",
					"adventureIcons",
				],
			],
			[
				'group_name'    => "board_ephemerality",
				'debug_only'    => false,
				'display_order' => 300,
				
				'options'       => [
					"epheSageThreadReply",
					"epheSageThreadDays",
					"epheSageThreadPage",
					"epheLockThreadReply",
					"epheLockThreadDays",
					"epheLockThreadPage",
					"epheDeleteThreadReply",
					"epheDeleteThreadDays",
					"epheDeleteThreadPage",
				],
			],
			[
				'group_name'    => "board_posts",
				'debug_only'    => false,
				'display_order' => 400,
				
				'options'       => [
					"postAttachmentsMax",
					"postMaxLength",
					"postMinLength",
					'postFloodTime',
				],
			],
			[
				'group_name'    => "board_threads",
				'debug_only'    => false,
				'display_order' => 500,
				
				'options'       => [
					"postsPerPage",
					"postsAuthorCountry",
					"postsThreadId",
				],
			],
			[
				'group_name'    => "navigation",
				'debug_only'    => false,
				'display_order' => 600,
				
				'options'       => [
					"boardListShow",
				],
			],
			[
				'group_name'    => "style",
				'debug_only'    => false,
				'display_order' => 1000,
				
				'options'       => [
					"boardCustomCSS",
				],
			],
			[
				'group_name'    => "sidebar",
				'debug_only'    => false,
				'display_order' => 1100,
				
				'options'       => [
					"boardSidebarText",
				],
			],
			[
				'group_name'    => "reports",
				'debug_only'    => false,
				'display_order' => 1200,
				
				'options'       => [
					"boardReportText",
					"globalReportText",
				]
			],
		];
	}
}
