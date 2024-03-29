# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.6 - TBD
### Added
- Sortable and Searchable Tables.  Components v1.0.4.

### Changed
- Change 'Open' Timesheet to 'Add'
- Upgraded Bootstrap to 5.2.1. Made changes to support compatibility.
- Added validator to AddPaycode Form in Timesheet

## 1.0.5 
### Fixed
- Add paycode form submit is disabled until select box is changed.

### Added
- Deletion of all timecards for a work week in a department.

## 1.0.4
### Added
- Add: Support Detailed Paycode Import

### Fixed
- Fix: Converted TUES and THURS references to standard three letter.
- Reports: Corrected Blue Sheet Days Calculation. 

### Changed
- Config: time_cards_lines table converted float values to decimal 8,2.
- Reports: Blue sheet report adds hours to parent paycode.
- Database Table: Converted Floating values to Decimals.
- Blue Sheet: PHP Notice is array values were uninitialized.  Added initialization routine to avoid notices, and having to check array keys.