<?php

namespace App\Filament\Resources;

use Closure;
use App\Models\Post;
use Illuminate\Support\Str;
use Filament\{Tables, Forms};
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\KeyValue;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use App\Filament\Filters\DateRangeFilter;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Filters\SelectFilter;
use function GuzzleHttp\default_ca_bundle;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\PostResource\Pages;
use Filament\Resources\{Form, Table, Resource};

use FilamentEditorJs\Forms\Components\EditorJs;
use FilamentCurator\Forms\Components\MediaPicker;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Card::make()->schema([
                Grid::make(['default' => 0])->schema([
                    TextInput::make('title')
                        ->afterStateUpdated(function (Closure $get, Closure $set, ?string $state) {
                            if (!$get('is_slug_changed_manually') && filled($state)) {
                                $set('slug', Str::slug($state));
                            }
                        })
                        ->reactive()
                        ->rules(['max:255', 'string'])
                        ->required()
                        ->placeholder('Title')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('slug')
                        ->afterStateUpdated(function (Closure $set) {
                            $set('is_slug_changed_manually', true);
                        })
                        ->rules(['max:255', 'string'])
                        ->required()
                        ->unique('posts', 'slug', fn (?Model $record) => $record)
                        ->placeholder('Slug')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Hidden::make('is_slug_changed_manually')
                        ->default(false)
                        ->dehydrated(false),

                    ViewField::make('')->view('filament.forms.components.post-info-text')
                        ->columnSpan([
                            'default' => 12
                        ]),

                    EditorJs::make('content')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    // Section::make('Inhalt')
                    //     ->description('Hier können Sie Ihren Seiteninhalt definieren')
                    //     ->schema([
                    //         Builder::make('content')
                    //         ->blocks([
                    //             Builder\Block::make('heading')
                    //                 ->schema([
                    //                     TextInput::make('content')
                    //                         ->label('Heading')
                    //                         ->required(),
                    //                     Select::make('level')
                    //                         ->options([
                    //                             'h1' => 'Heading 1',
                    //                             'h2' => 'Heading 2',
                    //                             'h3' => 'Heading 3',
                    //                         ])
                    //                         ->required(),
                    //                 ]),
                    //             Builder\Block::make('paragraph')
                    //                 ->schema([
                    //                     RichEditor::make('content')
                    //                         ->label('Paragraph')
                    //                         ->required(),
                    //                 ]),
                    //             Builder\Block::make('image')
                    //                 ->schema([
                    //                     FileUpload::make('url')
                    //                         ->label('Image')
                    //                         ->multiple()
                    //                         ->enableReordering()
                    //                         ->storeFileNamesIn('attachment_file_names')
                    //                         ->required(),
                    //                     TextInput::make('alt')
                    //                         ->label('Alt text')
                    //                 ]),
                    //         ])
                    //     ])
                    //     ->columnSpan([
                    //         'default' => 12,
                    //         'md' => 12,
                    //         'lg' => 12,
                    //     ]),

                    // FileUpload::make('image')
                    //     ->rules(['image', 'max:1024'])
                    //     ->nullable()
                    //     ->image()
                    //     ->placeholder('Image')
                    //     ->columnSpan([
                    //         'default' => 12,
                    //         'md' => 12,
                    //         'lg' => 12,
                    //     ]),


                    MediaPicker::make('image')
                        ->label('Bildas')
                        // ->buttonLabel(string | Htmlable | Closure $buttonLabel)
                        // ->buttonLabel('<b>Bildas upload</b>')
                        ->color('primary') // defaults to primary
                        ->outlined(true) // defaults to true
                        ->size('sm|md|lg') // defaults to md
                        ->fitContent(true) // defaults to false (forces image to fit inside the preview area)
                        ->columnSpan(['default' => 12]),

                    Toggle::make('is_published')
                        ->rules(['boolean'])
                        ->required()
                        ->default('0')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Select::make('category_id')
                        ->rules(['exists:categories,id'])
                        ->required()
                        ->relationship('category', 'name')
                        ->searchable()
                        ->placeholder('Category')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
                ]),
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\ImageColumn::make('image')
                    ->toggleable()
                    ->circular(),
                Tables\Columns\IconColumn::make('is_published')
                    ->toggleable()
                    ->boolean(),
                Tables\Columns\TextColumn::make('category.name')
                    ->toggleable()
                    ->limit(50),
            ])
            ->filters([
                DateRangeFilter::make('created_at'),

                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->indicator('Category')
                    ->multiple()
                    ->label('Category'),
            ]);
    }

    public static function getRelations(): array
    {
        return [PostResource\RelationManagers\TagsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'blah' => Pages\Blah::route('/blah'),
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            // 'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
