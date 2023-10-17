<?php

namespace App\Security\Voter;

use App\Entity\Cocktail;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CocktailVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const VIEW_VIP = 'VIEW_VIP';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT, self::VIEW_VIP])) {
            return false;
        }

        // only vote on `Post` objects
        if ($attribute === self::VIEW_VIP && !$subject instanceof Cocktail) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return match($attribute) {
            self::VIEW_VIP => $this->canViewVip($subject),
            self::EDIT => $this->canEdit(),
            default => throw new \LogicException('This code should not be reached!')
        };
    }


    private function canViewVip(Cocktail $cocktail): bool
    {
        return !$cocktail->isVip() || $this->security->isGranted('ROLE_VIP');
    }

    private function canEdit(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
