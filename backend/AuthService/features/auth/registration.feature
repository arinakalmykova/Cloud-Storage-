Feature: Регистрация пользователей
  Как гость системы
  Я хочу зарегистрироваться в системе
  Чтобы получить доступ к функциям

  Background:
    Given я очищаю тестовые данные

  @positive
  Scenario: Успешная регистрация с валидными данными
    When я пытаюсь зарегистрировать пользователя с именем "Test User", email "test@example.com" и паролем "pass1234"
    Then регистрация должна быть успешной

  @positive
  Scenario Outline: Успешная регистрация с различными надежными паролями
    When я пытаюсь зарегистрировать пользователя с именем "Test User", email "<email>" и паролем "<password>"
    Then регистрация должна быть успешной

    Examples:
      | email                  | password    |
      | user1@example.com      | pass1234    |
      | user2@example.com      | StrongPass1 |
      | user3@example.com      | 12345678    |

  @negative
  Scenario Outline: Регистрация с ненадёжными непустыми паролями
    When я пытаюсь зарегистрировать пользователя с именем "Test User", email "<email>" и паролем "<password>"
    Then регистрация должна завершиться с ошибкой "Invalid credentials"

    Examples:
      | email                  | password    |
      | user4@example.com      | short       |
      | user5@example.com      | password    |
      | user6@example.com      | 1234567     |

  @negative
  Scenario: Регистрация с пустым паролем
    When я пытаюсь зарегистрировать пользователя с именем "Test User", email "user7@example.com" и паролем ""
    Then регистрация должна завершиться с ошибкой "Invalid credentials"