<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait FileHandler {
	protected string $storagePrefix = 'public';
	protected bool $isOriginalName = false;

	/**
	 * Will store file in the storage folder.
	 * In the storage folder by default it will create a folder called profile.
	 * We can pass any name it will create that folder.
	 * @param UploadedFile $file
	 * @param string       $folder
	 *
	 * @return string
	 */
	public function storeFile( UploadedFile $file, string $folder = 'profile' ): string {
		$name = $this->generateUploadingFileName( $file );
		$file->storeAs( "{$this->storagePrefix}/{$folder}", $name );

		return $folder . '/' . $name;
	}

	/**
	 * Uploaded file name will generate.
	 *
	 * @param UploadedFile $file
	 *
	 * @return string
	 */
	private function generateUploadingFileName( UploadedFile $file ): string {
		$name = $this->getDefaultName();
		if ( $this->isOriginalName ) {
			$name = Str::of( $file->getClientOriginalName() )->replaceMatches( "/[.].*/", '' )->snake()->limit( 30,
				'' );
			$name = $name . "-" . uniqid();
		}

		return $name . "." . $file->getClientOriginalExtension();
	}

	/**
	 * Will generate uniq name
	 *
	 * @return string
	 */
	private function getDefaultName(): string {
		return Str::random( 40 );
	}
}
