# Project Structure

```
php-library/
├── src/                           # Source code
│   ├── Entity/                    # Data entities
│   │   ├── UrlRecord.php         # URL record entity
│   │   └── AnalyticsRecord.php   # Analytics record entity
│   ├── Storage/                   # Storage implementations
│   │   ├── StorageInterface.php   # Storage contract
│   │   ├── MemoryStorage.php     # In-memory storage
│   │   ├── FileStorage.php       # File-based storage
│   │   └── DatabaseStorage.php   # Database storage
│   ├── Generator/                 # Code generators
│   │   ├── GeneratorInterface.php # Generator contract
│   │   ├── RandomCodeGenerator.php # Random code generator
│   │   └── SequentialCodeGenerator.php # Sequential generator
│   ├── Exception/                 # Custom exceptions
│   │   ├── UrlShortenerException.php # Base exception
│   │   ├── ShortCodeNotFoundException.php
│   │   └── InvalidUrlException.php
│   ├── UrlShortener.php          # Main library class
│   └── UrlValidator.php          # URL validation utility
├── tests/                         # Test suites
│   ├── Unit/                     # Unit tests
│   │   ├── RandomCodeGeneratorTest.php
│   │   └── UrlValidatorTest.php
│   └── Integration/              # Integration tests
│       └── UrlShortenerIntegrationTest.php
├── examples/                      # Example usage
│   ├── basic_usage.php           # Basic examples
│   └── database_usage.php        # Database examples
├── vendor/                        # Composer dependencies (after install)
├── composer.json                 # Composer configuration
├── phpunit.xml                   # PHPUnit configuration
├── phpstan.neon                  # PHPStan configuration
├── phpcs.xml                     # PHP CodeSniffer rules
├── quick_start.php               # Quick start script
├── README.md                     # Documentation
├── LICENSE                       # MIT License
└── .gitignore                    # Git ignore rules
```

## Architecture Overview

### Design Patterns Used

1. **Dependency Injection**: Classes receive dependencies through constructor injection
2. **Strategy Pattern**: Interchangeable code generators and storage backends
3. **Repository Pattern**: Storage interface abstracts data persistence
4. **Factory Pattern**: Entity creation from arrays
5. **Value Object**: Immutable entities with business logic

### SOLID Principles

1. **Single Responsibility**: Each class has one clear purpose
2. **Open/Closed**: Easy to extend with new storage/generator implementations
3. **Liskov Substitution**: All implementations are perfectly interchangeable
4. **Interface Segregation**: Clean, focused interfaces
5. **Dependency Inversion**: Depends on abstractions, not concretions

### Key Features

- ✅ PSR-4 autoloading
- ✅ PSR-12 coding standards
- ✅ Comprehensive error handling
- ✅ Analytics tracking
- ✅ URL validation
- ✅ Multiple storage backends
- ✅ Flexible code generation
- ✅ Expiration support
- ✅ Metadata support
- ✅ Complete test coverage
- ✅ Documentation and examples
