import { render, screen, fireEvent } from '@testing-library/react';
import { describe, expect, test, vi } from 'vitest';
import { Button } from './Button';

describe('Кнопка', () => {
  test('отображает текст children и вызывает функцию при клике', () => {
    const handleClick = vi.fn();
    render(<Button onClick={handleClick}>Кнопка</Button>);
    const button = screen.getByText('Кнопка');
    fireEvent.click(button);
    expect(handleClick).toHaveBeenCalledTimes(1);
  });
});