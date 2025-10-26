@extends('layouts.app')

@section('page-title', 'Report User')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    Report User
                </h2>
                <p class="text-[#706f6c] dark:text-[#A1A09A]">
                    Report parking violations and violations by users
                </p>
            </div>
            <div class="w-16 h-16 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-red-600 dark:text-red-400" />
            </div>
        </div>
    </div>

    <!-- Report Form -->
    <div class="bg-white dark:bg-[#1a1a1a] rounded-lg shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A] p-6">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">Submit a Report</h3>
        
        <form class="space-y-6">
            <!-- Violation Type -->
            <div>
                <label for="violation_type" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    Violation Type
                </label>
                <select id="violation_type" name="violation_type" class="w-full px-3 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#1a1a1a] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Select violation type</option>
                    <option value="illegal_parking">Illegal Parking</option>
                    <option value="no_parking_zone">No Parking Zone</option>
                    <option value="blocking_fire_exit">Blocking Fire Exit</option>
                    <option value="handicap_violation">Handicap Violation</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <!-- Vehicle Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="plate_number" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                        Plate Number
                    </label>
                    <input type="text" id="plate_number" name="plate_number" class="w-full px-3 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#1a1a1a] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="ABC-1234">
                </div>
                <div>
                    <label for="vehicle_color" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                        Vehicle Color
                    </label>
                    <input type="text" id="vehicle_color" name="vehicle_color" class="w-full px-3 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#1a1a1a] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="White">
                </div>
            </div>

            <!-- Location -->
            <div>
                <label for="location" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    Location
                </label>
                <input type="text" id="location" name="location" class="w-full px-3 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#1a1a1a] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Building A, Parking Lot 1">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    Description
                </label>
                <textarea id="description" name="description" rows="4" class="w-full px-3 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#1a1a1a] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Describe the violation in detail..."></textarea>
            </div>

            <!-- Evidence Upload -->
            <div>
                <label for="evidence" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                    Evidence (Photos)
                </label>
                <input type="file" id="evidence" name="evidence[]" multiple accept="image/*" class="w-full px-3 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-lg bg-white dark:bg-[#1a1a1a] text-[#1b1b18] dark:text-[#EDEDEC] focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">Upload photos as evidence of the violation</p>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3">
                <button type="button" class="btn bg-gray-500 hover:bg-gray-600 text-white border-gray-500">Cancel</button>
                <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white border-red-600">Submit Report</button>
            </div>
        </form>
    </div>
</div>
@endsection
