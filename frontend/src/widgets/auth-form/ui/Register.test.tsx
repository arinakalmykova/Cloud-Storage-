import {Register} from "../../../widgets";
import {useRegister} from "../../../features";
import {describe,  it, expect, vi} from "vitest";
import { render, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

vi.mock("../../../features", () => ({
  useRegister: vi.fn(),
}));

describe("tests for register form", () => {
    it ("test for fail password", async () => {
        const spyOn = vi.spyOn(console,"log");
        const registerMock = vi.fn();
        registerMock.mockRejectedValue(new Error('API Error'));
        vi.mocked(useRegister).mockReturnValue({
            register: registerMock,
            loading: false,
            registerError: null,
        })

        render(<Register></Register>);
        
        const user = userEvent.setup();
        await user.type(screen.getByPlaceholderText("Имя"), "Arina");
        await user.type(screen.getByPlaceholderText("Email"), "aricrate@gmail.com");
        await user.type(screen.getByPlaceholderText("Пароль"), "1234");
        await user.type(screen.getByPlaceholderText("Подтвердите пароль"), "1234");
      expect(
        screen.getByText(
            "Пароль должен содержать минимум 6 символов, одну заглавную букву, одну строчную букву, одну цифру и один специальный символ."
        )
    ).toBeInTheDocument();
        expect(registerMock).not.toHaveBeenCalled();
      expect(spyOn).toHaveBeenCalled();
    })
   
})