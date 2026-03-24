Feature: Политика надежности паролей
  Как разработчик системы
  Я хочу проверять надежность паролей
  Чтобы обеспечить безопасность аккаунтов

  Scenario Outline: Проверка различных комбинаций паролей (непустых)
    Given я проверяю пароль "<password>"
    Then результат должен быть <expected>

    Examples:
      | password    | expected |
      | short       | false    |
      | password    | false    |
      | pass1234    | true     |
      | StrongPass1 | true     |
      | 12345678    | true     |
      | 1234567     | false    |

  Scenario: Проверка пустого пароля
    Given я проверяю пароль ""
    Then результат должен быть false