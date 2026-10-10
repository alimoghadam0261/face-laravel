<?php

namespace App\Http\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Person;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PersonManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public string $category_filter = 'all';

    /** modal_open is entangled with the Alpine modal state in the Blade view */
    public bool $modal_open = false;

    // Create / Edit Form
    public ?int $person_id = null;
    public string $full_name = '';
    public string $national_code = '';
    public string $phone = '';
    public string $address = '';
    public string $description = '';
    public string $category = 'guest';
    public ?string $photo_path = null;
    public ?string $photo_preview = null;
    public bool $processing_embedding = false;
    public $photo_file = null;

    #[Validate('required|string|max:255')]
    public string $modal_mode = 'create';

    #[Computed]
    public function persons()
    {
        $query = Person::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('national_code', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->category_filter !== 'all') {
            $query->where('category', $this->category_filter);
        }

        return $query->orderBy('full_name')->paginate(15);
    }

    public function resetForm(): void
    {
        $this->person_id = null;
        $this->full_name = '';
        $this->national_code = '';
        $this->phone = '';
        $this->address = '';
        $this->description = '';
        $this->category = 'guest';
        $this->photo_path = null;
        $this->photo_preview = null;
        $this->photo_file = null;
        $this->modal_open = false;
        $this->resetValidation();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->modal_mode = 'create';
        $this->modal_open = true;
        $this->dispatch('openCreateModal');
    }

    public function openEditModal(int $id): void
    {
        $person = Person::findOrFail($id);
        $this->person_id = $person->id;
        $this->full_name = $person->full_name;
        $this->national_code = $person->national_code;
        $this->phone = $person->phone ?? '';
        $this->address = $person->address ?? '';
        $this->description = $person->description ?? '';
        $this->category = $person->category;
        $this->photo_path = $person->photo_path;
        $this->photo_preview = $person->photo_path ? asset('storage/' . $person->photo_path) : null;
        $this->modal_mode = 'edit';
        $this->modal_open = true;
        $this->dispatch('openEditModal');
    }

    public function closeModal(): void
    {
        $this->modal_open = false;
        $this->dispatch('closeModal');
    }

    public function updatedPhotoFile(): void
    {
        if ($this->photo_file) {
            // Show the freshly picked photo before it is stored on save
            try {
                $this->photo_preview = $this->photo_file->temporaryUrl();
            } catch (\Exception $e) {
                $this->photo_preview = null;
            }
            $this->photo_path = null;
        }
    }

    public function extractEmbedding(string $photoPath): ?array
    {
        $this->processing_embedding = true;

        try {
            $disk = Storage::disk('public');

            if (! $disk->exists($photoPath)) {
                Log::error('AI Engine: photo not found on public disk', ['path' => $photoPath]);
                return null;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.ai.api_key'),
            ])
                ->timeout(30)
                ->attach(
                    'file',
                    $disk->get($photoPath),
                    basename($photoPath)
                )
                ->post(config('services.ai.url') . '/api/extract-embedding');

            if ($response->successful()) {
                $data = $response->json();

                if (! empty($data['success']) && ! empty($data['embedding'])) {
                    return $data['embedding'];
                }

                Log::warning('AI Engine: no face detected in uploaded photo', [
                    'message' => $data['message'] ?? 'unknown',
                ]);
                return null;
            }

            Log::warning('AI Engine embedding extraction failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('AI Engine connection error', ['message' => $e->getMessage()]);
            return null;
        } finally {
            $this->processing_embedding = false;
        }
    }

    /**
     * Ask the AI engine to reload its in-memory person cache.
     */
    protected function reloadAiCache(): void
    {
        try {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.ai.api_key'),
            ])
                ->timeout(10)
                ->post(config('services.ai.url') . '/api/reload-cache');
        } catch (\Exception $e) {
            Log::warning('AI Engine cache reload failed', ['message' => $e->getMessage()]);
        }
    }

    public function savePerson(): void
    {
        $validated = $this->validate([
            'full_name' => 'required|string|max:255',
            'national_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('people', 'national_code')->ignore($this->person_id),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
            'category' => 'required|in:vip,employee,guest',
            'photo_path' => 'nullable|string',
            'photo_file' => 'nullable|image|max:5120',
        ]);

        $person = $this->person_id
            ? Person::findOrFail($this->person_id)
            : new Person();

        $person->full_name = $validated['full_name'];
        $person->national_code = $validated['national_code'];
        $person->phone = $validated['phone'];
        $person->address = $validated['address'];
        $person->description = $validated['description'];
        $person->category = $validated['category'];

        if ($this->photo_file) {
            $path = $this->photo_file->store('photos', 'public');
            $person->photo_path = $path;
            $this->photo_path = $path;
            $this->photo_preview = asset('storage/' . $path);

            $embedding = $this->extractEmbedding($path);
            if ($embedding) {
                $person->face_embedding = $embedding;
            }
        } elseif ($this->photo_path && $this->photo_path !== ($person->photo_path ?? '')) {
            $person->photo_path = $this->photo_path;
            $embedding = $this->extractEmbedding($this->photo_path);
            if ($embedding) {
                $person->face_embedding = $embedding;
            }
        }

        $person->save();

        if ($this->photo_file) {
            $this->photo_file = null;
        }

        // Keep the AI engine's in-memory face cache in sync with the database
        $this->reloadAiCache();

        $this->resetForm();
        $this->dispatch('closeModal');
        $this->dispatch('personSaved');
    }

    public function deletePerson(int $id): void
    {
        $person = Person::findOrFail($id);
        $person->delete();

        // Keep the AI engine's in-memory face cache in sync with the database
        $this->reloadAiCache();

        $this->dispatch('personDeleted');
    }

    #[On('refresh-persons')]
    public function refreshPersons(): void
    {
        // Just trigger re-render
    }

    public function render()
    {
        return view('components.person-manager');
    }
}
