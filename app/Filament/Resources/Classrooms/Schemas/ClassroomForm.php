<?php

namespace App\Filament\Resources\Classrooms\Schemas;

use App\Models\Classroom;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ClassroomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basics')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('slug')->required(),
                        TextInput::make('location'),
                        TextInput::make('address'),
                        TextInput::make('capacity')->required()->numeric()->default(12),
                        TextInput::make('hourly_rate')->required()->numeric()->default(0)->prefix('₱'),
                        Toggle::make('is_active')->default(true),
                    ]),

                Section::make('Description')
                    ->schema([
                        Textarea::make('description')->columnSpanFull()->rows(3),
                    ]),

                Section::make('Media')
                    ->schema([
                        TextInput::make('image_url')
                            ->label('Hero image path or URL')
                            ->placeholder('/media/AICognitionRoom.jpeg or https://...')
                            ->live(onBlur: true)
                            ->columnSpanFull(),

                        FileUpload::make('hero_image_upload')
                            ->label('Open file for hero image')
                            ->helperText('Select an image file. It replaces the hero image when you save.')
                            ->image()
                            ->disk('public')
                            ->directory('rooms')
                            ->visibility('public')
                            ->maxSize(8192)
                            ->openable()
                            ->columnSpanFull(),

                        Placeholder::make('image_preview')
                            ->label('')
                            ->content(function (Get $get): HtmlString {
                                $url = Classroom::publicImageUrl($get('image_url'));
                                if (! $url) {
                                    return new HtmlString(
                                        '<div style="border:1px dashed rgba(13,28,76,0.18);padding:1.5rem;text-align:center;color:rgba(13,28,76,0.45);font-size:0.78rem;letter-spacing:0.12em;text-transform:uppercase;">No hero image yet</div>'
                                    );
                                }

                                $safe = e($url);

                                return new HtmlString(
                                    '<div style="border:1px solid rgba(13,28,76,0.10);overflow:hidden;border-radius:2px;">
                                        <img src="'.$safe.'" alt="Hero image preview" style="width:100%;max-height:260px;object-fit:cover;display:block;">
                                    </div>'
                                );
                            })
                            ->columnSpanFull(),

                        Repeater::make('gallery')
                            ->label('Gallery image paths or URLs')
                            ->simple(TextInput::make('url')->placeholder('/media/AICognitionDoor.jpeg or https://...'))
                            ->live(onBlur: true)
                            ->columnSpanFull(),

                        FileUpload::make('gallery_uploads')
                            ->label('Open files for gallery')
                            ->helperText('Select one or more images. They are added to the gallery when you save.')
                            ->image()
                            ->multiple()
                            ->appendFiles()
                            ->reorderable()
                            ->disk('public')
                            ->directory('rooms')
                            ->visibility('public')
                            ->maxSize(8192)
                            ->openable()
                            ->columnSpanFull(),

                        Placeholder::make('gallery_preview')
                            ->label('')
                            ->content(function (Get $get): HtmlString {
                                $items = $get('gallery') ?? [];
                                if (empty($items)) {
                                    return new HtmlString('');
                                }

                                $html = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:0.5rem;">';
                                foreach ($items as $item) {
                                    $url = is_array($item) ? ($item['url'] ?? null) : $item;
                                    $url = Classroom::publicImageUrl($url);
                                    if (! $url) {
                                        continue;
                                    }
                                    $safe = e($url);
                                    $html .= '<div style="border:1px solid rgba(13,28,76,0.10);overflow:hidden;">
                                        <img src="'.$safe.'" alt="" style="width:100%;height:120px;object-fit:cover;display:block;">
                                    </div>';
                                }
                                $html .= '</div>';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Amenities')
                    ->schema([
                        TagsInput::make('amenities')->columnSpanFull(),
                    ]),
            ]);
    }
}
