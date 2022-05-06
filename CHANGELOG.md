# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.4 - TBD
### Fixed
- Fix: Converted TUES and THURS references to standard three letter.
- Reports: Corrected Blue Sheet Days Calculation. 

### Changed
- Config: time_cards_lines table converted float values to decimal 8,2.
- Reports: Blue sheet report adds hours to parent paycode.
- Database Table: Converted Floating values to Decimals.
- Blue Sheet: PHP Notice is array values were uninitialized.  Added initialization routine to avoid notices, and having to check array keys.