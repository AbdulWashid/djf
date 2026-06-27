<?php

namespace App\Filament\Pages;

use App\Models\JobListingContent as JobListingContentModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;

class JobListingContent extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.job-listing-content';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Job Listing Content';

    protected static ?string $navigationGroup = 'Content';

    public ?array $data = [];

    public function mount(): void
    {
        $content = JobListingContentModel::firstOrCreate([]);

        $this->form->fill($content->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([

                Tabs::make('Job Listing Content')

                    ->persistTabInQueryString()

                    ->tabs([

                        static::contentTab(
                            'without_filter',
                            'Without Filter'
                        ),

                        static::contentTab(
                            'location',
                            'Location'
                        ),

                        static::contentTab(
                            'category',
                            'Category'
                        ),

                        static::contentTab(
                            'location_category',
                            'Location + Category'
                        ),

                    ])

                    ->columnSpanFull(),

            ]);
    }

    public function save(): void
    {
        $content = JobListingContentModel::firstOrCreate([]);

        $content->update($this->form->getState());

        \Filament\Notifications\Notification::make()
            ->title('Saved successfully.')
            ->success()
            ->send();
    }

    protected static function contentTab(string $field, string $title): Tab
    {
        return Tab::make($title)
            ->schema([

                Section::make('Content')
                    ->schema([

                        RichEditor::make("{$field}.content")
                            ->label('Content')
                            ->helperText(new \Illuminate\Support\HtmlString('
                                    <b>Available placeholders</b><br>
                                    {category-name} → Current category name<br>
                                    {place-name} → Current location name
                                    ')),

                    ]),

                Section::make('Frequently Asked Questions')
                    ->collapsible()
                    ->description('Add frequently asked questions to your post')
                    ->icon('heroicon-o-question-mark-circle')
                    ->schema([

                        Repeater::make("{$field}.faqs")
                            ->schema([

                                TextInput::make('question')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter question'),

                                RichEditor::make('answer')
                                    ->required()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'underline',
                                        'bulletList',
                                        'orderedList',
                                        'redo',
                                        'undo',
                                    ])
                                    ->placeholder('Enter answer'),

                            ])
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->addActionLabel('Add FAQ')
                            ->columns(2),

                    ]),

            ]);
    }
}
